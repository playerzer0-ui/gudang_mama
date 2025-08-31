<?php
// controller/report_controller.php
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
        echo json_encode(getLaporanHutangPiutang($month, $year, $storageCode, "hutang"));
        break;

    case "getLaporanPiutang":
        $month = filter_input(INPUT_GET, "month");
        $year = filter_input(INPUT_GET, "year");
        echo json_encode(getLaporanHutangPiutang($month, $year, "NON", "piutang"));
        break;

    case "getReportStock":
        $month = filter_input(INPUT_GET, "month");
        $year = filter_input(INPUT_GET, "year");
        $storageCode = filter_input(INPUT_GET, "storageCode");
        echo json_encode(generateSaldo($storageCode, $month, $year));
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
?>