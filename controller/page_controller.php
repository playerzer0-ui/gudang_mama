<?php
// controller/page_controller.php
switch($action){
    case "show_slip":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $pageState = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $title = "slip " . $pageState;
        require_once "../view/slip.php";
        break;

    case "show_invoice":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $pageState = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $title = "invoice " . $pageState;
        require_once "../view/invoice.php";
        break;

    case "show_payment":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $pageState = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $title = "payment " . $pageState;
        require_once "../view/payment.php";
        break;

    case "show_repack":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $title = "repack";
        $pageState = "repack";
        require_once "../view/repack.php";
        break;

    case "show_moving":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $title = "moving";
        $pageState = "moving";
        require_once "../view/moving.php";
        break;

    case "show_hutang":
        $title = "hutang";
        require_once "../view/hutang.php";
        break;

    case "show_piutang":
        $title = "piutang";
        require_once "../view/piutang.php";
        break;

    case "show_amends":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $title = "amends";
        $state = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        switch($state){
            case "slip":
                $no_SJs = getAllOrders();
                break;
            case "invoice":
                $no_SJs = getAllInvoices();
                break;
            case "payment":
                $no_SJs = getAllPayments();
                break;
            case "repack":
                $no_SJs = getAllRepacks();
                break;
            case "moving":
                $no_SJs = getAllMovings();
                break;
        }
        require_once "../view/amends.php";
        break;
}
?>