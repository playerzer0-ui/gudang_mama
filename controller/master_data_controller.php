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
        require_once "../model/users_action_functions.php";
        $data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $input_data = $_POST['input_data'] ?? null;
        $createdLogo = null;
        try {
            $definitions = [
                'vendor' => ['master_vendor', 'vendorCode', 4],
                'product' => ['master_product', 'productCode', 2],
                'customer' => ['master_customer', 'customerCode', 4],
                'storage' => ['master_storage', 'storageCode', 4],
                'users' => ['master_user', 'userID', 3],
            ];
            if (!isset($definitions[$data]) || !is_array($input_data) || !array_is_list($input_data)) {
                throw new InvalidArgumentException('Invalid master data submission.');
            }
            [$type, $referenceType, $fieldCount] = $definitions[$data];
            if (count($input_data) !== $fieldCount) throw new InvalidArgumentException('Incomplete master data.');
            foreach ($input_data as $value) {
                if (!is_scalar($value)) throw new InvalidArgumentException('Invalid master data value.');
            }
            $db->beginTransaction();
            switch ($data) {
                case 'vendor':
                    $flag = createVendor(...$input_data);
                    break;
                case 'product':
                    $flag = createProduct(...$input_data);
                    break;
                case 'customer':
                    $flag = createCustomer(...$input_data);
                    break;
                case 'storage':
                    $flag = createStorage(...$input_data);
                    break;
                case 'users':
                    $flag = register(...$input_data);
                    break;
            }
            if ($flag !== true) throw new RuntimeException('Master record could not be created.');
            $reference = (string)$input_data[0];
            if ($data === 'users') {
                // Only safe account fields enter the audit snapshot; never password hashes.
                $users = usersActionFetchRows('SELECT userID FROM users WHERE username = ?', [$reference], true);
                if (count($users) !== 1) throw new RuntimeException('Created user could not be identified.');
                $reference = $users[0]['userID'];
            }
            recordCreatedUserAction($type, [$referenceType => $reference], $referenceType);
            if ($data === 'storage') {
                $upload = $_FILES['logo'] ?? null;
                if (!$upload || $upload['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Storage logo is required.');
                $extension = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
                if (!preg_match('/^[A-Za-z0-9_-]+$/D', $reference)
                    || !in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
                    throw new RuntimeException('Invalid storage logo filename.');
                }
                $destination = __DIR__ . '/../img/' . $reference . '.' . $extension;
                // Never overwrite an existing logo during creation.
                $handle = @fopen($destination, 'x');
                if ($handle === false) throw new RuntimeException('Storage logo already exists or cannot be saved.');
                fclose($handle);
                $createdLogo = $destination;
                if (!move_uploaded_file($upload['tmp_name'], $destination)) throw new RuntimeException('Storage logo could not be saved.');
            }
            $db->commit();
            $createdLogo = null;
            header('Location: ../controller/index.php?' . http_build_query([
                'action' => 'master_read', 'data' => $data, 'msg' => 'Created data'
            ]));
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            if ($createdLogo !== null && is_file($createdLogo)) unlink($createdLogo);
            // Do not include submitted values or database exception text (password hashes).
            error_log('Master CREATE failed: ' . (is_string($data) ? $data : 'unknown'));
            header('Location: ../controller/index.php?' . http_build_query([
                'action' => 'master_create', 'data' => $data,
                'msg' => 'Unable to create record. Check for an existing code or username and valid details. No changes were saved.'
            ]));
        }
        exit;

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
                if ($result && strcasecmp(trim($result['username']), 'admin1') === 0) {
                    header('Location: ../controller/index.php?action=master_read&data=users&msg=' . urlencode('This account cannot be edited through Master Data.'));
                    exit;
                }
                require_once "../view/register.php";
                exit;
                break;
        }

        require_once "../view/update.php";
        break;

    case "master_update_data":
        require_once "../model/users_action_functions.php";
        $data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $oldCode = filter_input(INPUT_POST, 'oldCode', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $input_data = $_POST['input_data'] ?? null;
        $logoDestination = null;
        $previousLogo = null;
        $logoWritten = false;
        try {
            $definitions = [
                'vendor' => ['master_vendor', 'vendorCode', 4, 'updateVendor'],
                'product' => ['master_product', 'productCode', 2, 'updateProduct'],
                'customer' => ['master_customer', 'customerCode', 4, 'updateCustomer'],
                'storage' => ['master_storage', 'storageCode', 4, 'updateStorage'],
                'users' => ['master_user', 'userID', 3, 'updateUser'],
            ];
            if (!isset($definitions[$data]) || !is_array($input_data) || !array_is_list($input_data)) {
                throw new InvalidArgumentException('Invalid master update.');
            }
            [$type, $referenceType, $fieldCount, $updateFunction] = $definitions[$data];
            if (count($input_data) !== $fieldCount) throw new InvalidArgumentException('Incomplete master update.');
            foreach ($input_data as $value) {
                if (!is_scalar($value)) throw new InvalidArgumentException('Invalid master value.');
            }
            $db->beginTransaction();
            $oldReference = $oldCode;
            if ($data === 'users') {
                $users = usersActionFetchRows('SELECT userID, username FROM users WHERE username = ?', [$oldCode], true);
                if (count($users) !== 1 || strcasecmp(trim($users[0]['username']), 'admin1') === 0) {
                    throw new RuntimeException('Account is missing or protected.');
                }
                $oldReference = $users[0]['userID'];
            }
            $before = getUserActionSnapshot($type, [$referenceType => $oldReference], true);
            if ($before === null) throw new RuntimeException('Original master record not found.');
            $arguments = array_merge($input_data, [$oldCode]);
            $flag = $updateFunction(...$arguments);
            if ($flag !== true) throw new RuntimeException('Master record update failed.');
            $newReference = $data === 'users' ? $oldReference : (string)$input_data[0];
            $after = getUserActionSnapshot($type, [$referenceType => $newReference], true);
            if ($after === null) throw new RuntimeException('Updated master record not found.');
            if ($data === 'users') {
                // The existing update form always sets a password. Never snapshot its value/hash.
                $after['header']['password_changed'] = 'Yes';
            }
            if ($data === 'storage') {
                $upload = $_FILES['logo'] ?? null;
                if (!$upload || $upload['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Storage logo is required.');
                $extension = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
                if (!preg_match('/^[A-Za-z0-9_-]+$/D', $newReference)
                    || !in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
                    throw new RuntimeException('Invalid storage logo filename.');
                }
                $logoDestination = '../img/' . $newReference . '.' . $extension;
                if (is_file($logoDestination)) {
                    $previousLogo = file_get_contents($logoDestination);
                    if ($previousLogo === false) throw new RuntimeException('Cannot preserve existing logo.');
                }
                $after['header']['logo_uploaded'] = basename($logoDestination);
            }
            createUserAction('UPDATE', $type, $referenceType, $newReference, $before, $after,
                ['before' => $before['references'], 'after' => $after['references']]);
            if ($logoDestination !== null) {
                if (!move_uploaded_file($upload['tmp_name'], $logoDestination)) throw new RuntimeException('Logo could not be saved.');
                $logoWritten = true;
            }
            $db->commit();
            $logoWritten = false;
            header('Location: ../controller/index.php?' . http_build_query([
                'action' => 'master_read', 'data' => $data, 'msg' => 'Updated data'
            ]));
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            if ($logoWritten) {
                if ($previousLogo !== null) {
                    if (file_put_contents($logoDestination, $previousLogo) === false) error_log('Unable to restore storage logo after rollback.');
                } elseif (is_file($logoDestination)) {
                    unlink($logoDestination);
                }
            }
            // Do not expose SQL errors that could contain credential values.
            error_log('Master UPDATE failed: ' . (is_string($data) ? $data : 'unknown'));
            header('Location: ../controller/index.php?' . http_build_query([
                'action' => 'master_read', 'data' => $data,
                'msg' => 'Unable to update record. Check for duplicate codes, linked records, protected accounts or invalid details. No database changes were saved.'
            ]));
        }
        exit;

    case "master_delete":
        $title = "master delete";
        $data = filter_input(INPUT_GET, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($data === 'users') {
            $targetUser = getUserByCode($code);
            if ($targetUser && strcasecmp(trim($targetUser['username']), 'admin1') === 0) {
                header('Location: ../controller/index.php?action=master_read&data=users&msg=' . urlencode('admin1 is protected and cannot be deleted.'));
                exit;
            }
        }
        require_once "../view/delete.php";
        break;

    case "master_delete_data":
        require_once "../model/users_action_functions.php";
        $data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $failureMessage = 'Unable to delete record. No changes were saved.';
        try {
            $definitions = [
                'vendor' => ['master_vendor', 'vendorCode', 'deleteVendor'],
                'product' => ['master_product', 'productCode', 'deleteProduct'],
                'customer' => ['master_customer', 'customerCode', 'deleteCustomer'],
                'storage' => ['master_storage', 'storageCode', 'deleteStorage'],
                'users' => ['master_user', 'userID', 'deleteUser'],
            ];
            if (!isset($definitions[$data]) || !is_string($code) || trim($code) === '' || $code === '-') {
                throw new InvalidArgumentException('Invalid master deletion.');
            }
            [$type, $referenceType, $deleteFunction] = $definitions[$data];
            $db->beginTransaction();
            $before = getUserActionSnapshot($type, [$referenceType => $code], true);
            if ($before === null) {
                $failureMessage = 'Record no longer exists. Nothing was deleted.';
                throw new RuntimeException('Master record not found.');
            }
            if ($data === 'users' && strcasecmp(trim($before['header']['username']), 'admin1') === 0) {
                $failureMessage = 'admin1 is protected and cannot be deleted.';
                throw new RuntimeException('Protected account.');
            }
            $flag = $deleteFunction($code);
            if ($flag !== true) {
                if ($flag === 'foreign_key' || $flag === 'foreign') {
                    $failureMessage = 'This record is linked to other records and cannot be deleted. No changes were saved.';
                }
                throw new RuntimeException('Master deletion failed.');
            }
            createUserAction('DELETE', $type, $referenceType,
                $before['references'][$referenceType], $before, null, $before['references']);
            $db->commit();
            header('Location: ../controller/index.php?' . http_build_query([
                'action' => 'master_read', 'data' => $data, 'msg' => 'Deleted data'
            ]));
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Master DELETE failed: ' . (is_string($data) ? $data : 'unknown'));
            header('Location: ../controller/index.php?' . http_build_query([
                'action' => 'master_read', 'data' => $data, 'msg' => $failureMessage
            ]));
        }
        exit;

}
?>