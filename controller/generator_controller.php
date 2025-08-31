<?php
// controller/generator_controller.php
switch($action){
    case "generate_LPB":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateNoLPB($storageCode, $month, $year, "1");
        break;

    case "generate_SJ":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateNoLPB($storageCode, $month, $year, "2");
        break;

    case "generate_SJT":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateTaxSJ($storageCode, $month, $year);
        break;

    case "generate_SJR":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generate_SJR($storageCode, $month, $year);
        break;

    case "generate_SJP":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generate_SJP($storageCode, $month, $year);
        break;

    case "generateNoInvoice":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateNoInvoice($storageCode, $month, $year);
        break;
}
?>