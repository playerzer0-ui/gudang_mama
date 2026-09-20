<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../model/TwoFactorService.php';
if (($argv[1] ?? '') === '--print-sql') {
    echo implode(";\n\n", TwoFactorService::schema()) . ";\n";
    exit;
}
$options = getopt('', ['apply', 'host:', 'key-file:']);
if (!isset($options['apply'], $options['host'], $options['key-file'])) {
    echo "Usage: php scripts/setup_totp.php --print-sql\n";
    echo "Or: php scripts/setup_totp.php --apply --host=localhost --key-file=C:/xampp/private/gudang_mama_totp.key\n";
    echo "Host explicitly selects the existing model/database.php connection. Back up before applying.\n";
    exit(1);
}
$keyFile = $options['key-file'];
$parent = dirname($keyFile);
if (!is_dir($parent) && !mkdir($parent, 0700, true)) throw new RuntimeException('Cannot create private key directory.');
$parent = realpath($parent);
// In this project layout the website document root is htdocs (two directories above the project).
$documentRoot = realpath(dirname(__DIR__, 3));
$normalizedParent = strtolower(str_replace('\\', '/', $parent));
$normalizedRoot = strtolower(str_replace('\\', '/', $documentRoot));
if ($normalizedParent === $normalizedRoot || str_starts_with($normalizedParent, $normalizedRoot . '/')) {
    throw new RuntimeException('Choose a key file outside the website document root.');
}
$keyFile = $parent . DIRECTORY_SEPARATOR . basename($keyFile);
if (file_exists($keyFile)) {
    $key = trim(file_get_contents($keyFile));
    if (strlen(base64_decode($key, true) ?: '') !== 32) throw new RuntimeException('Existing key is invalid; it has not been replaced.');
} else {
    $handle = fopen($keyFile, 'x');
    if (!$handle) throw new RuntimeException('Cannot create key file.');
    fwrite($handle, base64_encode(random_bytes(32)) . "\n");
    fclose($handle);
    chmod($keyFile, 0600);
}
$_SERVER['HTTP_HOST'] = $options['host'];
require __DIR__ . '/../model/database.php';
if (!isset($db) || !$db instanceof PDO) exit(1);
foreach (TwoFactorService::schema() as $sql) $db->exec($sql);
$configDirectory = dirname(__DIR__) . '/config';
if (!is_dir($configDirectory)) mkdir($configDirectory, 0700, true);
file_put_contents($configDirectory . '/totp.local.php', '<?php return ' . var_export($keyFile, true) . ';' . "\n");
echo "Authenticator tables and local key configuration are ready. Existing warehouse tables were not altered.\n";
echo "Keep a secure backup of the key file. All accounts must enroll at next login.\n";
