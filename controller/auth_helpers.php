<?php
require_once __DIR__ . '/../model/TwoFactorService.php';

function gm_auth_service(): TwoFactorService
{
    global $db;
    $key = getenv('GUDANG_MAMA_TOTP_KEY') ?: '';
    $file = getenv('GUDANG_MAMA_TOTP_KEY_FILE');
    $localConfig = __DIR__ . '/../config/totp.local.php';
    if (!$file && is_file($localConfig)) $file = require $localConfig;
    if ($key === '' && $file && is_readable($file)) {
        $key = trim(file_get_contents($file));
    }
    return new TwoFactorService($db, $key);
}

function gm_redirect(string $action): never
{
    header('Location: ../controller/index.php?action=' . $action);
    exit;
}

function gm_csrf(): string
{
    if (!isset($_SESSION['gm_csrf'])) $_SESSION['gm_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['gm_csrf'];
}

function gm_valid_csrf(array $post): bool
{
    return isset($_SESSION['gm_csrf']) && is_string($post['csrf'] ?? null)
        && hash_equals($_SESSION['gm_csrf'], $post['csrf']);
}

function gm_require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Use the form to submit this request.');
    }
    if (!gm_valid_csrf($_POST)) {
        http_response_code(419);
        exit('This form has expired. Go back, refresh the page, and try again.');
    }
}

function gm_user(string $id): ?array
{
    global $db;
    $statement = $db->prepare('SELECT userID, username, password, userType FROM users WHERE userID = ?');
    $statement->execute([$id]);
    return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
}

function gm_pending_user(): ?array
{
    $pending = $_SESSION['gm_pending'] ?? null;
    if (!$pending || time() >= $pending['expires']) {
        unset($_SESSION['gm_pending']);
        return null;
    }
    $user = gm_user($pending['id']);
    if (!$user || !hash_equals($pending['password_stamp'], hash('sha256', $user['password']))) {
        unset($_SESSION['gm_pending']);
        return null;
    }
    return $user;
}

function gm_finish_login(array $user, int $version): void
{
    $_SESSION = [
        'userID' => $user['userID'],
        'username' => $user['username'],
        'userType' => (int)$user['userType'],
        'gm_version' => $version,
        'gm_password_stamp' => hash('sha256', $user['password']),
    ];
    session_regenerate_id(true);
    gm_csrf();
}

function gm_has_full_session(array $session): bool
{
    return isset($session['userID'], $session['gm_version'], $session['gm_password_stamp']);
}

function gm_guard(string $action): void
{
    $public = ['show_login', 'login', 'totp_setup', 'totp_confirm', 'totp_challenge', 'totp_verify', 'logout'];
    if (in_array($action, $public, true)) return;
    if (!gm_has_full_session($_SESSION)) {
        gm_redirect(isset($_SESSION['gm_pending']) ? 'totp_challenge' : 'show_login');
    }
    $user = gm_user($_SESSION['userID']);
    $state = gm_auth_service()->state($_SESSION['userID']);
    if (!$user || !$state || (int)$state['version'] !== $_SESSION['gm_version']
        || !hash_equals($_SESSION['gm_password_stamp'], hash('sha256', $user['password']))) {
        $_SESSION = [];
        session_regenerate_id(true);
        gm_redirect('show_login');
    }
    // Keep role changes effective without waiting for another login.
    $GLOBALS['userType'] = $_SESSION['userType'] = (int)$user['userType'];
    $GLOBALS['username'] = $_SESSION['username'] = $user['username'];
    if (isset($_SESSION['gm_recovery_codes']) && !in_array($action, ['totp_recovery_codes', 'totp_acknowledge'], true)) {
        gm_redirect('totp_recovery_codes');
    }
}

function gm_auth_error(): never
{
    // Do not reveal connection strings, user secrets, or submitted codes.
    http_response_code(503);
    exit('Login security is unavailable. Please contact the administrator.');
}

function gm_auth_view(string $screen, array $data = []): void
{
    header('Cache-Control: no-store, private');
    header('Referrer-Policy: no-referrer');
    header('X-Content-Type-Options: nosniff');
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../view/two_factor.php';
}
