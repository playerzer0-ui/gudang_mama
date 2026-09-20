# Google Authenticator for Gudang Mama

All accounts must complete password + TOTP authentication. No warehouse workflow or existing user-table columns were changed.

## User flow

1. Sign in with the existing username and password.
2. On first login, scan the QR code using Google Authenticator (or enter the displayed time-based setup key).
3. Confirm a six-digit code, then save eight recovery codes.
4. Later logins ask for the password and a fresh authenticator code.
5. If a phone is lost, enter a recovery code after the password. Complete enrollment on the replacement phone. Old recovery codes and previously authenticated sessions are invalidated when replacement enrollment completes.

Pending logins expire after ten minutes. Password-only, pre-upgrade, and recovery-pending sessions cannot access warehouse actions. Password changes invalidate existing authentication sessions. Codes already accepted cannot be reused; wait for the next code if you sign out immediately after enrollment.

There is no email-verification or email-code step. Google Authenticator works through a shared secret; the app does not send the secret to Google. QR codes are generated locally with BaconQrCode.

## Install / activate another environment

Requires PHP 8.2+, PDO MySQL, OpenSSL, XMLWriter, and Composer dependencies:

```powershell
composer install --no-dev --no-interaction
php scripts/setup_totp.php --print-sql
php scripts/setup_totp.php --apply --host=localhost --key-file=C:/xampp/private/gudang_mama_totp.key
```

Back up the database before applying in a company environment. The host explicitly selects the existing connection in model/database.php: localhost selects its local credentials; other values select its other connection. Check that configuration before running.

The script creates only user_two_factor and auth_attempts. It does not alter users, orders, products, invoices, or payments. It never overwrites an existing key. Re-running it with the same key path is safe.

The generated config/totp.local.php contains only the key-file path and is ignored by Git. The key itself must be outside the document root, readable by PHP and restricted to the app's operating-system account. The helper also supports GUDANG_MAMA_TOTP_KEY (base64, exactly 32 decoded bytes) or GUDANG_MAMA_TOTP_KEY_FILE, which take precedence over local configuration.

Keep an access-controlled backup of the encryption key alongside database backups. Do not replace it after enrollment: existing secrets require that key. Do not commit or send the key, QR codes, or recovery codes. Use HTTPS in deployment; if TLS terminates at a proxy, configure the server to expose the trusted HTTPS state to PHP.

The current XAMPP installation has been initialized with C:/xampp/private/gudang_mama_totp.key.

## Implementation

- Standalone pragmarx/google2fa; Laravel is not required.
- AES-256-GCM encryption for stored secrets.
- Recovery codes contain 80 random bits each and only SHA-256 hashes are stored in the database.
- Database-backed attempt counters: five per account per five minutes; password login additionally allows at most 30 attempts per source IP per five minutes. Successful attempts also count.
- Row locking on MySQL protects code consumption against simultaneous requests.
- CSRF and POST checks cover login, TOTP confirmation/verification, recovery acknowledgement, and logout.
- Secret-bearing screens use no-store caching and generate QR images locally.
- Missing configuration or unavailable authentication storage fails closed.
- Existing business forms retain their existing behavior; this change does not add CSRF protection to every warehouse form or redesign the existing role permissions.

If a user loses both their phone and recovery codes, a trusted administrator must verify their identity before resetting their enrollment. No password-only reset or public bypass has been added.

Expired attempt rows can be removed during normal database maintenance: DELETE FROM auth_attempts WHERE reset_at < UNIX_TIMESTAMP(). They contain only hashed bucket identifiers and counters.

## Verification

```powershell
php tests/totp_test.php
php tests/totp_http_test.php
```

These use isolated SQLite databases. The HTTP test copies application files into a temporary directory, substitutes only database configuration, runs a loopback PHP server, and removes its test directory afterward. It never uses company records or enrolls real accounts.

References:
- https://github.com/antonioribeiro/google2fa
- https://github.com/Bacon/BaconQrCode

Composer audit also reports existing advisories in phpoffice/phpspreadsheet. Its version was not changed as part of authentication work.
