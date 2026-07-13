<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

const RECRUITMENT_EMAIL_TYPES = ['approved', 'rejected'];

function recruitment_email_defaults(): array
{
    return [
        'mail_sender_name' => APP_NAME,
        'mail_reply_to' => '',
        'mail_approved_subject' => 'Chúc mừng {{ho_ten}} đã trở thành thành viên CIT',
        'mail_approved_body' => '<p>Xin chào <strong>{{ho_ten}}</strong>,</p><p>Chúc mừng bạn đã vượt qua đợt tuyển thành viên của <strong>{{ten_clb}}</strong>.</p><p>Chúng mình rất vui được đồng hành cùng bạn trong những hoạt động sắp tới. Thông tin tiếp theo sẽ được gửi tới bạn trong thời gian sớm nhất.</p><p>Trân trọng,<br><strong>{{ten_clb}}</strong></p>',
        'mail_rejected_subject' => 'Kết quả tuyển thành viên {{ten_clb}}',
        'mail_rejected_body' => '<p>Xin chào <strong>{{ho_ten}}</strong>,</p><p>Cảm ơn bạn đã dành thời gian tham gia đợt tuyển thành viên của <strong>{{ten_clb}}</strong>.</p><p>Rất tiếc trong đợt này chúng mình chưa thể đồng hành cùng bạn. Hy vọng sẽ được gặp lại bạn trong những hoạt động và cơ hội tiếp theo của CIT.</p><p>Trân trọng,<br><strong>{{ten_clb}}</strong></p>',
    ];
}

function recruitment_email_settings(object $pdo): array
{
    $settings = recruitment_email_defaults();
    $keys = array_keys($settings);
    $placeholders = implode(',', array_fill(0, count($keys), '?'));
    $statement = $pdo->prepare("SELECT content_key, content_value FROM page_contents WHERE content_key IN ({$placeholders})");
    $statement->execute($keys);

    foreach ($statement as $row) {
        $key = (string) $row['content_key'];
        if (array_key_exists($key, $settings)) {
            $settings[$key] = (string) $row['content_value'];
        }
    }

    return $settings;
}

function save_recruitment_email_settings(object $pdo, array $settings): void
{
    $allowed = array_keys(recruitment_email_defaults());
    $statement = $pdo->prepare(
        'INSERT INTO page_contents (content_key, content_value) VALUES (:content_key, :content_value)
         ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)'
    );

    foreach ($allowed as $key) {
        if (!array_key_exists($key, $settings)) {
            continue;
        }
        $statement->execute(['content_key' => $key, 'content_value' => (string) $settings[$key]]);
    }

    require_once __DIR__ . '/editable.php';
    editable_cache_clear();
}

function smtp_configuration(): array
{
    return [
        'host' => trim((string) (getenv('MAIL_HOST') ?: '')),
        'port' => max(1, (int) (getenv('MAIL_PORT') ?: 587)),
        'username' => trim((string) (getenv('MAIL_USERNAME') ?: '')),
        'password' => (string) (getenv('MAIL_PASSWORD') ?: ''),
        'encryption' => strtolower(trim((string) (getenv('MAIL_ENCRYPTION') ?: 'tls'))),
        'from_address' => trim((string) (getenv('MAIL_FROM_ADDRESS') ?: '')),
        'timeout' => min(30, max(3, (int) (getenv('MAIL_TIMEOUT') ?: 10))),
    ];
}

function smtp_is_configured(): bool
{
    $config = smtp_configuration();
    return $config['host'] !== '' && filter_var($config['from_address'], FILTER_VALIDATE_EMAIL) !== false;
}

