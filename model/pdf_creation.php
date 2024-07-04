<?php 

require_once "../model/storage_functions.php";
require_once "../model/invoice_functions.php";
require_once "../model/payment_functions.php";
require_once "../model/vendor_functions.php";
require_once "../model/order_functions.php";
require_once "../model/product_functions.php";
require_once "../model/order_products_functions.php";
require_once "../fpdf/fpdf.php";

// Create instance of FPDF
$pdf = new FPDF('L', 'mm', 'A5');
$pdf->AddPage();

// Set font
$pdf->SetFont('Arial', 'B', 12);

// Header
$pdf->Cell(130, 10, 'INVOICE IN', 0, 1, 'C');

// PT and Vendor details
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 10, 'PT', 0, 0);
$pdf->Cell(5, 10, ':', 0, 0);
$pdf->Cell(80, 10, $storageName, 0, 0);
$pdf->Cell(30, 10, 'Name Vendor', 0, 0);
$pdf->Cell(5, 10, ':', 0, 0);
$pdf->Cell(40, 10, $vendorName, 0, 1);

// Second row of details
$pdf->Cell(30, 10, 'NO. SJ', 0, 0);
$pdf->Cell(5, 10, ':', 0, 0);
$pdf->Cell(80, 10, $no_sj, 0, 0);
$pdf->Cell(30, 10, 'No PO', 0, 0);
$pdf->Cell(5, 10, ':', 0, 0);
$pdf->Cell(40, 10, $purchase_order, 0, 1);

// Third row of details
$pdf->Cell(30, 10, 'No Truk', 0, 0);
$pdf->Cell(5, 10, ':', 0, 0);
$pdf->Cell(80, 10, $no_truk, 0, 0);
$pdf->Cell(30, 10, 'Tgl Invoice', 0, 0);
$pdf->Cell(5, 10, ':', 0, 0);
$pdf->Cell(40, 10, $invoice_date, 0, 1);

// Fourth row of details
$pdf->Cell(30, 10, 'NO. LPB', 0, 0);
$pdf->Cell(5, 10, ':', 0, 0);
$pdf->Cell(80, 10, $no_LPB, 0, 0);
$pdf->Cell(30, 10, 'NO.Invoice Vendor', 0, 0);
$pdf->Cell(5, 10, ':', 0, 0);
$pdf->Cell(40, 10, $no_invoice, 0, 1);

// Add product table
$pdf->Ln(1);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 10, 'No', 1);
$pdf->Cell(30, 10, 'KD', 1);
$pdf->Cell(50, 10, 'Material', 1);
$pdf->Cell(20, 10, 'QTY', 1);
$pdf->Cell(20, 10, 'UOM', 1);
$pdf->Cell(30, 10, 'Price/uom', 1);
$pdf->Cell(30, 10, 'Nominal', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);
for($i = 0; $i < count($productCodes); $i++){
    $pdf->Cell(10, 10, $i, 1);
    $pdf->Cell(30, 10, $productCodes[$i], 1);
    $pdf->Cell(50, 10, $productNames[$i], 1);
    $pdf->Cell(20, 10, $qtys[$i], 1);
    $pdf->Cell(20, 10, $uoms[$i], 1);
    $pdf->Cell(30, 10, $price_per_uom[$i], 1);
    $pdf->Cell(30, 10, ($qtys[$i] * $price_per_uom[$i]), 1);
    $total_amount += ($qtys[$i] * $price_per_uom[$i]);
    $pdf->Ln();
}

$taxPPN = $total_amount * 0.11;
$pay_amount = $total_amount + $taxPPN;

// Footer
$pdf->Ln(1);
$pdf->Cell(30, 10, 'NO. Faktur', 0, 0);
$pdf->Cell(70, 10, ': ' . $no_faktur, 0, 0);
$pdf->Cell(30, 10, 'Total Nilai Barang', 0, 0);
$pdf->Cell(30, 10, ': ' . $total_amount, 0, 1);

$pdf->Cell(100, 10, '', 0, 0);
$pdf->Cell(30, 10, 'PPN 11%', 0, 0);
$pdf->Cell(30, 10, ': ' . $taxPPN, 0, 1);

$pdf->Cell(100, 10, '', 0, 0);
$pdf->Cell(40, 10, 'NIlai Yg Harus Dibayar', 0, 0);
$pdf->Cell(30, 10, ': ' . $pay_amount, 0, 1);

// Output the PDF
header('Content-Type: application/pdf');
$pdf->Output('I', 'invoice.pdf');
?>