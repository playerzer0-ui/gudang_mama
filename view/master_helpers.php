<?php
$masterEscape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$masterTitles = ['vendor' => 'Vendors', 'customer' => 'Customers', 'product' => 'Products', 'storage' => 'Storages', 'users' => 'Users'];
$masterTitle = $masterTitles[$data] ?? ucfirst($data);
$masterFieldLabel = static fn($key) => ucwords(trim(preg_replace('/([a-z])([A-Z])/', '$1 $2', str_replace('_', ' ', $key))));
$masterUrl = static fn($actionName, $recordCode = null) => '../controller/index.php?' . http_build_query(array_filter(['action' => $actionName, 'data' => $data, 'code' => $recordCode], static fn($value) => $value !== null));
?>
