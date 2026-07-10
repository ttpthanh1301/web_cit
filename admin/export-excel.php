<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';

// Giới hạn bộ nhớ và thời gian cho export trên hosting nhỏ
ini_set('memory_limit', '128M');   // không vượt RAM hosting
set_time_limit(60);                // tối đa 60 giây

const EXPORT_ROW_LIMIT = 2000;    // giới hạn số đơn xuất

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    exit('Chưa cài PhpSpreadsheet. Vui lòng chạy composer install.');
}
require_once $autoload;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$fields = $pdo->query('SELECT id, field_label, field_type FROM form_fields ORDER BY sort_order, id')->fetchAll();
$fieldColumns = [];
$fieldTypes = [];
foreach ($fields as $index => $field) {
    $fieldColumns[(int) $field['id']] = $index + 4;
    $fieldTypes[(int) $field['id']] = $field['field_type'];
}

$filters = submission_filters($_GET);
$params = [];
$where = submission_filter_sql($filters, $params);

// Đếm trước để cảnh báo nếu vượt giới hạn
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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Danh sách đăng ký');
$headers = ['Mã đơn', 'Ngày nộp', 'Trạng thái'];
foreach ($fields as $field) {
    $headers[] = $field['field_label'];
}
$headerColumn = 1;
foreach ($headers as $header) {
    $sheet->setCellValueExplicit(
        Coordinate::stringFromColumnIndex($headerColumn++) . '1',
        (string) $header,
        DataType::TYPE_STRING
    );
}
$lastColumn = Coordinate::stringFromColumnIndex(count($headers));
$sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
$sheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0B2F5B');
$sheet->getStyle("A1:{$lastColumn}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$rowNumber = 1;
$currentId = null;
foreach ($statement as $record) {
    $submissionId = (int) $record['id'];
    if ($currentId !== $submissionId) {
        $currentId = $submissionId;
        $rowNumber++;
        $sheet->setCellValue("A{$rowNumber}", $submissionId);
        $sheet->setCellValue("B{$rowNumber}", date('d/m/Y H:i', strtotime($record['submitted_at'])));
        $sheet->setCellValue("C{$rowNumber}", status_label($record['status']));
    }
    $fieldId = $record['field_id'] !== null ? (int) $record['field_id'] : 0;
    if (isset($fieldColumns[$fieldId])) {
        $column = Coordinate::stringFromColumnIndex($fieldColumns[$fieldId]);
        $sheet->setCellValueExplicit(
            $column . $rowNumber,
            format_submission_value($record['value'], $fieldTypes[$fieldId]),
            DataType::TYPE_STRING
        );
    }
}

$sheet->freezePane('A2');
$sheet->setAutoFilter("A1:{$lastColumn}{$rowNumber}");
$sheet->getStyle("A1:{$lastColumn}" . max(1, $rowNumber))->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
$sheet->getColumnDimension('A')->setWidth(12);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(16);
for ($columnIndex = 4; $columnIndex <= count($headers); $columnIndex++) {
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setWidth(28);
}

// Thêm cảnh báo nếu bị giới hạn
if ($limited) {
    $sheet->getCell('A' . ($rowNumber + 2))->
        setValue("Chú ý: Chỉ xuất {$rowNumber} đầu tiên (vượt giới hạn " . EXPORT_ROW_LIMIT . " đơn). Dùng bộ lọc để xuất theo lô.");
}

$filename = 'danh-sach-dang-ky-' . date('Y-m-d-His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0, no-store, no-cache, must-revalidate');
// Xả buffer trước khi xuất file
while (ob_get_level()) { ob_end_clean(); }
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
$spreadsheet->disconnectWorksheets();
unset($spreadsheet);
exit;
