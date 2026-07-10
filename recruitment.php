<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/form-fields.php';
require_once __DIR__ . '/includes/db.php';

ensure_session_started();

$fields = $pdo->query(
    'SELECT id, field_label, field_name, field_type, options, is_required
     FROM form_fields
     ORDER BY sort_order ASC, id ASC'
)->fetchAll();

$errors = [];
$answers = [];

if (is_post()) {
    verify_csrf();
    $postedAnswers = isset($_POST['fields']) && is_array($_POST['fields']) ? $_POST['fields'] : [];

    foreach ($fields as $field) {
        $fieldId = (int) $field['id'];
        $fieldType = $field['field_type'];
        $options = field_options($field['options']);
        $rawValue = $postedAnswers[$fieldId] ?? '';

        if ($fieldType === 'checkbox') {
            $selected = is_array($rawValue) ? $rawValue : [];
            $selected = array_values(array_unique(array_map(
                static fn ($value): string => trim((string) $value),
                $selected
            )));
            $invalidChoices = array_diff($selected, $options);

            if ($invalidChoices) {
                $errors[$fieldId] = 'Lựa chọn không hợp lệ.';
            } elseif ((int) $field['is_required'] === 1 && !$selected) {
                $errors[$fieldId] = 'Vui lòng chọn ít nhất một lựa chọn.';
            }

            $answers[$fieldId] = $selected;
            continue;
        }

        $value = is_string($rawValue) ? trim($rawValue) : '';
        $answers[$fieldId] = $value;

        if ((int) $field['is_required'] === 1 && $value === '') {
            $errors[$fieldId] = 'Trường này là bắt buộc.';
            continue;
        }

        if ($value === '') {
            continue;
        }

        $maxLength = $fieldType === 'textarea' ? 5000 : 255;
        if (mb_strlen($value) > $maxLength) {
            $errors[$fieldId] = "Nội dung không được vượt quá {$maxLength} ký tự.";
        } elseif ($fieldType === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$fieldId] = 'Email không đúng định dạng.';
        } elseif ($fieldType === 'phone') {
            $digits = preg_replace('/\D+/', '', $value) ?? '';
            if (!preg_match('/^\+?[0-9\s().-]{9,20}$/', $value) || strlen($digits) < 9 || strlen($digits) > 12) {
                $errors[$fieldId] = 'Số điện thoại không đúng định dạng.';
            }
        } elseif (in_array($fieldType, ['dropdown', 'radio'], true) && !in_array($value, $options, true)) {
            $errors[$fieldId] = 'Lựa chọn không hợp lệ.';
        }
    }

    if (!$fields) {
        $errors['form'] = 'Form đăng ký chưa được cấu hình.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $pdo->exec("INSERT INTO form_submissions (submitted_at, status) VALUES (NOW(), 'pending')");
            $submissionId = (int) $pdo->lastInsertId();
            $insertValue = $pdo->prepare(
                'INSERT INTO form_submission_values (submission_id, field_id, value)
                 VALUES (:submission_id, :field_id, :value)'
            );

            foreach ($fields as $field) {
                $fieldId = (int) $field['id'];
                $answer = $answers[$fieldId] ?? '';
                $storedValue = $field['field_type'] === 'checkbox'
                    ? json_encode($answer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : (string) $answer;

                $insertValue->execute([
                    'submission_id' => $submissionId,
                    'field_id' => $fieldId,
                    'value' => $storedValue,
                ]);
            }

            $pdo->commit();
            redirect('recruitment.php?success=1');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Recruitment submission failed: ' . $exception->getMessage());
            $errors['form'] = 'Không thể lưu đăng ký lúc này. Vui lòng thử lại sau.';
        }
    }
}

