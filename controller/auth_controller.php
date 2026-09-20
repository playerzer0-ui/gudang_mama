<?php
try {
    switch ($action) {
        case 'dashboard':
            $title = 'dashboard';
            require __DIR__ . '/../view/dashboard.php';
            break;

        case 'show_login':
            if (gm_has_full_session($_SESSION)) gm_redirect('dashboard');
            $title = 'login';
            header('Cache-Control: no-store, private');
            require __DIR__ . '/../view/login.php';
            break;

        case 'login':
            gm_require_post();
            $service = gm_auth_service();
            $name = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            if (!$service->attempt('password-ip:' . $ip, 30)
                || !$service->attempt('password-user:' . strtolower($name))) {
                http_response_code(429);
                header('Retry-After: 300');
                $msg = 'Too many login attempts. Please wait five minutes.';
                $title = 'login';
                require __DIR__ . '/../view/login.php';
                break;
            }
            $user = login($name, $password);
            if (!$user) {
                $msg = 'Invalid username or password.';
                $title = 'login';
                require __DIR__ . '/../view/login.php';
                break;
            }
            $_SESSION = ['gm_pending' => [
                'id' => $user['userID'],
                'password_stamp' => hash('sha256', $user['password']),
                'expires' => time() + 600,
            ]];
            session_regenerate_id(true);
            gm_csrf();
            gm_redirect($service->state($user['userID']) ? 'totp_challenge' : 'totp_setup');

        case 'totp_setup':
        case 'totp_confirm':
            if ($action === 'totp_confirm') gm_require_post();
            $user = gm_pending_user();
            if (!$user) gm_redirect('show_login');
            $service = gm_auth_service();
            $state = $service->state($user['userID']);
            $recoveryVersion = $_SESSION['gm_pending']['recovery_version'] ?? null;
            if ($state && $recoveryVersion === null) gm_redirect('totp_challenge');
            if ($state && $recoveryVersion !== (int)$state['version']) {
                $_SESSION = [];
                gm_redirect('show_login');
            }
            if (!isset($_SESSION['gm_pending']['secret'])) {
                $_SESSION['gm_pending']['secret'] = $service->newSecret();
            }
            $secret = $_SESSION['gm_pending']['secret'];
            $error = '';
            if ($action === 'totp_confirm') {
                if (!$service->attempt('totp:' . $user['userID'])) {
                    http_response_code(429);
                    header('Retry-After: 300');
                    $error = 'Too many attempts. Please wait five minutes.';
                } else {
                    $code = is_string($_POST['code'] ?? null) ? trim($_POST['code']) : '';
                    $codes = $service->enroll($user['userID'], $secret, $code, $recoveryVersion);
                    if ($codes !== null) {
                        $version = (int)$service->state($user['userID'])['version'];
                        gm_finish_login($user, $version);
                        $_SESSION['gm_recovery_codes'] = $codes;
                        gm_redirect('totp_recovery_codes');
                    }
                    $error = 'The code is invalid or setup has changed. Try a fresh code or sign in again.';
                }
            }
            gm_auth_view('setup', [
                'secret' => $secret, 'error' => $error,
                'qr' => $service->qrDataUri($user['username'], $secret),
            ]);
            break;

        case 'totp_challenge':
        case 'totp_verify':
            if ($action === 'totp_verify') gm_require_post();
            $user = gm_pending_user();
            if (!$user) gm_redirect('show_login');
            $service = gm_auth_service();
            if (!$service->state($user['userID']) || isset($_SESSION['gm_pending']['recovery_version'])) gm_redirect('totp_setup');
            $error = '';
            if ($action === 'totp_verify') {
                if (!$service->attempt('totp:' . $user['userID'])) {
                    http_response_code(429);
                    header('Retry-After: 300');
                    $error = 'Too many attempts. Please wait five minutes.';
                } else {
                    $code = is_string($_POST['code'] ?? null) ? trim($_POST['code']) : '';
                    $version = $service->verify($user['userID'], $code);
                    if ($version !== null) {
                        gm_finish_login($user, $version);
                        gm_redirect('dashboard');
                    }
                    $version = $service->recover($user['userID'], $code);
                    if ($version !== null) {
                        $_SESSION['gm_pending']['recovery_version'] = $version;
                        $_SESSION['gm_pending']['expires'] = time() + 600;
                        session_regenerate_id(true);
                        gm_redirect('totp_setup');
                    }
                    $error = 'Invalid or already used code. Try the next code in your authenticator.';
                }
            }
            gm_auth_view('challenge', ['error' => $error]);
            break;

        case 'totp_recovery_codes':
            if (!isset($_SESSION['gm_recovery_codes'])) gm_redirect('dashboard');
            gm_auth_view('recovery', ['codes' => $_SESSION['gm_recovery_codes']]);
            break;

        case 'totp_acknowledge':
            gm_require_post();
            if (($_POST['saved'] ?? '') !== 'yes') gm_redirect('totp_recovery_codes');
            unset($_SESSION['gm_recovery_codes']);
            gm_redirect('dashboard');

        case 'logout':
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                gm_require_post();
                logout();
                gm_redirect('show_login');
            }
            gm_auth_view('logout');
            break;
    }
} catch (Throwable $error) {
    gm_auth_error();
}
