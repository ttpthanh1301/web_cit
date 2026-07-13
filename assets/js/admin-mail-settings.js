(() => {
    const form = document.querySelector('#email-settings-form');
    if (!form) return;

    const senderName = form.querySelector('[name="mail_sender_name"]');
    const replyTo = form.querySelector('[name="mail_reply_to"]');
    const csrf = form.querySelector('[name="csrf_token"]');

    const escapeHtml = (value) => String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');

    const sampleValues = {
        ho_ten: 'Nguyễn Minh Anh',
        email: 'minhanh@example.com',
        ma_don: 'CIT-000123',
        ngay_nop: new Intl.DateTimeFormat('vi-VN', { dateStyle: 'short', timeStyle: 'short' }).format(new Date()),
        ten_clb: 'CLB Công nghệ CIT'
    };

    const renderSample = (value) => String(value)
        .replace(/\{\{\s*field:([a-zA-Z0-9_]+)\s*\}\}/g, (_, name) => escapeHtml(sampleValues[name] || `Dữ liệu ${name}`))
        .replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, (_, name) => escapeHtml(sampleValues[name] || ''));

    const panels = [...form.querySelectorAll('[data-template-panel]')];
    const syncPanel = (panel) => {
        const editor = panel.querySelector('[data-email-editor]');
        const body = panel.querySelector('[data-email-body]');
        const subject = panel.querySelector('[data-email-subject]');
        const frame = panel.querySelector('[data-email-preview]');
        if (panel.dataset.editorMode !== 'html') body.value = editor.innerHTML.trim();
        const subjectPreview = renderSample(subject.value || 'Chưa có tiêu đề');
        const bodyPreview = renderSample(body.value || '<p>Chưa có nội dung.</p>');
        frame.srcdoc = `<!doctype html><html lang="vi"><head><meta charset="utf-8"><style>body{margin:0;padding:24px;color:#1e293b;background:#fff;font:15px/1.65 Arial,sans-serif}header{margin-bottom:22px;padding-bottom:14px;border-bottom:1px solid #e2e8f0}header small{display:block;margin-bottom:5px;color:#64748b;font-size:11px;text-transform:uppercase}header strong{font-size:17px}a{color:#2563eb}h1,h2,h3{line-height:1.25}</style></head><body><header><small>Tiêu đề</small><strong>${subjectPreview}</strong></header>${bodyPreview}</body></html>`;
    };

    panels.forEach((panel) => {
        const editor = panel.querySelector('[data-email-editor]');
        const subject = panel.querySelector('[data-email-subject]');
        const imageInput = panel.querySelector('[data-email-image-input]');
        const uploadButton = panel.querySelector('[data-upload-image]');
        const uploadStatus = panel.querySelector('[data-upload-status]');
        const body = panel.querySelector('[data-email-body]');
        const htmlToggle = panel.querySelector('[data-toggle-html]');
        const htmlHint = panel.querySelector('[data-html-hint]');
        const formatButtons = [...panel.querySelectorAll('[data-command]')];
        let savedRange = null;
        let updateTimer;
        const scheduleSync = () => {
            window.clearTimeout(updateTimer);
            updateTimer = window.setTimeout(() => syncPanel(panel), 120);
        };
        editor.addEventListener('input', scheduleSync);
        body.addEventListener('input', scheduleSync);
        subject.addEventListener('input', scheduleSync);
        const insertIntoSource = (value) => {
            const start = body.selectionStart;
            const end = body.selectionEnd;
            body.setRangeText(value, start, end, 'end');
            body.dispatchEvent(new Event('input', { bubbles: true }));
            body.focus();
        };
        const rememberCaret = () => {
            const selection = window.getSelection();
            if (selection && selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                if (editor.contains(range.commonAncestorContainer)) savedRange = range.cloneRange();
            }
        };
        editor.addEventListener('keyup', rememberCaret);
        editor.addEventListener('mouseup', rememberCaret);

        formatButtons.forEach((button) => {
            button.addEventListener('click', () => {
                editor.focus();
                const command = button.dataset.command;
                if (command === 'createLink') {
                    const url = window.prompt('Nhập liên kết (https://...)');
                    if (!url) return;
                    document.execCommand(command, false, url);
                } else {
                    document.execCommand(command, false, null);
                }
                syncPanel(panel);
            });
        });

        panel.querySelectorAll('[data-email-token]').forEach((button) => {
            button.addEventListener('click', () => {
                const token = button.dataset.emailToken || '';
                if (panel.dataset.editorMode === 'html') {
                    insertIntoSource(token);
                    return;
                }
                editor.focus();
                document.execCommand('insertText', false, token);
                syncPanel(panel);
            });
        });

        htmlToggle.addEventListener('click', () => {
            const enteringHtml = panel.dataset.editorMode !== 'html';
            if (enteringHtml) {
                body.value = editor.innerHTML.trim();
                panel.dataset.editorMode = 'html';
            } else {
                editor.innerHTML = body.value;
                panel.dataset.editorMode = 'visual';
            }
            editor.classList.toggle('d-none', enteringHtml);
            body.classList.toggle('d-none', !enteringHtml);
            htmlHint.classList.toggle('d-none', !enteringHtml);
            htmlToggle.classList.toggle('is-active', enteringHtml);
            htmlToggle.setAttribute('aria-pressed', enteringHtml ? 'true' : 'false');
            htmlToggle.setAttribute('title', enteringHtml ? 'Quay lại trình soạn trực quan' : 'Chỉnh sửa HTML');
            formatButtons.forEach((button) => { button.disabled = enteringHtml; });
            (enteringHtml ? body : editor).focus();
            syncPanel(panel);
        });

        uploadButton.addEventListener('click', () => {
            if (panel.dataset.editorMode === 'html') {
                body.focus();
            } else {
                editor.focus();
                rememberCaret();
            }
            imageInput.click();
        });
        imageInput.addEventListener('change', async () => {
            const file = imageInput.files?.[0];
            if (!file) return;
            if (file.size > 4 * 1024 * 1024) {
                uploadStatus.className = 'mail-upload-status is-error';
                uploadStatus.textContent = 'Ảnh không được vượt quá 4 MB.';
                imageInput.value = '';
                return;
            }
            uploadButton.disabled = true;
            uploadButton.classList.add('is-uploading');
            uploadStatus.className = 'mail-upload-status is-loading';
            uploadStatus.textContent = 'Đang tối ưu và tải ảnh lên...';
            const payload = new FormData();
            payload.append('csrf_token', csrf.value);
            payload.append('image', file);
            try {
                const response = await fetch('email-image-upload.php', { method: 'POST', body: payload, credentials: 'same-origin' });
                const text = await response.text();
                const data = JSON.parse(text);
                if (!response.ok || !data.success) throw new Error(data.message || 'Không thể tải ảnh lên.');

                const safeUrl = escapeHtml(data.url);
                const width = Math.max(1, Math.min(1200, Number(data.width || 1200)));
                const imageHtml = `<p><img src="${safeUrl}" alt="Hình ảnh trong email" width="${width}" style="max-width:100%;height:auto;display:inline-block;"></p>`;
                if (panel.dataset.editorMode === 'html') {
                    insertIntoSource(imageHtml);
                } else {
                    editor.focus();
                    if (savedRange) {
                        const selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(savedRange);
                    }
                    document.execCommand('insertHTML', false, imageHtml);
                }
                syncPanel(panel);
                uploadStatus.className = 'mail-upload-status is-success';
                uploadStatus.textContent = 'Đã tối ưu và chèn ảnh vào email.';
            } catch (error) {
                uploadStatus.className = 'mail-upload-status is-error';
                uploadStatus.textContent = error.message || 'Không thể tải ảnh lên.';
            } finally {
                imageInput.value = '';
                uploadButton.disabled = false;
                uploadButton.classList.remove('is-uploading');
            }
        });

        panel.querySelector('[data-send-test]').addEventListener('click', async (event) => {
            const button = event.currentTarget;
            const status = panel.querySelector('[data-test-status]');
            const recipient = panel.querySelector('[data-test-recipient]').value.trim();
            syncPanel(panel);
            status.className = 'mail-test-status is-loading';
            status.textContent = 'Đang gửi email thử...';
            button.disabled = true;

            const payload = new FormData();
            payload.append('csrf_token', csrf.value);
            payload.append('recipient', recipient);
            payload.append('sender_name', senderName.value);
            payload.append('reply_to', replyTo.value);
            payload.append('subject', subject.value);
            payload.append('body', panel.querySelector('[data-email-body]').value);
            try {
                const response = await fetch('email-test-send.php', { method: 'POST', body: payload, credentials: 'same-origin' });
                const data = await response.json();
                status.className = `mail-test-status ${data.success ? 'is-success' : 'is-error'}`;
                status.textContent = data.message || (data.success ? 'Đã gửi.' : 'Không thể gửi.');
            } catch (error) {
                status.className = 'mail-test-status is-error';
                status.textContent = 'Mất kết nối khi gửi email thử.';
            } finally {
                button.disabled = false;
            }
        });

        syncPanel(panel);
    });

    form.addEventListener('submit', () => panels.forEach(syncPanel));
})();
