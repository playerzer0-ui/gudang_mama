<?php

require_once "../vendor/autoload.php";
require_once "../model/order_products_functions.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$letters = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP'];

function report_stock_excel($storageCode, $month, $year) {
    global $letters;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $data = generateSaldo($storageCode, $month, $year);
    $count = 1;

    $spreadsheet->getProperties()->setCreator("user")
    ->setLastModifiedBy("user")
    ->setTitle("report_stock_" . $storageCode . "_" . $month . "_" . $year)
    ->setSubject("report_stock_" . $storageCode . "_" . $month . "_" . $year)
    ->setDescription("monthly report generated with storage")
    ->setKeywords("Office Excel  open XML php")
    ->setCategory("report file");

    for($i = 0; $i < 36; $i++){
        $sheet->getColumnDimension($letters[$i])->setAutoSize(true);
    }
    //header
    $sheet->mergeCells("A1:G1");
    $sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A1")->getFont()->setBold(5)->setSize(36);
    $sheet->setCellValue("A1", "REPORT STOCK: " . $storageCode);
    $sheet->setCellValue("A2", "MONTH: " . $month);
    $sheet->setCellValue("A3", "YEAR: " . $year);

    //Table head
    $sheet->mergeCells("A5:A7");
    $sheet->setCellValue("A5", "no.");
    $sheet->getStyle("A5")->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);
    $sheet->mergeCells("B5:B7");
    $sheet->setCellValue("B5", "KD");
    $sheet->getStyle("B5")->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);
    $sheet->mergeCells("C5:C7");
    $sheet->setCellValue("C5", "material");
    $sheet->getStyle("C5")->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);
    $sheet->mergeCells("D5:F6");
    $sheet->setCellValue("D5", "saldo awal");
    $sheet->getStyle("D5")->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);
    $sheet->setCellValue("D7", "qty");
    $sheet->setCellValue("E7", "h/qty");
    $sheet->setCellValue("F7", "rupiah");
    //penerimaan
    $sheet->mergeCells("G5:R5");
    $sheet->setCellValue("G5", "PENERIMAAN");
    $sheet->getStyle("G5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->mergeCells("G6:I6");
    $sheet->setCellValue("G6", "pembelian");
    $sheet->getStyle("G6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->mergeCells("J6:L6");
    $sheet->setCellValue("J6", "pindah PT");
    $sheet->getStyle("J6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->mergeCells("M6:O6");
    $sheet->setCellValue("M6", "repack");
    $sheet->getStyle("M6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->mergeCells("P6:R6");
    $sheet->setCellValue("P6", "totalIn");
    $sheet->getStyle("P6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue("G7", "qty");
    $sheet->setCellValue("H7", "h/qty");
    $sheet->setCellValue("I7", "rupiah");
    $sheet->setCellValue("J7", "qty");
    $sheet->setCellValue("K7", "h/qty");
    $sheet->setCellValue("L7", "rupiah");
    $sheet->setCellValue("M7", "qty");
    $sheet->setCellValue("N7", "h/qty");
    $sheet->setCellValue("O7", "rupiah");
    $sheet->setCellValue("P7", "qty");
    $sheet->setCellValue("Q7", "h/qty");
    $sheet->setCellValue("R7", "rupiah");
    //barang_siap_dijual
    $sheet->mergeCells("S5:U6");
    $sheet->setCellValue("S5", "barang siap dijual");
    $sheet->getStyle("S5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue("S7", "qty");
    $sheet->setCellValue("T7", "h/qty");
    $sheet->setCellValue("U7", "rupiah");
    //pengeluaran
    $sheet->mergeCells("V5:AG5");
    $sheet->setCellValue("V5", "PENGELUARAN");
    $sheet->getStyle("V5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->mergeCells("V6:X6");
    $sheet->setCellValue("V6", "penjualan");
    $sheet->getStyle("V6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->mergeCells("Y6:AA6");
    $sheet->setCellValue("Y6", "pindah PT");
    $sheet->getStyle("Y6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->mergeCells("AB6:AD6");
    $sheet->setCellValue("AB6", "repack");
    $sheet->getStyle("AB6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->mergeCells("AE6:AG6");
    $sheet->setCellValue("AE6", "totalOut");
    $sheet->getStyle("AE6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue("V7", "qty");
    $sheet->setCellValue("W7", "h/qty");
    $sheet->setCellValue("X7", "rupiah");
    $sheet->setCellValue("Y7", "qty");
    $sheet->setCellValue("Z7", "h/qty");
    $sheet->setCellValue("AA7", "rupiah");
    $sheet->setCellValue("AB7", "qty");
    $sheet->setCellValue("AC7", "h/qty");
    $sheet->setCellValue("AD7", "rupiah");
    $sheet->setCellValue("AE7", "qty");
    $sheet->setCellValue("AF7", "h/qty");
    $sheet->setCellValue("AG7", "rupiah");
    //saldo_akhir
    $sheet->mergeCells("AH5:AJ6");
    $sheet->setCellValue("AH5", "saldo akhir");
    $sheet->getStyle("AH5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue("AH7", "qty");
    $sheet->setCellValue("AI7", "h/qty");
    $sheet->setCellValue("AJ7", "rupiah");

    $cell = 8;
    $indonesianNumberFormat = '#,##0';
    foreach($data as $key => $val){
        if($key == "0") continue;

        $sheet->setCellValue("A".$cell, $count++);
        $sheet->setCellValue("B".$cell, $val["productCode"]);
        $sheet->setCellValue("C".$cell, $val["productName"]);
        $sheet->setCellValue("D".$cell, $val["saldo_awal"]["totalQty"]);
        $sheet->setCellValue("E".$cell, $val["saldo_awal"]["price_per_qty"]);
        $sheet->setCellValue("F".$cell, $val["saldo_awal"]["totalPrice"]);

        $sheet->setCellValue("G".$cell, $val["penerimaan"]["pembelian"]["totalQty"]);
        $sheet->setCellValue("H".$cell, $val["penerimaan"]["pembelian"]["price_per_qty"]);
        $sheet->setCellValue("I".$cell, $val["penerimaan"]["pembelian"]["totalPrice"]);
        $sheet->setCellValue("J".$cell, $val["penerimaan"]["movingIn"]["totalQty"]);
        $sheet->setCellValue("K".$cell, $val["penerimaan"]["movingIn"]["price_per_qty"]);
        $sheet->setCellValue("L".$cell, $val["penerimaan"]["movingIn"]["totalPrice"]);
        $sheet->setCellValue("M".$cell, $val["penerimaan"]["repackIn"]["totalQty"]);
        $sheet->setCellValue("N".$cell, $val["penerimaan"]["repackIn"]["price_per_qty"]);
        $sheet->setCellValue("O".$cell, $val["penerimaan"]["repackIn"]["totalPrice"]);
        $sheet->setCellValue("P".$cell, $val["penerimaan"]["totalIn"]["totalQty"]);
        $sheet->setCellValue("Q".$cell, $val["penerimaan"]["totalIn"]["price_per_qty"]);
        $sheet->setCellValue("R".$cell, $val["penerimaan"]["totalIn"]["totalPrice"]);

        $sheet->setCellValue("S".$cell, $val["barang_siap_dijual"]["totalQty"]);
        $sheet->setCellValue("T".$cell, $val["barang_siap_dijual"]["price_per_qty"]);
        $sheet->setCellValue("U".$cell, $val["barang_siap_dijual"]["totalPrice"]);

        $sheet->setCellValue("V".$cell, $val["pengeluaran"]["penjualan"]["totalQty"]);
        $sheet->setCellValue("W".$cell, $val["pengeluaran"]["penjualan"]["price_per_qty"]);
        $sheet->setCellValue("X".$cell, $val["pengeluaran"]["penjualan"]["totalPrice"]);
        $sheet->setCellValue("Y".$cell, $val["pengeluaran"]["movingOut"]["totalQty"]);
        $sheet->setCellValue("Z".$cell, $val["pengeluaran"]["movingOut"]["price_per_qty"]);
        $sheet->setCellValue("AA".$cell, $val["pengeluaran"]["movingOut"]["totalPrice"]);
        $sheet->setCellValue("AB".$cell, $val["pengeluaran"]["repackOut"]["totalQty"]);
        $sheet->setCellValue("AC".$cell, $val["pengeluaran"]["repackOut"]["price_per_qty"]);
        $sheet->setCellValue("AD".$cell, $val["pengeluaran"]["repackOut"]["totalPrice"]);
        $sheet->setCellValue("AE".$cell, $val["pengeluaran"]["totalOut"]["totalQty"]);
        $sheet->setCellValue("AF".$cell, $val["pengeluaran"]["totalOut"]["price_per_qty"]);
        $sheet->setCellValue("AG".$cell, $val["pengeluaran"]["totalOut"]["totalPrice"]);

        $sheet->setCellValue("AH".$cell, $val["saldo_akhir"]["totalQty"]);
        $sheet->setCellValue("AI".$cell, $val["saldo_akhir"]["price_per_qty"]);
        $sheet->setCellValue("AJ".$cell, $val["saldo_akhir"]["totalPrice"]);

        
        for($i = 3; $i < 36; $i++){
            $sheet->getStyle($letters[$i].$cell)->getNumberFormat()->setFormatCode($indonesianNumberFormat);
        }
        $cell++;
    }


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

    // Delete the file after sending it to the client
    unlink($filePath);
}

?>