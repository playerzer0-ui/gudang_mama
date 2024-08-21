<?php

function checkAccess($action, $userType) {
    $adminActions = ["show_payment", "show_invoice", "show_hutang", "show_piutang", "master_read", "master_create", "master_create_data", "master_update", "master_update_data", "master_delete", "master_delete_data", "create_invoice", "create_payment", "getLaporanHutang", "getLaporanPiutang", "invoice", "payment", "excel_hutang", "excel_piutang"];
    // Add more actions that require admin access

    if (in_array($action, $adminActions) && $userType === 0) {
        return false;
    }
    return true;
}

function formatToIndonesianNumber($number) {
    return number_format($number, 0, ',', '.');
}

?>