function sanitize_recruitment_email_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }
    if (!class_exists(DOMDocument::class)) {
        return '<p>' . nl2br(e(strip_tags($html))) . '</p>';
    }

    $document = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML(
        '<?xml encoding="UTF-8"><div id="cit-email-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $xpath = new DOMXPath($document);
    $root = $xpath->query('//*[@id="cit-email-root"]')->item(0);
    if (!$root instanceof DOMElement) {
        return '';
    }

    $allowedTags = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a', 'img', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'blockquote', 'div', 'span'];
    $removeWithContents = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'math'];

    $sanitizeNode = static function (DOMNode $parent) use (&$sanitizeNode, $allowedTags, $removeWithContents): void {
        for ($node = $parent->firstChild; $node !== null;) {
            $next = $node->nextSibling;
            if ($node instanceof DOMComment) {
                $parent->removeChild($node);
                $node = $next;
                continue;
            }
            if ($node instanceof DOMElement) {
                $tag = strtolower($node->tagName);
                $alignment = '';
                if (in_array($tag, $removeWithContents, true)) {
                    $parent->removeChild($node);
                    $node = $next;
                    continue;
                }
                if (!in_array($tag, $allowedTags, true)) {
                    $sanitizeNode($node);
                    while ($node->firstChild !== null) {
                        $parent->insertBefore($node->firstChild, $node);
                    }
                    $parent->removeChild($node);
                    $node = $next;
                    continue;
                }

                foreach (iterator_to_array($node->attributes) as $attribute) {
                    $name = strtolower($attribute->name);
                    if ($name === 'align' && in_array($tag, ['p', 'div', 'h1', 'h2', 'h3', 'blockquote'], true)) {
                        $candidate = strtolower(trim($attribute->value));
                        if (in_array($candidate, ['left', 'center', 'right', 'justify'], true)) {
                            $alignment = $candidate;
                        }
                    }
                    $keep = ($tag === 'a' && in_array($name, ['href', 'title', 'target'], true))
                        || ($tag === 'img' && in_array($name, ['src', 'alt', 'width', 'height'], true));
                    if ($name === 'style') {
                        $style = strtolower(trim($attribute->value));
                        if (preg_match('/^text-align\s*:\s*(left|center|right|justify)\s*;?$/', $style, $match)) {
                            $node->setAttribute('style', 'text-align: ' . $match[1] . ';');
                            $keep = true;
                        }
                    }
                    if (!$keep) {
                        $node->removeAttribute($attribute->name);
                    }
                }
                if ($alignment !== '') {
                    $node->setAttribute('style', 'text-align: ' . $alignment . ';');
                }

                if ($tag === 'a' && $node->hasAttribute('href')) {
                    $href = trim($node->getAttribute('href'));
                    if (!preg_match('~^(https?://|mailto:|#)~i', $href)) {
                        $node->removeAttribute('href');
                    } else {
                        $node->setAttribute('target', '_blank');
                        $node->setAttribute('rel', 'noopener noreferrer');
                    }
                }
                if ($tag === 'img') {
                    $src = trim($node->getAttribute('src'));
                    if (!preg_match('~^https?://~i', $src)) {
                        $parent->removeChild($node);
                        $node = $next;
                        continue;
                    }
                    foreach (['width', 'height'] as $dimension) {
                        $value = $node->getAttribute($dimension);
                        if ($value !== '' && (!ctype_digit($value) || (int) $value < 1 || (int) $value > 2400)) {
                            $node->removeAttribute($dimension);
                        }
                    }
                    $node->setAttribute('alt', mb_substr($node->getAttribute('alt'), 0, 180));
                    $node->setAttribute('style', 'max-width: 100%; height: auto; display: inline-block;');
                }
                $sanitizeNode($node);
            }
            $node = $next;
        }
    };

    $sanitizeNode($root);
    $output = '';
    foreach ($root->childNodes as $child) {
        $output .= $document->saveHTML($child);
    }

    return trim($output);
}

