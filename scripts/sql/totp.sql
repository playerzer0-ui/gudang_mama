-- Authentication tables only: safe to import into an existing Gudang Mama database.
-- Keep these definitions in sync with the authentication section in table_commands.sql.
CREATE TABLE IF NOT EXISTS user_two_factor (
    user_id CHAR(36) PRIMARY KEY,
    secret TEXT NOT NULL,
    confirmed_at BIGINT NOT NULL,
    last_step BIGINT NOT NULL,
    recovery_hashes TEXT NOT NULL,
    version INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS auth_attempts (
    bucket CHAR(64) PRIMARY KEY,
    attempts INTEGER NOT NULL,
    reset_at BIGINT NOT NULL
);
