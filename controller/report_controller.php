<?php
// controller/report_controller.php
require_once __DIR__ . '/../model/users_action_functions.php';
try {
switch($action){
    case "getHPP":
        $storageCode = filter_input(INPUT_GET, "storageCode");
        $month = filter_input(INPUT_GET, "month");
        $year = filter_input(INPUT_GET, "year");
        $productCode = filter_input(INPUT_GET, "productCode");
        
        $data = generateSaldo($storageCode, $month, $year);
        if(isset($data[$productCode]["barang_siap_dijual"]["price_per_qty"])){
            echo $data[$productCode]["barang_siap_dijual"]["price_per_qty"];
        }
        else{
            echo 0;
        }
        break;

    case "getLaporanHutang":
        $month = filter_input(INPUT_GET, "month");
        $year = filter_input(INPUT_GET, "year");
        $storageCode = filter_input(INPUT_GET, "storageCode");
        $reportJson = json_encode(getLaporanHutangPiutang($month, $year, $storageCode, "hutang"), JSON_THROW_ON_ERROR);
        recordReportView('debts', $action, ['month' => $month, 'year' => $year, 'storageCode' => $storageCode], 'report_generated');
        echo $reportJson;
        break;

    case "getLaporanPiutang":
        $month = filter_input(INPUT_GET, "month");
        $year = filter_input(INPUT_GET, "year");
        $reportJson = json_encode(getLaporanHutangPiutang($month, $year, "NON", "piutang"), JSON_THROW_ON_ERROR);
        recordReportView('receivables', $action, ['month' => $month, 'year' => $year], 'report_generated');
        echo $reportJson;
        break;

    case "getReportStock":
        $month = filter_input(INPUT_GET, "month");
        $year = filter_input(INPUT_GET, "year");
        $storageCode = filter_input(INPUT_GET, "storageCode");
        $reportJson = json_encode(generateSaldo($storageCode, $month, $year), JSON_THROW_ON_ERROR);
        recordReportView('storage', $action, ['month' => $month, 'year' => $year, 'storageCode' => $storageCode], 'report_generated');
        echo $reportJson;
        break;

    case "calculateHutang":
        $no_sj = filter_input(INPUT_GET, "no_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $remaining = 0;

        if($no_sj != null){
            $payment_amount = filter_input(INPUT_GET, "payment_amount", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $tax = filter_input(INPUT_GET, "tax", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $totalNominal = getTotalNominalByNoSJ($no_sj)["totalNominal"];
            $totalPayment = getTotalPayment($no_sj)["totalPayment"];
            
            $totalNominal = $totalNominal + ($totalNominal * ((double)$tax / 100));
            if($payment_amount != null){
                $remaining = $totalNominal - $totalPayment - $payment_amount;
                echo $remaining;
            }
            else{
                $remaining = $totalNominal - $totalPayment;
                echo $remaining;
            }
        }
        else{
            echo $remaining;
        }
        break;
}
} catch (Throwable $error) {
    error_log('Report request (' . $action . '): ' . $error->getMessage());
    http_response_code(500);
    echo 'Unable to load the report. Please try again.';
}
?>