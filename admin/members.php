<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';

$fields = $pdo->query('SELECT id, field_label, field_type FROM form_fields ORDER BY sort_order, id')->fetchAll();
$filters = submission_filters($_GET);
$params = [];
$where = submission_filter_sql($filters, $params);
$countStatement = $pdo->prepare('SELECT COUNT(*) FROM form_submissions fs' . $where);
$countStatement->execute($params);
$total = (int) $countStatement->fetchColumn();
$perPage = 20;
$totalPages = max(1, (int) ceil($total / $perPage));
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$listStatement = $pdo->prepare(
    'SELECT fs.id, fs.submitted_at, fs.status FROM form_submissions fs' . $where .
    ' ORDER BY fs.submitted_at DESC, fs.id DESC LIMIT :limit OFFSET :offset'
);
foreach ($params as $key => $value) {
    $listStatement->bindValue(':' . $key, $value);
}
$listStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
$listStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStatement->execute();
$submissions = $listStatement->fetchAll();

$valuesBySubmission = [];
if ($submissions) {
    $ids = array_column($submissions, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $valueStatement = $pdo->prepare("SELECT submission_id, field_id, value FROM form_submission_values WHERE submission_id IN ({$placeholders})");
    $valueStatement->execute($ids);
    foreach ($valueStatement as $valueRow) {
        $valuesBySubmission[(int) $valueRow['submission_id']][(int) $valueRow['field_id']] = $valueRow['value'];
    }
}

$filterQuery = query_string($filters);
$pageTitle = 'Danh sách đăng ký';
require __DIR__ . '/header.php';
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div><h1 class="h3 fw-bold mb-1">Danh sách đăng ký</h1><p class="text-secondary mb-0">Tổng cộng <?= number_format($total) ?> đơn phù hợp.</p></div>
    <a class="btn btn-success" href="export-excel.php<?= $filterQuery ? '?' . e($filterQuery) : '' ?>">Xuất Excel</a>
</div>
<section class="content-card mb-4">
    <?php render_component('admin/filter-form', ['filters' => $filters]); ?>
</section>
<section class="content-card">
    <?php if (!$submissions): ?><div class="alert alert-info mb-0">Không tìm thấy đơn đăng ký phù hợp.</div><?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-dynamic align-middle mb-0">
                <thead><tr><th>#</th><th>Ngày nộp</th><th>Trạng thái</th><?php foreach ($fields as $field): ?><th><?= e($field['field_label']) ?></th><?php endforeach; ?><th>Thao tác</th></tr></thead>
                <tbody>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?= (int) $submission['id'] ?></td><td><?= e(date('d/m/Y H:i', strtotime($submission['submitted_at']))) ?></td><td><?php render_component('admin/status-badge', ['status' => $submission['status']]); ?></td>
                        <?php foreach ($fields as $field): $display = format_submission_value($valuesBySubmission[(int) $submission['id']][(int) $field['id']] ?? null, $field['field_type']); ?><td><span class="text-truncate-cell" title="<?= e($display) ?>"><?= e($display) ?></span></td><?php endforeach; ?>
                        <td>
                            <?php render_component('admin/table-actions', [
                                'actions' => [
                                    'detail' => [
                                        'url' => 'member-detail.php?id=' . (int) $submission['id'] . '&return=' . urlencode($filterQuery . ($filterQuery ? '&' : '') . 'page=' . $page),
                                        'label' => 'Chi tiết'
                                    ]
                                ]
                            ]); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php render_component('pagination', [
            'baseUrl' => 'members.php',
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
        ]); ?>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/footer.php'; ?>
