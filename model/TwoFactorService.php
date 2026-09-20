<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;

final class TwoFactorService
{
    private PDO $db;
    private Google2FA $totp;
    private string $key;

    public function __construct(PDO $db, string $encodedKey)
    {
        $this->db = $db;
        $this->totp = new Google2FA();
        $key = base64_decode($encodedKey, true);
        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('A 32-byte base64 TOTP encryption key is required.');
        }
        $this->key = $key;
    }

    public static function schema(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS user_two_factor (
                user_id CHAR(36) PRIMARY KEY,
                secret TEXT NOT NULL,
                confirmed_at BIGINT NOT NULL,
                last_step BIGINT NOT NULL,
                recovery_hashes TEXT NOT NULL,
                version INTEGER NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS auth_attempts (
                bucket CHAR(64) PRIMARY KEY,
                attempts INTEGER NOT NULL,
                reset_at BIGINT NOT NULL
            )"
        ];
    }

    public function state(string $userId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM user_two_factor WHERE user_id = ?');
        $statement->execute([$userId]);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function newSecret(): string
    {
        return $this->totp->generateSecretKey(32);
    }

    public function qrDataUri(string $username, string $secret): string
    {
        $renderer = new BaconQrCode\Renderer\ImageRenderer(
            new BaconQrCode\Renderer\RendererStyle\RendererStyle(260),
            new BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $svg = (new BaconQrCode\Writer($renderer))->writeString(
            $this->totp->getQRCodeUrl('Gudang Mama', $username, $secret)
        );
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function seal(string $secret): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($secret, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($ciphertext === false) {
            throw new RuntimeException('Cannot encrypt authenticator secret.');
        }
        return base64_encode($iv . $tag . $ciphertext);
    }

    private function unseal(string $sealed): string
    {
        $bytes = base64_decode($sealed, true);
        if ($bytes === false || strlen($bytes) < 29) {
            throw new RuntimeException('Invalid encrypted authenticator secret.');
        }
        $secret = openssl_decrypt(substr($bytes, 28), 'aes-256-gcm', $this->key,
            OPENSSL_RAW_DATA, substr($bytes, 0, 12), substr($bytes, 12, 16));
        if ($secret === false) {
            throw new RuntimeException('Cannot decrypt authenticator secret.');
        }
        return $secret;
    }

    private function lockSuffix(): string
    {
        return $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
    }

    // Counters live in the database, so a new browser session cannot reset them.
    public function attempt(string $identity, int $limit = 5, int $seconds = 300): bool
    {
        $bucket = hash('sha256', $identity);
        $now = time();
        $this->db->beginTransaction();
        try {
            $insert = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
                ? 'INSERT IGNORE' : 'INSERT OR IGNORE';
            $this->db->prepare("$insert INTO auth_attempts (bucket, attempts, reset_at) VALUES (?, 0, ?)")
                ->execute([$bucket, $now + $seconds]);
            $statement = $this->db->prepare('SELECT attempts, reset_at FROM auth_attempts WHERE bucket = ?' . $this->lockSuffix());
            $statement->execute([$bucket]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $attempts = (int)$row['reset_at'] <= $now ? 0 : (int)$row['attempts'];
            $reset = (int)$row['reset_at'] <= $now ? $now + $seconds : (int)$row['reset_at'];
            $allowed = $attempts < $limit;
            if ($allowed) {
                $this->db->prepare('UPDATE auth_attempts SET attempts = ?, reset_at = ? WHERE bucket = ?')
                    ->execute([$attempts + 1, $reset, $bucket]);
            }
            $this->db->commit();
            return $allowed;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }

    // expectedVersion is set only after a one-use recovery code was consumed.
    public function enroll(string $userId, string $secret, string $code, ?int $expectedVersion = null): ?array
    {
        if (!preg_match('/^[0-9]{6}$/D', $code)) return null;
        $step = $this->totp->verifyKeyNewer($secret, $code, 0, 1);
        if ($step === false) return null;
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $raw = bin2hex(random_bytes(10));
            $codes[] = implode('-', str_split($raw, 5));
        }
        $hashes = json_encode(array_map(fn($code) => hash('sha256', str_replace('-', '', $code)), $codes), JSON_THROW_ON_ERROR);
        $encrypted = $this->seal($secret);
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare('SELECT version FROM user_two_factor WHERE user_id = ?' . $this->lockSuffix());
            $statement->execute([$userId]);
            $existing = $statement->fetch(PDO::FETCH_ASSOC);
            if ($existing && ($expectedVersion === null || (int)$existing['version'] !== $expectedVersion)) {
                $this->db->rollBack();
                return null;
            }
            if (!$existing && $expectedVersion !== null) {
                $this->db->rollBack();
                return null;
            }
            if ($existing) {
                $this->db->prepare('UPDATE user_two_factor SET secret = ?, confirmed_at = ?, last_step = ?, recovery_hashes = ?, version = version + 1 WHERE user_id = ?')
                    ->execute([$encrypted, time(), $step, $hashes, $userId]);
            } else {
                $this->db->prepare('INSERT INTO user_two_factor (user_id, secret, confirmed_at, last_step, recovery_hashes, version) VALUES (?, ?, ?, ?, ?, 1)')
                    ->execute([$userId, $encrypted, time(), $step, $hashes]);
            }
            $this->db->commit();
            return $codes;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }

    // Return the verified version; callers bind the completed session to this version.
    public function verify(string $userId, string $code): ?int
    {
        if (!preg_match('/^[0-9]{6}$/D', $code)) return null;
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare('SELECT * FROM user_two_factor WHERE user_id = ?' . $this->lockSuffix());
            $statement->execute([$userId]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $step = $row ? $this->totp->verifyKeyNewer($this->unseal($row['secret']), $code, (int)$row['last_step'], 1) : false;
            if ($step === false) {
                $this->db->rollBack();
                return null;
            }
            $this->db->prepare('UPDATE user_two_factor SET last_step = ? WHERE user_id = ?')->execute([$step, $userId]);
            $this->db->commit();
            return (int)$row['version'];
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }

    public function recover(string $userId, string $code): ?int
    {
        $normalized = str_replace('-', '', strtolower(trim($code)));
        if (!preg_match('/^[a-f0-9]{20}$/D', $normalized)) return null;
        $candidate = hash('sha256', $normalized);
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare('SELECT recovery_hashes, version FROM user_two_factor WHERE user_id = ?' . $this->lockSuffix());
            $statement->execute([$userId]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $hashes = $row ? json_decode($row['recovery_hashes'], true, 512, JSON_THROW_ON_ERROR) : [];
            foreach ($hashes as $index => $hash) {
                if (hash_equals($hash, $candidate)) {
                    unset($hashes[$index]);
                    $this->db->prepare('UPDATE user_two_factor SET recovery_hashes = ? WHERE user_id = ?')
                        ->execute([json_encode(array_values($hashes), JSON_THROW_ON_ERROR), $userId]);
                    $this->db->commit();
                    return (int)$row['version'];
                }
            }
            $this->db->rollBack();
            return null;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }
}
