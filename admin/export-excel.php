<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';

ini_set('memory_limit', '96M');
set_time_limit(45);

const EXPORT_ROW_LIMIT = 2000;

function export_csv_value(?string $value, string $fieldType): string
{
    $formatted = format_submission_value($value, $fieldType);
    return $formatted === '—' ? '' : $formatted;
}

$fields = $pdo->query('SELECT id, field_label, field_type FROM form_fields ORDER BY sort_order, id')->fetchAll();
$fieldLabels = [];
$fieldTypes = [];
$fieldColumnIndexes = [];
foreach ($fields as $field) {
    $fieldId = (int) $field['id'];
    $fieldLabels[$fieldId] = (string) $field['field_label'];
    $fieldTypes[$fieldId] = (string) $field['field_type'];
    $fieldColumnIndexes[$fieldId] = count($fieldColumnIndexes) + 3;
}

$filters = submission_filters($_GET);
$params = [];
$where = submission_filter_sql($filters, $params);

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM form_submissions fs' . $where);
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$limited = $totalRows > EXPORT_ROW_LIMIT;

$statement = $pdo->prepare(
    'SELECT limited.id, limited.submitted_at, limited.status, fsv.field_id, fsv.value
     FROM (
        SELECT fs.id, fs.submitted_at, fs.status
        FROM form_submissions fs' . $where . '
        ORDER BY fs.submitted_at DESC, fs.id DESC
        LIMIT ' . EXPORT_ROW_LIMIT . '
     ) limited
     LEFT JOIN form_submission_values fsv ON fsv.submission_id = limited.id
     ORDER BY limited.submitted_at DESC, limited.id DESC, fsv.field_id ASC'
);
$statement->execute($params);

$filename = 'danh-sach-dang-ky-' . date('Y-m-d-His') . '.csv';
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');

$output = fopen('php://output', 'wb');
if ($output === false) {
    http_response_code(500);
    exit('Không thể tạo file export.');
}

fwrite($output, "\xEF\xBB\xBF");
$headers = ['Mã đơn', 'Ngày nộp', 'Trạng thái'];
foreach ($fieldLabels as $label) {
    $headers[] = $label;
}
fputcsv($output, $headers);

$currentId = null;
$currentRow = [];
$exportedRows = 0;

$flushRow = static function () use (&$currentRow, $output, &$exportedRows): void {
    if ($currentRow === []) {
        return;
    }

    fputcsv($output, $currentRow);
    $exportedRows++;
};

foreach ($statement as $record) {
    $submissionId = (int) $record['id'];
    if ($currentId !== $submissionId) {
        $flushRow();
        $currentId = $submissionId;
        $currentRow = [
            $submissionId,
            date('d/m/Y H:i', strtotime((string) $record['submitted_at'])),
            status_label((string) $record['status']),
        ];
        foreach ($fieldLabels as $_) {
            $currentRow[] = '';
        }
    }

    $fieldId = $record['field_id'] !== null ? (int) $record['field_id'] : 0;
    if (isset($fieldTypes[$fieldId], $fieldColumnIndexes[$fieldId])) {
        $currentRow[$fieldColumnIndexes[$fieldId]] = export_csv_value(
            $record['value'] !== null ? (string) $record['value'] : null,
            $fieldTypes[$fieldId]
        );
    }
}
$flushRow();

if ($limited) {
    fputcsv($output, []);
    fputcsv($output, ["Chú ý: Chỉ xuất {$exportedRows} đơn đầu tiên trong tổng {$totalRows} đơn. Dùng bộ lọc để xuất theo lô."]);
}

fclose($output);
exit;