function recruitment_email_context(object $pdo, int $submissionId): ?array
{
    $statement = $pdo->prepare(
        'SELECT fs.id, fs.submitted_at, ff.field_name, ff.field_type, fsv.value
         FROM form_submissions fs
         LEFT JOIN form_submission_values fsv ON fsv.submission_id = fs.id
         LEFT JOIN form_fields ff ON ff.id = fsv.field_id
         WHERE fs.id = :submission_id
         ORDER BY ff.sort_order, ff.id'
    );
    $statement->execute(['submission_id' => $submissionId]);
    $rows = $statement->fetchAll();
    if (!$rows) {
        return null;
    }

    $fields = [];
    $email = '';
    foreach ($rows as $row) {
        $fieldName = isset($row['field_name']) ? (string) $row['field_name'] : '';
        if ($fieldName === '') {
            continue;
        }
        $value = format_submission_value(
            isset($row['value']) ? (string) $row['value'] : null,
            isset($row['field_type']) ? (string) $row['field_type'] : 'text'
        );
        $fields[$fieldName] = $value === '—' ? '' : $value;
        if ($fieldName === 'email' || ($email === '' && ($row['field_type'] ?? '') === 'email')) {
            $email = trim($fields[$fieldName]);
        }
    }

    $submittedAt = (string) $rows[0]['submitted_at'];
    return [
        'submission_id' => $submissionId,
        'email' => $email,
        'name' => trim((string) ($fields['ho_ten'] ?? '')) ?: 'Bạn',
        'fields' => $fields,
        'values' => array_merge($fields, [
            'ho_ten' => trim((string) ($fields['ho_ten'] ?? '')) ?: 'Bạn',
            'email' => $email,
            'ma_don' => 'CIT-' . str_pad((string) $submissionId, 6, '0', STR_PAD_LEFT),
            'ngay_nop' => date('d/m/Y H:i', strtotime($submittedAt)),
            'ten_clb' => APP_NAME,
        ]),
    ];
}

function render_recruitment_email_template(string $template, array $context, bool $html): string
{
    $values = isset($context['values']) && is_array($context['values']) ? $context['values'] : [];
    $fields = isset($context['fields']) && is_array($context['fields']) ? $context['fields'] : [];

    $rendered = preg_replace_callback(
        '/\{\{\s*(?:(field):)?([a-zA-Z0-9_]+)\s*\}\}/u',
        static function (array $match) use ($values, $fields, $html): string {
            $value = ($match[1] ?? '') === 'field'
                ? (string) ($fields[$match[2]] ?? '')
                : (string) ($values[$match[2]] ?? '');
            return $html ? nl2br(e($value)) : preg_replace('/[\r\n]+/', ' ', $value);
        },
        $template
    );

    return is_string($rendered) ? trim($rendered) : '';
}