$pageTitle = 'Tuyển thành viên';
$pageScripts = ['assets/js/navbar.min.js'];
require_once __DIR__ . '/includes/header.php';
?>
<section class="recruitment-hero">
    <div class="container text-center">
        <p class="text-uppercase fw-bold small opacity-75 mb-2">Cùng viết tiếp hành trình CIT</p>
        <h1 class="display-4 fw-bold">Ứng tuyển thành viên CIT</h1>
        <p class="lead text-wide mx-auto mb-0">Dựa trên những hoạt động fanpage như AETERNUM, HTTT, Birthday With CIT và Vinh danh CIT, chúng mình chào đón bạn yêu công nghệ và muốn trải nghiệm thực tế.</p>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="row align-items-end gy-3 mb-5">
            <div class="col-lg-7">
                <p class="section-eyebrow">Bạn sẽ nhận được</p>
                <h2 class="display-6 fw-bold mb-0">Lợi ích khi trở thành thành viên</h2>
            </div>
            <div class="col-lg-5">
                <p class="text-secondary text-flow text-flow-lg mb-0">
                    CIT là nơi bạn rèn kỹ năng, tham gia dự án thực tế và kết nối với những người cùng đam mê công nghệ.
                </p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <article class="mini-card h-100 text-center">
                    <div class="mini-card-icon"><i class="bi bi-gear-fill"></i></div>
                    <h3 class="h5 fw-bold mb-2">Rèn kỹ năng thực tế</h3>
                    <p class="text-secondary mb-0">Làm việc cùng dự án, tham gia workshop và tự tin cọ xát với thử thách công nghệ.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="mini-card h-100 text-center">
                    <div class="mini-card-icon"><i class="bi bi-people-fill"></i></div>
                    <h3 class="h5 fw-bold mb-2">Kết nối đồng đội</h3>
                    <p class="text-secondary mb-0">Xây dựng mạng lưới, tìm người cùng đam mê và học hỏi từ các anh chị đi trước.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="mini-card h-100 text-center">
                    <div class="mini-card-icon"><i class="bi bi-award"></i></div>
                    <h3 class="h5 fw-bold mb-2">Vinh danh cống hiến</h3>
                    <p class="text-secondary mb-0">Được ghi nhận trong các hoạt động CIT và cảm nhận tinh thần trân trọng đóng góp.</p>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="section-space section-muted">
    <div class="container">
        <div class="row align-items-end gy-3 mb-5">
            <div class="col-lg-7">
                <p class="section-eyebrow">Quy trình</p>
                <h2 class="display-6 fw-bold mb-0">3 bước để gia nhập CIT</h2>
            </div>
            <div class="col-lg-5">
                <p class="text-secondary text-flow text-flow-lg mb-0">
                    Thủ tục đơn giản, rõ ràng và nhanh chóng. Bạn chỉ cần gửi thông tin, CIT sẽ xem xét và liên hệ lại.
                </p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <article class="mini-card h-100 text-center">
                    <div class="mini-card-icon"><i class="bi bi-pencil-square"></i></div>
                    <h3 class="h5 fw-bold mb-2">1. Ứng tuyển</h3>
                    <p class="text-secondary mb-0">Gửi thông tin cá nhân và chia sẻ lý do bạn muốn tham gia CIT.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="mini-card h-100 text-center">
                    <div class="mini-card-icon"><i class="bi bi-chat-dots-fill"></i></div>
                    <h3 class="h5 fw-bold mb-2">2. Kiểm tra</h3>
                    <p class="text-secondary mb-0">Ban tuyển dụng sẽ xem xét, trao đổi và xác nhận với bạn trong thời gian sớm nhất.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="mini-card h-100 text-center">
                    <div class="mini-card-icon"><i class="bi bi-joystick"></i></div>
                    <h3 class="h5 fw-bold mb-2">3. Gắn kết</h3>
                    <p class="text-secondary mb-0">Gia nhập CIT, tham gia hoạt động và trải nghiệm tinh thần đồng đội cùng fanpage chính thức.</p>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="pb-5">
    <div class="container container-form">
        <div class="form-shell">
            <?php if (($_GET['success'] ?? '') === '1'): ?>
                <div class="alert alert-success p-4 mb-0" role="alert">
                    <h2 class="h4 alert-heading">Đăng ký thành công!</h2>
                    <p class="mb-3">Cảm ơn bạn đã quan tâm đến CIT Club. Chúng mình đã nhận được thông tin và sẽ liên hệ với bạn sớm nhất.</p>
                    <a href="index.php" class="btn btn-success">Về trang chủ</a>
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <span class="section-eyebrow">Đơn ứng tuyển</span>
                    <h2 class="h3 fw-bold mt-2">Hãy cho chúng mình biết về bạn</h2>
                    <p class="text-secondary mb-0">Các mục có dấu <span class="required-mark">*</span> là bắt buộc.</p>
                </div>

                <?php if (isset($errors['form'])): ?>
                    <div class="alert alert-danger" role="alert"><?= e($errors['form']) ?></div>
                <?php endif; ?>

                <?php if (!$fields): ?>
                    <div class="alert alert-info mb-0">Form đăng ký đang được chuẩn bị. Vui lòng quay lại sau.</div>
                <?php else: ?>
                    <form method="post" action="recruitment.php">
                        <?= csrf_field() ?>
                        <div class="row g-4">
                            <?php foreach ($fields as $field): ?>
                                <?php render_component('form-field', [
                                    'field' => $field,
                                    'answers' => $answers,
                                    'errors' => $errors,
                                ]); ?>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn btn-club btn-lg w-100 mt-4" type="submit">Gửi đơn đăng ký</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
