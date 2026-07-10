<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$statement = $pdo->prepare('SELECT id, submitted_at, status FROM form_submissions WHERE id = :id');
$statement->execute(['id' => $id]);
$submission = $statement->fetch();
if (!$submission) {
    http_response_code(404);
    exit('Không tìm thấy đơn đăng ký.');
}
$returnQuery = isset($_GET['return']) && is_string($_GET['return']) ? $_GET['return'] : '';
if (preg_match('/[^a-zA-Z0-9_=&%+.-]/', $returnQuery)) {
    $returnQuery = '';
}
$valueStatement = $pdo->prepare(
    'SELECT ff.field_label, ff.field_type, fsv.value
     FROM form_fields ff LEFT JOIN form_submission_values fsv
       ON fsv.field_id = ff.id AND fsv.submission_id = :submission_id
     ORDER BY ff.sort_order, ff.id'
);
$valueStatement->execute(['submission_id' => $id]);
$answers = $valueStatement->fetchAll();
$pageTitle = 'Chi tiết đơn #' . $id;
require __DIR__ . '/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><a class="text-decoration-none" href="members.php<?= $returnQuery ? '?' . e($returnQuery) : '' ?>">← Quay lại danh sách</a><h1 class="h3 fw-bold mt-2 mb-1">Đơn đăng ký #<?= $id ?></h1><p class="text-secondary mb-0">Nộp lúc <?= e(date('d/m/Y H:i', strtotime($submission['submitted_at']))) ?></p></div>
    <?php render_component('admin/status-badge', ['status' => $submission['status'], 'class' => 'fs-6']); ?>
</div>
<div class="row g-4">
    <div class="col-lg-8"><section class="content-card"><h2 class="h5 fw-bold mb-4">Câu trả lời</h2><?php if (!$answers): ?><p class="text-secondary mb-0">Form hiện không có field.</p><?php else: ?><dl class="row mb-0"><?php foreach ($answers as $answer): ?><dt class="col-sm-4 mb-1"><?= e($answer['field_label']) ?></dt><dd class="col-sm-8 mb-4 text-break"><?= nl2br(e(format_submission_value($answer['value'], $answer['field_type']))) ?></dd><?php endforeach; ?></dl><?php endif; ?></section></div>
    <div class="col-lg-4"><section class="content-card"><h2 class="h5 fw-bold mb-3">Xử lý đơn</h2>
        <form method="post" action="member-approve.php" class="d-grid gap-2 mb-4"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="return" value="<?= e($returnQuery) ?>"><button class="btn btn-success" name="status" value="approved" type="submit">Duyệt đơn</button><button class="btn btn-warning" name="status" value="pending" type="submit">Chuyển về chờ duyệt</button><button class="btn btn-outline-danger" name="status" value="rejected" type="submit">Từ chối đơn</button></form>
        <hr><form method="post" action="member-delete.php" onsubmit="return confirm('Xóa vĩnh viễn đơn đăng ký này?');"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="return" value="<?= e($returnQuery) ?>"><button class="btn btn-danger w-100" type="submit">Xóa đơn</button></form>
    </section></div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
