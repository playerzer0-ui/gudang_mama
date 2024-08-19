<?php

require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function report_stock_excel($storageCode, $month, $year) {
    $spreadsheet = new Spreadsheet();
    $filename = "report_stock_" . $storageCode . "_" . $month . "_" . $year . ".xlsx";
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $spreadsheet->getProperties()->setCreator("user")
    ->setLastModifiedBy("user")
    ->setTitle("report_stock_" . $storageCode . "_" . $month . "_" . $year)
    ->setSubject("report_stock_" . $storageCode . "_" . $month . "_" . $year)
    ->setDescription("monthly report generated with storage")
    ->setKeywords("Office Excel  open XML php")
    ->setCategory("report file");


    $sheet->setCellValue("A1", "hello");


    $filePath = "../files/report_stock_" . $storageCode . "_" . $month . "_" . $year . ".xlsx";
    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    ob_end_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate, post-check, pre-check');
    header('Pragma: public');
    header('Content-Type: application/force-download');
    header('Content-Type: application/download');
    header('Content-Length: ' . filesize($filePath));
    header('Expires: 0');
    readfile($filePath);
}




?>