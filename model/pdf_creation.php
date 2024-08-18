<?php 

require_once "../model/storage_functions.php";
require_once "../model/invoice_functions.php";
require_once "../model/payment_functions.php";
require_once "../model/vendor_functions.php";
require_once "../model/order_functions.php";
require_once "../model/product_functions.php";
require_once "../model/order_products_functions.php";
require_once "../fpdf/fpdf.php";

function displayImage($pdf, $storageCode){
    $basePath = '../img/';
    $extensions = ['.png', '.jpg']; // List of possible extensions
    $imagePath = null;

    // Loop through the extensions to find the correct file
    foreach ($extensions as $extension) {
        $potentialPath = $basePath . $storageCode . $extension;
        if (file_exists($potentialPath)) {
            $imagePath = $potentialPath;
            break;
        }
    }

    // If no image is found, use the fallback image
    if ($imagePath === null) {
        $imagePath = $basePath . 'default.png'; // Fallback image
    }

    $pdf->Image($imagePath, 1, 1, 40, 20);
}

function headerIn($pdf, $storageName, $vendorName, $customerName, $no_sj, $purchase_order, $customerAddress, $no_truk, $npwp, $invoice_date, $no_LPB, $no_invoice, $status){
    // PT and Vendor details
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(30, 10, 'PT', 0, 0);
    $pdf->Cell(5, 10, ':', 0, 0);
    $pdf->Cell(80, 10, $storageName, 0, 0);
    if($status == "in"){
        $pdf->Cell(30, 10, 'Name Vendor', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(40, 10, $vendorName, 0, 1);
    }
    else{
        $pdf->Cell(30, 10, 'Name Customer', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(40, 10, $customerName, 0, 1);
    }

    // Second row of details
    $pdf->Cell(30, 10, 'NO. SJ', 0, 0);
    $pdf->Cell(5, 10, ':', 0, 0);
    $pdf->Cell(80, 10, $no_sj, 0, 0);
    if($status == "in"){
        $pdf->Cell(30, 10, 'No PO', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(40, 10, $purchase_order, 0, 1);
    }
    else{
        $pdf->Cell(30, 10, 'Alamat', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(40, 10, $customerAddress, 0, 1);
    }

    // Third row of details
    if($status == "in"){
        $pdf->Cell(30, 10, 'No Truk', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(80, 10, $no_truk, 0, 0);
    }
    else{
        $pdf->Cell(30, 10, 'NPWP', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(80, 10, $npwp, 0, 0);
    }
    $pdf->Cell(30, 10, 'Invoice Date', 0, 0);
    $pdf->Cell(5, 10, ':', 0, 0);
    $pdf->Cell(40, 10, $invoice_date, 0, 1);

    // Fourth row of details
    if($status == "in"){
        $pdf->Cell(30, 10, 'NO. LPB', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(80, 10, $no_LPB, 0, 0);
    }
    else{
        $pdf->Cell(30, 10, '', 0, 0);
        $pdf->Cell(5, 10, '', 0, 0);
        $pdf->Cell(80, 10, "", 0, 0);
    }
    $pdf->Cell(30, 10, 'NO_Invoice', 0, 0);
    $pdf->Cell(5, 10, ':', 0, 0);
    $pdf->Cell(40, 10, $no_invoice, 0, 1);
}

function headerMoving($pdf, $storageCodeSender, $storageCodeReceiver, $no_moving, $moving_date, $invoice_date, $no_invoice){
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(30, 10, 'PT pengirim', 0, 0);
    $pdf->Cell(5, 10, ':', 0, 0);
    $pdf->Cell(80, 10, $storageCodeSender, 0, 0);
    $pdf->Cell(30, 10, 'PT penerima', 0, 0);
    $pdf->Cell(5, 10, ':', 0, 0);
    $pdf->Cell(40, 10, $storageCodeReceiver, 0, 1);

    // Second row of details
    $pdf->Cell(30, 10, 'no_moving', 0, 0);
    $pdf->Cell(5, 10, ':', 0, 0);
    $pdf->Cell(80, 10, $no_moving, 0, 0);
    $pdf->Cell(30, 10, 'moving_date', 0, 0);
    $pdf->Cell(5, 10, ':', 0, 0);
    $pdf->Cell(40, 10, $moving_date, 0, 1);

    // Third row of details
    $pdf->Cell(30, 10, 'Invoice Date', 0, 0);
    $pdf->Cell(5, 10, ':', 0, 0);
    $pdf->Cell(80, 10, $invoice_date, 0, 0);
    $pdf->Cell(30, 10, 'no_invoice', 0, 0);
    $pdf->Cell(5, 10, ':', 0, 0);
    $pdf->Cell(40, 10, $no_invoice, 0, 1);
}

function footerInvoice($pdf, $no_faktur, $total_amount, $tax, $taxPPN, $pay_amount){
    $pdf->Ln(1);
    $pdf->Cell(30, 10, 'NO. Faktur', 0, 0);
    $pdf->Cell(70, 10, ': ' . $no_faktur, 0, 0);
    $pdf->Cell(30, 10, 'Total Nilai Barang', 0, 0);
    $pdf->Cell(30, 10, ': ' . formatToIndonesianNumber($total_amount), 0, 1);

    $pdf->Cell(100, 10, '', 0, 0);
    $pdf->Cell(30, 10, 'PPN (%): ' . $tax, 0, 0);
    $pdf->Cell(30, 10, ': ' . formatToIndonesianNumber($taxPPN), 0, 1);

    $pdf->Cell(100, 10, '', 0, 0);
    $pdf->Cell(30, 10, 'NIlai Yg Harus Dibayar', 0, 0);
    $pdf->Cell(30, 10, ': ' . formatToIndonesianNumber($pay_amount), 0, 1);
}

function displayProducts($pdf, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $total_amount){
    $pdf->Ln(1);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(10, 10, 'No', 1);
    $pdf->Cell(30, 10, 'KD', 1);
    $pdf->Cell(50, 10, 'Material', 1);
    $pdf->Cell(20, 10, 'QTY', 1);
    $pdf->Cell(20, 10, 'UOM', 1);
    $pdf->Cell(30, 10, 'Price/uom', 1);
    $pdf->Cell(30, 10, 'Nominal', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 6);
    for($i = 0; $i < count($productCodes); $i++){
        $pdf->Cell(10, 7, $i + 1, 1);
        $pdf->Cell(30, 7, $productCodes[$i], 1);
        $pdf->Cell(50, 7, $productNames[$i], 1);
        $pdf->Cell(20, 7, formatToIndonesianNumber($qtys[$i]), 1);
        $pdf->Cell(20, 7, $uoms[$i], 1);
        $pdf->Cell(30, 7, formatToIndonesianNumber($price_per_uom[$i]), 1);
        $pdf->Cell(30, 7, formatToIndonesianNumber(($qtys[$i] * $price_per_uom[$i])), 1);
        $total_amount += ($qtys[$i] * $price_per_uom[$i]);
        $pdf->Ln();
    }

    return $total_amount;
}

function footerPayment($pdf, $payment_date, $total_amount, $payment_amount, $tax, $taxPPN, $pay_amount){
    // Footer
    $pdf->Ln(1);
    // Left side (Payment details)
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(30, 7, 'tanggal payment', 0, 0);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(45, 7, $payment_date, 0, 0);

    // Right side (Total, PPN, and Nilai Yg Harus Dibayar)
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(30, 7, 'Total Nilai Barang', 0, 0);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(40, 7, formatToIndonesianNumber($total_amount), 0, 1);

    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(30, 7, 'nilai payment', 0, 0);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(45, 7, formatToIndonesianNumber($payment_amount), 0, 0);

    // Right side (Total, PPN, and Nilai Yg Harus Dibayar)
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(30, 7, 'PPN (%): ' . $tax, 0, 0);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(40, 7, formatToIndonesianNumber($taxPPN), 0, 1);

    $pdf->Cell(80, 7, '', 0, 0);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(30, 7, 'Nilai Yg Harus Dibayar', 0, 0);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(40, 7, formatToIndonesianNumber($pay_amount), 0, 1);
}

function create_invoice_in_pdf($storageCode, $storageName, $vendorName, $no_sj, $no_truk, $purchase_order, $invoice_date,  $no_LPB, $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $no_faktur, $tax){
    // Create instance of FPDF
    $total_amount = 0;
    $pdf = new FPDF('L', 'mm', 'A5');
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('Arial', 'B', 8);
    
    // Header
    displayImage($pdf, $storageCode);
    $pdf->Cell(130, 10, 'INVOICE IN', 0, 1, 'C');

    headerIn($pdf, $storageName, $vendorName, "", $no_sj, $purchase_order, "", $no_truk, "", $invoice_date, $no_LPB, $no_invoice, "in");

    // Add product table
    $total_amount = displayProducts($pdf, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $total_amount);

    $taxPPN = $total_amount * ($tax / 100);
    $pay_amount = $total_amount + $taxPPN;

    // Footer
    footerInvoice($pdf, $no_faktur, $total_amount, $tax, $taxPPN, $pay_amount);

    // Output the PDF
    header('Content-Type: application/pdf');
    $pdf->Output('I', 'invoice.pdf');
}

function create_invoice_out_pdf($storageCode, $storageName, $customerName, $no_sj, $customerAddress, $npwp, $invoice_date,  $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $no_faktur, $tax){
    // Create instance of FPDF
    $total_amount = 0;
    $pdf = new FPDF('L', 'mm', 'A5');
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('Arial', 'B', 8);

    // Header
    displayImage($pdf, $storageCode);
    $pdf->Cell(130, 10, 'INVOICE OUT', 0, 1, 'C');

    headerIn($pdf, $storageName, "", $customerName, $no_sj, "", $customerAddress, "", $npwp, $invoice_date, "", $no_invoice, "out");

    // Add product table
    $total_amount = displayProducts($pdf, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $total_amount);

    $taxPPN = $total_amount * ($tax / 100);
    $pay_amount = $total_amount + $taxPPN;

    // Footer
    footerInvoice($pdf, $no_faktur, $total_amount, $tax, $taxPPN, $pay_amount);

    // Output the PDF
    header('Content-Type: application/pdf');
    $pdf->Output('I', 'invoice.pdf');
}

function create_invoice_moving_pdf($storageCodeSender, $storageCodeReceiver, $no_moving, $moving_date, $invoice_date,  $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $no_faktur, $tax){
    // Create instance of FPDF
    $total_amount = 0;
    $pdf = new FPDF('L', 'mm', 'A5');
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('Arial', 'B', 8);

    // Header
    displayImage($pdf, $storageCodeSender);
    $pdf->Cell(130, 10, 'INVOICE OUT MOVING', 0, 1, 'C');

    // PT and Vendor details
    headerMoving($pdf, $storageCodeSender, $storageCodeReceiver, $no_moving, $moving_date, $invoice_date, $no_invoice);

    // Add product table
    $total_amount = displayProducts($pdf, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $total_amount);

    $taxPPN = $total_amount * ($tax / 100);
    $pay_amount = $total_amount + $taxPPN;

    // Footer
    footerInvoice($pdf, $no_faktur, $total_amount, $tax, $taxPPN, $pay_amount);

    // Output the PDF
    header('Content-Type: application/pdf');
    $pdf->Output('I', 'invoice.pdf');
}

function create_payment_in_pdf($storageCode, $storageName, $vendorName, $no_sj, $no_truk, $purchase_order, $invoice_date, $no_LPB, $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $payment_amount, $payment_date, $tax) {
    // Create instance of FPDF
    $total_amount = 0;
    $pdf = new FPDF('L', 'mm', 'A5');
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('Arial', 'B', 8);

    // Header
    displayImage($pdf, $storageCode);
    $pdf->Cell(130, 10, 'PAYMENT IN', 0, 1, 'C');

    headerIn($pdf, $storageName, $vendorName, "", $no_sj, $purchase_order, "", $no_truk, "", $invoice_date, $no_LPB, "", "in");

    // Add product table
    $total_amount = displayProducts($pdf, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $total_amount);

    $taxPPN = $total_amount * ($tax / 100);
    $pay_amount = $total_amount + $taxPPN;

    // Footer
    footerPayment($pdf, $payment_date, $total_amount, $payment_amount, $tax, $taxPPN, $pay_amount);

    // Output the PDF
    header('Content-Type: application/pdf');
    $pdf->Output('I', 'invoice.pdf');
}

function create_payment_moving_pdf($storageCodeSender, $storageCodeReceiver, $no_moving, $moving_date, $invoice_date,  $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $payment_amount, $payment_date, $tax) {
    // Create instance of FPDF
    $total_amount = 0;
    $pdf = new FPDF('L', 'mm', 'A5');
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('Arial', 'B', 8);

    // Header
    displayImage($pdf, $storageCodeSender);
    $pdf->Cell(130, 10, 'PAYMENT MOVING', 0, 1, 'C');

    headerMoving($pdf, $storageCodeSender, $storageCodeReceiver, $no_moving, $moving_date, $invoice_date, $no_invoice);

    // Add product table
    $total_amount = displayProducts($pdf, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $total_amount);

    $taxPPN = $total_amount * ($tax / 100);
    $pay_amount = $total_amount + $taxPPN;

    // Footer
    footerPayment($pdf, $payment_date, $total_amount, $payment_amount, $tax, $taxPPN, $pay_amount);

    // Output the PDF
    header('Content-Type: application/pdf');
    $pdf->Output('I', 'invoice.pdf');
}

function create_payment_out_pdf($storageCode, $storageName, $customerName, $no_sj, $customerAddress, $npwp, $invoice_date,  $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $payment_amount, $payment_date, $tax) {
    // Create instance of FPDF
    $total_amount = 0;
    $pdf = new FPDF('L', 'mm', 'A5');
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('Arial', 'B', 8);

    // Header
    displayImage($pdf, $storageCode);
    $pdf->Cell(130, 10, 'PAYMENT OUT', 0, 1, 'C');

    // PT and Vendor details
    headerIn($pdf, $storageName, "", $customerName, $no_sj, "", $customerAddress, "", $npwp, $invoice_date, "", $no_invoice, "out");

    // Add product table
    $total_amount = displayProducts($pdf, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $total_amount);

    $taxPPN = $total_amount * ($tax / 100);
    $pay_amount = $total_amount + $taxPPN;

    // Footer
    footerPayment($pdf, $payment_date, $total_amount, $payment_amount, $tax, $taxPPN, $pay_amount);

    // Output the PDF
    header('Content-Type: application/pdf');
    $pdf->Output('I', 'invoice.pdf');
}

function formatToIndonesianNumber($number) {
    return number_format($number, 0, ',', '.');
}

?>