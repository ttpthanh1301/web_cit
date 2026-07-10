<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/form-fields.php';
require_once __DIR__ . '/../includes/submissions.php';
require_once __DIR__ . '/../includes/db.php';

ensure_session_started();
if (empty($_SESSION['admin_id'])) {
    set_flash('warning', 'Vui lòng đăng nhập để tiếp tục.');
    redirect('login.php');
}
