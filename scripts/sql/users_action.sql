-- Audit history for user operations. Import this file alone into an existing database.
-- Keep this definition in sync with table_commands.sql.
-- No foreign keys: history must survive deletion of users and documents.
CREATE TABLE IF NOT EXISTS `users_action` (
    `action_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` CHAR(36) NOT NULL,
    `username` VARCHAR(100) NOT NULL COMMENT 'Username at the time of the action',
    `action` VARCHAR(16) NOT NULL COMMENT 'CREATE, UPDATE, DELETE, VIEW, or EXPORT',
    `document_type` VARCHAR(32) NOT NULL COMMENT 'For example: slip_in, slip_out, slip_tax, invoice, payment, repack, moving',
    `reference_type` VARCHAR(32) NOT NULL COMMENT 'For example: no_LPB, no_sj, no_moving, no_repack, no_invoice, payment_id',
    `reference_value` VARCHAR(100) NOT NULL,
    `related_references` JSON DEFAULT NULL COMMENT 'Other identifiers belonging to this operation',
    `performed_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT 'Action time, separate from the document date',
    `before_data` JSON DEFAULT NULL COMMENT 'Document header, products, and affected related records before the change',
    `after_data` JSON DEFAULT NULL COMMENT 'Document header, products, and affected related records after the change',
    PRIMARY KEY (`action_id`),
    KEY `idx_users_action_time` (`performed_at`),
    KEY `idx_users_action_user_time` (`user_id`, `performed_at`),
    KEY `idx_users_action_reference` (`document_type`, `reference_type`, `reference_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
