<?php
// controller/master_data_controller.php
switch($action){
    case "master_read":
        $title = "master read";
        $data = filter_input(INPUT_GET, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        switch($data){
            case "vendor":
                $result = getAllVendors();
                $keyNames = getAllVendorsKeyNames();
                break;
            case "product":
                $result = getAllProducts();
                $keyNames = getAllProductsKeyNames();
                break;
            case "customer":
                $result = getAllCustomers();
                $keyNames = getAllCustomersKeyNames();
                break;
            case "storage":
                $result = getAllStorages();
                $keyNames = getAllStoragesKeyNames();
                break;
            case "users":
                $result = getAllUsers();
                $keyNames = getAllUsersKeyNames();
                break;
        }

        require_once "../view/read.php";
        break;

    case "master_create":
        $title = "master create";
        $data = filter_input(INPUT_GET, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        switch($data){
            case "vendor":
                $keyNames = getAllVendorsKeyNames();
                break;
            case "product":
                $keyNames = getAllProductsKeyNames();
                break;
            case "customer":
                $keyNames = getAllCustomersKeyNames();
                break;
            case "storage":
                $keyNames = getAllStoragesKeyNames();
                break;
            case "users":
                $keyNames = getAllUsersKeyNames();
                require_once "../view/register.php";
                exit;
                break;
        }

        require_once "../view/create.php";
        break;

    case "master_create_data":
        $data = filter_input(INPUT_POST, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $input_data = filter_input_array(INPUT_POST)["input_data"];

        switch($data){
            case "vendor":
                $flag = createVendor($input_data[0], $input_data[1], $input_data[2], $input_data[3]);
                break;
            case "product":
                $flag = createProduct($input_data[0], $input_data[1]);
                break;
            case "customer":
                $flag = createCustomer($input_data[0], $input_data[1], $input_data[2], $input_data[3]);
                break;
            case "storage":
                if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                    $logo_tmp_name = $_FILES['logo']['tmp_name'];
                    $storageCode = $input_data[0];
                    $logo_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $logo_new_name = $storageCode . '.' . $logo_extension;
                    $logo_destination = "../img/" . $logo_new_name;
            
                    if(move_uploaded_file($logo_tmp_name, $logo_destination)) {
                        $flag = createStorage($input_data[0], $input_data[1], $input_data[2], $input_data[3]);
                    } else {
                        $flag = false;
                    }
                } else {
                    $flag = false;
                }
                break;
            case "users":
                $flag = register($input_data[0], $input_data[1], $input_data[2]);
                break;
        }

        if(!$flag){
            header("Location:../controller/index.php?action=master_create&data=" . $data . "&msg=code existed already");
        }
        else{
            header("Location:../controller/index.php?action=master_read&data=" . $data . "&msg=created data");
        }
        break;

    case "master_update":
        $title = "master update";
        $data = filter_input(INPUT_GET, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        switch($data){
            case "vendor":
                $keyNames = getAllVendorsKeyNames();
                $result = getVendorByCode($code);
                break;
            case "product":
                $keyNames = getAllProductsKeyNames();
                $result = getProductByCode($code);
                break;
            case "customer":
                $keyNames = getAllCustomersKeyNames();
                $result = getCustomerByCode($code);
                break;
            case "storage":
                $keyNames = getAllStoragesKeyNames();
                $result = getstorageByCode($code);
                break;
            case "users":
                $result = getUserByCode($code);
                require_once "../view/register.php";
                exit;
                break;
        }

        require_once "../view/update.php";
        break;

    case "master_update_data":
        $data = filter_input(INPUT_POST, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $oldCode = filter_input(INPUT_POST, "oldCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $input_data = filter_input_array(INPUT_POST)["input_data"];

        switch($data){
            case "vendor":
                $flag = updateVendor($input_data[0], $input_data[1], $input_data[2], $input_data[3], $oldCode);
                break;
            case "product":
                $flag = updateProduct($input_data[0], $input_data[1], $oldCode);
                break;
            case "customer":
                $flag = updateCustomer($input_data[0], $input_data[1], $input_data[2], $input_data[3], $oldCode);
                break;
            case "storage":
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                    if (!empty($oldCode)) {
                        $old_logo_path = "../img/" . $oldCode . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            
                        if (file_exists($old_logo_path)) {
                            unlink($old_logo_path);
                        }
                    }
            
                    $logo_tmp_name = $_FILES['logo']['tmp_name'];
                    $storageCode = $input_data[0];
                    $logo_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $logo_new_name = $storageCode . '.' . $logo_extension;
                    $logo_destination = "../img/" . $logo_new_name;
            
                    if (move_uploaded_file($logo_tmp_name, $logo_destination)) {
                        $flag = updateStorage($input_data[0], $input_data[1], $input_data[2], $input_data[3], $oldCode);
                    } else {
                        $flag = false;
                    }
                } else {
                    $flag = false;
                }
                break;
            case "users":
                $flag = updateUser($input_data[0], $input_data[1], $input_data[2], $oldCode);
                break;
        }

        if($flag){
            header("Location:../controller/index.php?action=master_read&data=" . $data . "&msg=updated data");
        }
        else if($flag == "duplicate"){
            header("Location:../controller/index.php?action=master_update&data=" . $data . "&msg=code existed already");
        }
        else if($flag == "foreign"){
            header("Location:../controller/index.php?action=master_update&data=" . $data . "&msg=code is messing with the orders on the order products");
        }
        else{
            header("Location:../controller/index.php?action=master_update&data=" . $data . "&msg=code update error");
        }
        break;

    case "master_delete":
        $title = "master delete";
        $data = filter_input(INPUT_GET, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        require_once "../view/delete.php";
        break;

    case "master_delete_data":
        $data = filter_input(INPUT_POST, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $code = filter_input(INPUT_POST, "code", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        switch($data){
            case "vendor":
                $flag = deleteVendor($code);
                break;
            case "product":
                $flag = deleteProduct($code);
                break;
            case "customer":
                $flag = deleteCustomer($code);
                break;
            case "storage":
                $flag = deleteStorage($code);
                break;
            case "users":
                $flag = deleteUser($code);
                break;
        }

        if($flag){
            header("Location:../controller/index.php?action=master_read&data=" . $data . "&msg=deleted data");
        }
        else if($flag == "foreign"){
            header("Location:../controller/index.php?action=master_read&data=" . $data . "&msg=code is messing with the orders or linked to something else");
        }
        else{
            header("Location:../controller/index.php?action=master_read&data=" . $data . "&msg=code delete error");
        }

        require_once "../view/delete.php";
        break;
}
?>