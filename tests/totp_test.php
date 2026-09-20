<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../controller/auth_helpers.php';

$count = 0;
function check(bool $condition, string $message): void {
    global $count;
    if (!$condition) throw new RuntimeException($message);
    $count++;
}
$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
foreach (TwoFactorService::schema() as $sql) $db->exec($sql);
$db->exec('CREATE TABLE users (userID TEXT PRIMARY KEY, username TEXT, password TEXT, userType INTEGER)');
$passwordHash = password_hash('test-password', PASSWORD_BCRYPT);
$db->prepare('INSERT INTO users VALUES (?, ?, ?, ?)')->execute(['test-user', 'worker', $passwordHash, 0]);
$key = base64_encode(random_bytes(32));
$service = new TwoFactorService($db, $key);
$generator = new PragmaRX\Google2FA\Google2FA();
$secret = $service->newSecret();
$code = $generator->getCurrentOtp($secret);
check($service->enroll('test-user', $secret, 'bad') === null, 'Malformed enrollment rejected');
check($service->state('test-user') === null, 'Failed enrollment must not activate TOTP');
$codes = $service->enroll('test-user', $secret, $code);
check(is_array($codes) && count($codes) === 8 && count(array_unique($codes)) === 8, 'Eight unique recovery codes');
$state = $service->state('test-user');
check($state['secret'] !== $secret && !str_contains($state['secret'], $secret), 'Secret is encrypted');
check(!str_contains($state['recovery_hashes'], $codes[0]), 'Recovery codes are hashed');
check($service->verify('test-user', $code) === null, 'Enrollment code cannot be replayed');
$db->exec('UPDATE user_two_factor SET last_step = 0');
check($service->verify('test-user', $code) === 1, 'Valid authenticator grants version');
check($service->verify('test-user', $code) === null, 'Accepted authenticator cannot be replayed');
check($service->verify('test-user', '12345') === null, 'Short authenticator rejected');
check($service->verify('missing', $code) === null, 'Missing account rejected');
check($service->enroll('test-user', $secret, $code) === null, 'Cannot overwrite active authenticator');
check($service->recover('test-user', $codes[0]) === 1, 'Valid recovery code accepted');
check($service->recover('test-user', $codes[0]) === null, 'Recovery code is one use');
$newSecret = $service->newSecret();
$newCode = $generator->getCurrentOtp($newSecret);
check($service->enroll('test-user', $newSecret, $newCode, 99) === null, 'Stale recovery version rejected');
$newCodes = $service->enroll('test-user', $newSecret, $newCode, 1);
check(is_array($newCodes) && (int)$service->state('test-user')['version'] === 2, 'Recovery replaces authenticator and invalidates old sessions');
check($service->recover('test-user', $codes[1]) === null, 'Old recovery set invalidated');
check($service->recover('test-user', strtoupper($newCodes[0])) === 2, 'Recovery code normalization');
$db->exec('UPDATE user_two_factor SET last_step = 0');
try {
    (new TwoFactorService($db, base64_encode(random_bytes(32))))->verify('test-user', $newCode);
    throw new RuntimeException('Wrong encryption key unexpectedly accepted');
} catch (RuntimeException $e) {
    check($e->getMessage() === 'Cannot decrypt authenticator secret.', 'Wrong key fails closed');
}
check(!$db->inTransaction(), 'Failed decryption rolls back transaction');
for ($i = 0; $i < 5; $i++) check($service->attempt('shared-user'), 'Allowed attempt');
check(!(new TwoFactorService($db, $key))->attempt('shared-user'), 'Limit survives new service/session');
$db->exec('UPDATE auth_attempts SET reset_at = 0');
check($service->attempt('shared-user'), 'Expired attempt window resets');
check(str_starts_with($service->qrDataUri('worker', $secret), 'data:image/svg+xml;base64,'), 'QR generated locally');
$_SESSION = [];
check(!gm_has_full_session($_SESSION), 'Anonymous session rejected');
$_SESSION = ['userID' => 'test-user', 'userType' => 0];
check(!gm_has_full_session($_SESSION), 'Pre-upgrade session rejected');
$_SESSION = ['gm_pending' => ['id' => 'test-user', 'password_stamp' => hash('sha256', $passwordHash), 'expires' => time() + 60]];
check(!gm_has_full_session($_SESSION), 'Pending session is not fully authenticated');
check(gm_pending_user()['userID'] === 'test-user', 'Pending password proof accepted');
$_SESSION['gm_pending']['expires'] = time() - 1;
check(gm_pending_user() === null, 'Expired pending login rejected');
$_SESSION['gm_pending'] = ['id' => 'test-user', 'password_stamp' => 'old', 'expires' => time() + 60];
check(gm_pending_user() === null, 'Password change invalidates pending login');
$csrf = gm_csrf();
check(gm_valid_csrf(['csrf' => $csrf]), 'Valid CSRF token accepted');
check(!gm_valid_csrf(['csrf' => 'invalid']) && !gm_valid_csrf(['csrf' => []]), 'Invalid CSRF rejected');
echo "$count security assertions passed (isolated SQLite database).\n";
