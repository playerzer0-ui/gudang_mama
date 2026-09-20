<?php $escape = fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account security — Gudang Mama</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f3f5f7;color:#202b36;font:16px/1.5 system-ui,sans-serif}
        main{max-width:520px;margin:48px auto;padding:28px;background:white;border:1px solid #dce2e7;border-radius:12px}
        h1{font-size:25px;margin:8px 0 16px}label{display:block;margin:14px 0 6px}
        input[type=text]{width:100%;font:inherit;padding:12px;border:1px solid #8997a4;border-radius:6px}
        button{font:inherit;background:#1266cd;color:white;border:0;border-radius:6px;padding:12px 18px;margin-top:18px;cursor:pointer}
        .error{background:#fff0ef;color:#942e25;padding:12px;border-radius:6px}.brand{color:#586675}
        .qr{display:block;width:260px;max-width:100%;margin:auto}code{overflow-wrap:anywhere}
        ul.codes{font:16px/2 monospace;padding-left:22px}.cancel{margin-top:20px;border-top:1px solid #ddd;padding-top:6px}.cancel button{background:#eef1f4;color:#344252}
        @media(max-width:560px){main{margin:16px;padding:20px}}
    </style>
</head>
<body><main>
    <div class="brand">Gudang Mama · Account security</div>
    <?php if (!empty($error)): ?><p class="error" role="alert"><?= $escape($error) ?></p><?php endif; ?>
    <?php if ($screen === 'setup'): ?>
        <h1>Set up Google Authenticator</h1>
        <p>Open Google Authenticator, tap <strong>+</strong>, then <strong>Scan a QR code</strong>.</p>
        <img class="qr" src="<?= $escape($qr) ?>" alt="Scan this QR code with Google Authenticator">
        <details><summary>Cannot scan the code?</summary><p>Choose “Enter a setup key”, name it “Gudang Mama”, and select “Time based”.</p><code><?= $escape($secret) ?></code></details>
        <form method="post" action="index.php?action=totp_confirm">
            <input type="hidden" name="csrf" value="<?= $escape(gm_csrf()) ?>">
            <label for="code">Enter the six-digit code from the app</label>
            <input type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus>
            <button type="submit">Confirm authenticator</button>
        </form>
    <?php elseif ($screen === 'challenge'): ?>
        <h1>Enter your authenticator code</h1>
        <p>Open Google Authenticator and enter the current code for Gudang Mama.</p>
        <form method="post" action="index.php?action=totp_verify">
            <input type="hidden" name="csrf" value="<?= $escape(gm_csrf()) ?>">
            <label for="code">Authenticator code or recovery code</label>
            <input type="text" id="code" name="code" maxlength="23" autocomplete="one-time-code" required autofocus>
            <button type="submit">Verify</button>
        </form>
        <p>Lost your phone? Enter one saved recovery code. You will set up your authenticator again before continuing.</p>
    <?php elseif ($screen === 'recovery'): ?>
        <h1>Save your recovery codes</h1>
        <p>Store these somewhere safe, separate from your phone. Each code works once, together with your password, if you lose access to the authenticator.</p>
        <ul class="codes"><?php foreach ($codes as $code): ?><li><?= $escape($code) ?></li><?php endforeach; ?></ul>
        <p>These codes will not be shown again after you continue. Any previous recovery codes have been replaced.</p>
        <form method="post" action="index.php?action=totp_acknowledge">
            <input type="hidden" name="csrf" value="<?= $escape(gm_csrf()) ?>">
            <label><input type="checkbox" name="saved" value="yes" required> I have saved my recovery codes.</label>
            <button type="submit">Continue to Gudang Mama</button>
        </form>
    <?php else: ?>
        <h1>Sign out?</h1><p>You will need your password and authenticator to sign in again.</p>
    <?php endif; ?>
    <form class="cancel" method="post" action="index.php?action=logout">
        <input type="hidden" name="csrf" value="<?= $escape(gm_csrf()) ?>">
        <button type="submit"><?= $screen === 'logout' ? 'Sign out' : 'Cancel and sign out' ?></button>
    </form>
</main></body></html>
