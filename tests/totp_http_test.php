<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../model/TwoFactorService.php';
$root = dirname(__DIR__);
$temp = sys_get_temp_dir() . '/gudang-totp-test-' . bin2hex(random_bytes(6));
mkdir($temp, 0700, true);
function copyTree(string $source, string $destination): void {
    mkdir($destination, 0700, true);
    foreach (new DirectoryIterator($source) as $file) {
        if ($file->isDot()) continue;
        if ($file->isDir()) copyTree($file->getPathname(), $destination . '/' . $file->getFilename());
        else copy($file->getPathname(), $destination . '/' . $file->getFilename());
    }
}
foreach (['controller', 'model', 'view', 'fpdf'] as $dir) copyTree($root . '/' . $dir, $temp . '/' . $dir);
mkdir($temp . '/vendor');
file_put_contents($temp . '/vendor/autoload.php', '<?php require ' . var_export($root . '/vendor/autoload.php', true) . ';');
$dbPath = $temp . '/test.sqlite';
file_put_contents($temp . '/model/database.php', '<?php $db = new PDO(' . var_export('sqlite:' . $dbPath, true) . ', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);');
$db = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
foreach (TwoFactorService::schema() as $sql) $db->exec($sql);
$db->exec('CREATE TABLE users (userID TEXT PRIMARY KEY, username TEXT, password TEXT, userType INTEGER)');
$password = 'a <real>& password';
$db->prepare('INSERT INTO users VALUES (?, ?, ?, ?)')->execute(['http-user', 'worker', password_hash($password, PASSWORD_BCRYPT), 0]);
$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
$address = stream_socket_get_name($socket, false);
fclose($socket);
$key = base64_encode(random_bytes(32));
$env = getenv();
$env['GUDANG_MAMA_TOTP_KEY'] = $key;
$process = proc_open([PHP_BINARY, '-S', $address, '-t', $temp], [
    0 => ['pipe', 'r'], 1 => ['file', $temp . '/server.log', 'a'], 2 => ['file', $temp . '/server.log', 'a'],
], $pipes, $temp, $env);
if (!is_resource($process)) throw new RuntimeException('Test server did not start');
$count = 0;
$cookie = '';
function expect(bool $condition, string $message): void {
    global $count;
    if (!$condition) throw new RuntimeException($message);
    $count++;
}
function request(string $action, ?array $post = null): array {
    global $address, $cookie;
    $headers = 'Cookie: ' . $cookie . "\r\n";
    if ($post !== null) $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $context = stream_context_create(['http' => [
        'method' => $post === null ? 'GET' : 'POST', 'header' => $headers,
        'content' => $post === null ? '' : http_build_query($post),
        'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 5,
    ]]);
    $body = file_get_contents('http://' . $address . '/controller/index.php?action=' . $action, false, $context);
    $status = $http_response_header[0] ?? '';
    $location = '';
    foreach ($http_response_header as $header) {
        if (preg_match('/^Set-Cookie: (PHPSESSID=[^;]*)/i', $header, $match)) $cookie = $match[1];
        if (str_starts_with($header, 'Location: ')) $location = substr($header, 10);
    }
    return [$status, $location, $body, $http_response_header];
}
function csrf(string $body): string {
    preg_match('/name="csrf" value="([^"]+)"/', $body, $match);
    if (!isset($match[1])) throw new RuntimeException('Missing CSRF form');
    return $match[1];
}
try {
    for ($i = 0; $i < 50; $i++) {
        $ready = @stream_socket_client('tcp://' . $address, $errno, $error, 0.1);
        if ($ready) { fclose($ready); break; }
        usleep(100000);
    }
    [, $location] = request('create_slip', ['kd' => ['anything']]);
    expect(str_ends_with($location, 'show_login'), 'Anonymous warehouse writes denied');
    [$status, , $body] = request('show_login');
    expect(str_contains($status, '200'), 'Login page loads');
    $token = csrf($body);
    [$status] = request('login', ['username' => 'worker', 'password' => $password]);
    expect(str_contains($status, '419'), 'Password login needs CSRF');
    [$status] = request('login');
    expect(str_contains($status, '405'), 'Password login requires POST');
    [, $location] = request('login', ['username' => 'worker', 'password' => $password, 'csrf' => $token]);
    expect(str_ends_with($location, 'totp_setup'), 'Password containing special characters leads to required enrollment');
    [, $location] = request('create_slip', ['kd' => ['anything']]);
    expect(str_ends_with($location, 'totp_challenge'), 'Password-only session cannot write');
    [, , $body, $headers] = request('totp_setup');
    expect(in_array('Cache-Control: no-store, private', $headers, true), 'Enrollment is not cached');
    preg_match('/<code>([A-Z2-7]+)<\/code>/', $body, $match);
    expect(isset($match[1]), 'Enrollment has a manual setup key');
    $secret = $match[1];
    $token = csrf($body);
    [$status] = request('totp_confirm', ['code' => '123456']);
    expect(str_contains($status, '419'), 'Enrollment confirmation needs CSRF');
    [, , $body] = request('totp_confirm', ['code' => 'bad', 'csrf' => $token]);
    expect(str_contains($body, 'invalid'), 'Invalid enrollment stays pending');
    $generator = new PragmaRX\Google2FA\Google2FA();
    $code = $generator->getCurrentOtp($secret);
    [, $location] = request('totp_confirm', ['code' => $code, 'csrf' => $token]);
    expect(str_ends_with($location, 'totp_recovery_codes'), 'Enrollment requires saving recovery codes');
    [, $location] = request('create_slip');
    expect(str_ends_with($location, 'totp_recovery_codes'), 'Recovery acknowledgement cannot be skipped');
    [, , $body] = request('totp_recovery_codes');
    preg_match_all('/<li>([a-f0-9-]{23})<\/li>/', $body, $matches);
    expect(count($matches[1]) === 8, 'Recovery codes displayed');
    $recovery = $matches[1][0];
    $token = csrf($body);
    [, $location] = request('totp_acknowledge', ['csrf' => $token, 'saved' => 'yes']);
    expect(str_ends_with($location, 'dashboard'), 'Completed enrollment enters application');
    [, $location] = request('totp_recovery_codes');
    expect(str_ends_with($location, 'dashboard'), 'Recovery codes not displayed again');
    request('logout', ['csrf' => $token]);
    [, , $body] = request('show_login');
    [, $location] = request('login', ['username' => 'worker', 'password' => $password, 'csrf' => csrf($body)]);
    expect(str_ends_with($location, 'totp_challenge'), 'Enrolled account requires authenticator');
    [, $location] = request('totp_setup');
    expect(str_ends_with($location, 'totp_challenge'), 'Password cannot reset an existing authenticator');
    [, , $body] = request('totp_challenge');
    $token = csrf($body);
    [, , $body] = request('totp_verify', ['code' => $code, 'csrf' => $token]);
    expect(str_contains($body, 'Invalid or already used'), 'Enrollment code cannot be reused to sign in');
    // Advance stored replay boundary only in this isolated database to avoid a timed test.
    $db->exec('UPDATE user_two_factor SET last_step = 0');
    [, $location] = request('totp_verify', ['code' => $generator->getCurrentOtp($secret), 'csrf' => $token]);
    expect(str_ends_with($location, 'dashboard'), 'Valid second factor completes login');
    $previousSession = $cookie;
    $cookie = ''; // Start another browser without terminating the existing full session.
    [, , $body] = request('show_login');
    request('login', ['username' => 'worker', 'password' => $password, 'csrf' => csrf($body)]);
    [, , $body] = request('totp_challenge');
    [, $location] = request('totp_verify', ['code' => $recovery, 'csrf' => csrf($body)]);
    expect(str_ends_with($location, 'totp_setup'), 'Recovery requires enrollment on replacement phone');
    [, $location] = request('create_slip');
    expect(str_ends_with($location, 'totp_challenge'), 'Recovery session cannot bypass re-enrollment');
    $db->exec('DELETE FROM auth_attempts'); // Isolated fixture: begin a fresh attempt window.
    [, , $body] = request('totp_setup');
    preg_match('/<code>([A-Z2-7]+)<\/code>/', $body, $match);
    expect(isset($match[1]) && $match[1] !== $secret, 'Replacement phone receives a new secret');
    [, $location] = request('totp_confirm', ['code' => $generator->getCurrentOtp($match[1]), 'csrf' => csrf($body)]);
    expect(str_ends_with($location, 'totp_recovery_codes'), 'Replacement enrollment succeeds');
    $replacementSession = $cookie;
    $cookie = $previousSession;
    [, $location] = request('totp_recovery_codes');
    expect(str_ends_with($location, 'show_login'), 'Replacement enrollment revokes older authenticated sessions');
    $cookie = $replacementSession;
    [, , $body] = request('totp_recovery_codes');
    $token = csrf($body);
    request('totp_acknowledge', ['csrf' => $token, 'saved' => 'yes']);
    request('logout', ['csrf' => $token]);
    [, , $body] = request('show_login');
    request('login', ['username' => 'worker', 'password' => $password, 'csrf' => csrf($body)]);
    [, , $body] = request('totp_challenge');
    $token = csrf($body);
    $db->exec('DELETE FROM auth_attempts');
    for ($i = 0; $i < 6; $i++) {
        [$status] = request('totp_verify', ['code' => 'bad', 'csrf' => $token]);
    }
    expect(str_contains($status, '429'), 'Sixth invalid authenticator submission is throttled');
    echo "$count HTTP assertions passed against an isolated copy and SQLite database.\n";
} finally {
    proc_terminate($process);
    fclose($pipes[0]);
    proc_close($process);
    $db = null;
    // Delete only files beneath the exact randomly generated test directory.
    $resolved = realpath($temp);
    $parent = realpath(sys_get_temp_dir());
    if ($resolved && dirname($resolved) === $parent && str_starts_with(basename($resolved), 'gudang-totp-test-')) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resolved, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $file) {
            if ($file->isDir()) rmdir($file->getPathname()); else unlink($file->getPathname());
        }
        rmdir($resolved);
    }
}