function recruitment_email_plain_text(string $html): string
{
    $html = preg_replace_callback('/<img\b[^>]*\balt=(?:"([^"]*)"|\'([^\']*)\')[^>]*>/i', static function (array $match): string {
        $alt = trim(html_entity_decode((string) ($match[1] ?: $match[2] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        return $alt !== '' ? "\n[Hình ảnh: {$alt}]\n" : "\n[Hình ảnh]\n";
    }, $html) ?? $html;
    $text = preg_replace('#<(br|/p|/div|/li|/h[1-3])\b[^>]*>#i', "\n", $html);
    $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace("/[ \t]+\n/", "\n", $text);
    $text = preg_replace("/\n{3,}/", "\n\n", (string) $text);
    return trim((string) $text);
}

function send_recruitment_email(array $message): array
{
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!is_file($autoload)) {
        return ['success' => false, 'error' => 'PHPMailer chưa được cài đặt trên máy chủ.'];
    }
    require_once $autoload;

    $config = smtp_configuration();
    if (!smtp_is_configured()) {
        return ['success' => false, 'error' => 'Cấu hình SMTP chưa đầy đủ.'];
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->Port = $config['port'];
        $mail->Timeout = $config['timeout'];
        $mail->SMTPAuth = $config['username'] !== '';
        if ($mail->SMTPAuth) {
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
        }
        if ($config['encryption'] === 'ssl' || $config['encryption'] === 'smtps') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($config['encryption'] === 'tls' || $config['encryption'] === 'starttls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }

        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->setFrom($config['from_address'], (string) ($message['sender_name'] ?? APP_NAME));
        $replyTo = trim((string) ($message['reply_to'] ?? ''));
        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo);
        }
        $mail->addAddress((string) $message['recipient_email'], (string) ($message['recipient_name'] ?? ''));
        $mail->isHTML(true);
        $mail->Subject = (string) $message['subject'];
        $mail->Body = (string) $message['body_html'];
        $mail->AltBody = recruitment_email_plain_text((string) $message['body_html']);
        $mail->send();

        return ['success' => true, 'message_id' => $mail->getLastMessageID()];
    } catch (Throwable $exception) {
        error_log('Recruitment email failed: ' . $exception->getMessage());
        return ['success' => false, 'error' => mb_substr($mail->ErrorInfo ?: $exception->getMessage(), 0, 500)];
    }
}

function update_email_batch_progress(object $pdo, int $batchId): array
{
    $statement = $pdo->prepare('SELECT status, COUNT(*) AS total FROM email_deliveries WHERE batch_id = :batch_id GROUP BY status');
    $statement->execute(['batch_id' => $batchId]);
    $counts = ['pending' => 0, 'sending' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0];
    foreach ($statement as $row) {
        $status = (string) $row['status'];
        if (array_key_exists($status, $counts)) {
            $counts[$status] = (int) $row['total'];
        }
    }

    $batchStatement = $pdo->prepare('SELECT selected_count, duplicate_count FROM email_batches WHERE id = :id');
    $batchStatement->execute(['id' => $batchId]);
    $batch = $batchStatement->fetch();
    if (!$batch) {
        throw new RuntimeException('Không tìm thấy đợt gửi email.');
    }

    $selected = (int) $batch['selected_count'];
    $duplicate = (int) $batch['duplicate_count'];
    $processed = $counts['sent'] + $counts['failed'] + $counts['skipped'] + $duplicate;
    $complete = $counts['pending'] === 0 && $counts['sending'] === 0;
    $update = $pdo->prepare(
        'UPDATE email_batches
         SET processed_count = :processed_count, sent_count = :sent_count, failed_count = :failed_count,
             skipped_count = :skipped_count, status = :status,
             completed_at = CASE WHEN :is_complete = 1 THEN COALESCE(completed_at, NOW()) ELSE NULL END
         WHERE id = :id'
    );
    $update->execute([
        'processed_count' => min($selected, $processed),
        'sent_count' => $counts['sent'],
        'failed_count' => $counts['failed'],
        'skipped_count' => $counts['skipped'] + $duplicate,
        'status' => $complete ? 'completed' : 'processing',
        'is_complete' => $complete ? 1 : 0,
        'id' => $batchId,
    ]);

    return [
        'batch_id' => $batchId,
        'selected' => $selected,
        'processed' => min($selected, $processed),
        'sent' => $counts['sent'],
        'failed' => $counts['failed'],
        'skipped' => $counts['skipped'] + $duplicate,
        'pending' => $counts['pending'] + $counts['sending'],
        'complete' => $complete,
    ];
}

function process_email_delivery(object $pdo, int $deliveryId, bool $allowRetry = false): array
{
    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            'SELECT ed.*, eb.subject_template, eb.body_template, eb.sender_name, eb.reply_to
             FROM email_deliveries ed
             INNER JOIN email_batches eb ON eb.id = ed.batch_id
             WHERE ed.id = :id FOR UPDATE'
        );
        $statement->execute(['id' => $deliveryId]);
        $delivery = $statement->fetch();
        if (!$delivery) {
            throw new RuntimeException('Không tìm thấy email cần gửi.');
        }
        if ($delivery['status'] === 'sent') {
            $pdo->commit();
            return ['status' => 'sent', 'batch_id' => (int) $delivery['batch_id']];
        }
        if ($delivery['status'] === 'sending') {
            $pdo->commit();
            return ['status' => 'sending', 'batch_id' => (int) $delivery['batch_id']];
        }
        if ($delivery['status'] === 'failed' && !$allowRetry) {
            $pdo->commit();
            return ['status' => 'failed', 'batch_id' => (int) $delivery['batch_id']];
        }
        if ($delivery['status'] === 'skipped' && !$allowRetry) {
            $pdo->commit();
            return ['status' => 'skipped', 'batch_id' => (int) $delivery['batch_id']];
        }
        if (!in_array($delivery['status'], ['pending', 'failed', 'skipped'], true)) {
            $pdo->commit();
            return ['status' => (string) $delivery['status'], 'batch_id' => (int) $delivery['batch_id']];
        }

        $mark = $pdo->prepare(
            "UPDATE email_deliveries SET status = 'sending', attempts = attempts + 1, last_attempt_at = NOW(), last_error = NULL WHERE id = :id"
        );
        $mark->execute(['id' => $deliveryId]);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    $batchId = (int) $delivery['batch_id'];
    $context = recruitment_email_context($pdo, (int) $delivery['submission_id']);
    if ($context === null) {
        $result = ['success' => false, 'skipped' => true, 'error' => 'Hồ sơ không còn tồn tại.'];
    } else {
        $recipientEmail = trim((string) ($delivery['recipient_email'] ?: $context['email']));
        $recipientName = trim((string) ($delivery['recipient_name'] ?: $context['name']));
        $subject = trim((string) ($delivery['subject'] ?: render_recruitment_email_template((string) $delivery['subject_template'], $context, false)));
        $bodyHtml = trim((string) ($delivery['body_html'] ?: render_recruitment_email_template((string) $delivery['body_template'], $context, true)));

        $snapshot = $pdo->prepare(
            'UPDATE email_deliveries SET recipient_email = :recipient_email, recipient_name = :recipient_name,
             subject = :subject, body_html = :body_html WHERE id = :id'
        );
        $snapshot->execute([
            'recipient_email' => $recipientEmail !== '' ? $recipientEmail : null,
            'recipient_name' => $recipientName,
            'subject' => mb_substr($subject, 0, 255),
            'body_html' => $bodyHtml,
            'id' => $deliveryId,
        ]);

        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $result = ['success' => false, 'skipped' => true, 'error' => 'Hồ sơ không có địa chỉ email hợp lệ.'];
        } else {
            $result = send_recruitment_email([
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName,
                'sender_name' => (string) $delivery['sender_name'],
                'reply_to' => (string) $delivery['reply_to'],
                'subject' => $subject,
                'body_html' => $bodyHtml,
            ]);
        }
    }

    if (!empty($result['success'])) {
        $finish = $pdo->prepare(
            "UPDATE email_deliveries SET status = 'sent', sent_at = NOW(), last_error = NULL, message_id = :message_id WHERE id = :id"
        );
        $finish->execute(['message_id' => (string) ($result['message_id'] ?? ''), 'id' => $deliveryId]);
        $status = 'sent';
    } else {
        $status = !empty($result['skipped']) ? 'skipped' : 'failed';
        $finish = $pdo->prepare('UPDATE email_deliveries SET status = :status, last_error = :last_error WHERE id = :id');
        $finish->execute([
            'status' => $status,
            'last_error' => mb_substr((string) ($result['error'] ?? 'Không thể gửi email.'), 0, 500),
            'id' => $deliveryId,
        ]);
    }

    update_email_batch_progress($pdo, $batchId);
    return ['status' => $status, 'batch_id' => $batchId, 'error' => $result['error'] ?? null];
}

function email_json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
