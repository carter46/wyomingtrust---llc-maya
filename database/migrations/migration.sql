-- WyomingTrust Database Migration
-- Run this file to update your existing database with the latest schema changes
-- Safe to run multiple times - includes existence checks

-- ============================================================================
-- Migration: Add favicon column to site_settings table
-- ============================================================================

SET @dbname = DATABASE();
SET @tablename = 'site_settings';
SET @columnname = 'favicon';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) DEFAULT NULL AFTER logo')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- Migration: Ensure payment_methods config_data column exists
-- ============================================================================

SET @tablename = 'payment_methods';
SET @columnname = 'config_data';

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' JSON DEFAULT NULL AFTER is_active')
));
PREPARE alterIfNotExists2 FROM @preparedStatement;
EXECUTE alterIfNotExists2;
DEALLOCATE PREPARE alterIfNotExists2;

-- ============================================================================
-- Migration: Add payment_method_id to user_trusts (admin can see selected method)
-- ============================================================================

SET @tablename = 'user_trusts';
SET @columnname = 'payment_method_id';

-- Add column if missing
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT UNSIGNED DEFAULT NULL AFTER trust_service_id')
));
PREPARE alterIfNotExists3 FROM @preparedStatement;
EXECUTE alterIfNotExists3;
DEALLOCATE PREPARE alterIfNotExists3;

-- Add index if missing
SET @indexname = 'idx_user_trusts_payment_method_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX ', @indexname, ' (', @columnname, ')')
));
PREPARE alterIfNotExists4 FROM @preparedStatement;
EXECUTE alterIfNotExists4;
DEALLOCATE PREPARE alterIfNotExists4;

-- Add foreign key if missing
SET @fkname = 'fk_user_trusts_payment_method';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (CONSTRAINT_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = @fkname)
      AND (CONSTRAINT_TYPE = 'FOREIGN KEY')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT ', @fkname, ' FOREIGN KEY (', @columnname, ') REFERENCES payment_methods(id) ON DELETE SET NULL')
));
PREPARE alterIfNotExists5 FROM @preparedStatement;
EXECUTE alterIfNotExists5;
DEALLOCATE PREPARE alterIfNotExists5;

-- ============================================================================
-- Migration: Create wallet_addresses table
-- ============================================================================

SET @tablename = 'wallet_addresses';

-- Check if table exists
SET @tableexists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename);

SET @preparedStatement = IF(@tableexists > 0,
    'SELECT 1',
    'CREATE TABLE wallet_addresses (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        coin_id INT UNSIGNED NOT NULL,
        address VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_wallet_addresses_coin (coin_id),
        CONSTRAINT fk_wallet_addresses_coin FOREIGN KEY (coin_id) 
            REFERENCES coins(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

PREPARE createWalletAddressesTable FROM @preparedStatement;
EXECUTE createWalletAddressesTable;
DEALLOCATE PREPARE createWalletAddressesTable;

-- ============================================================================
-- Migration: Create crypto_price_cache table for server-side price caching
-- ============================================================================

SET @tablename = 'crypto_price_cache';

-- Check if table exists
SET @tableexists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename);

SET @preparedStatement = IF(@tableexists > 0,
    'SELECT 1',
    'CREATE TABLE crypto_price_cache (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        coin_key VARCHAR(100) NOT NULL,
        price_usd DECIMAL(20, 8) NOT NULL,
        change_24h DECIMAL(10, 4) DEFAULT 0,
        last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_coin_key (coin_key),
        INDEX idx_last_updated (last_updated)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

PREPARE createCryptoPriceCacheTable FROM @preparedStatement;
EXECUTE createCryptoPriceCacheTable;
DEALLOCATE PREPARE createCryptoPriceCacheTable;

-- ============================================================================
-- Migration: Add wallet_link_use_modal and wallet_link_url to site_settings
-- ============================================================================

SET @tablename = 'site_settings';

-- Add wallet_link_use_modal column
SET @columnname = 'wallet_link_use_modal';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TINYINT(1) NOT NULL DEFAULT 1 AFTER require_email_verification')
));
PREPARE alterIfNotExistsWalletModal FROM @preparedStatement;
EXECUTE alterIfNotExistsWalletModal;
DEALLOCATE PREPARE alterIfNotExistsWalletModal;

-- Add wallet_link_url column
SET @columnname = 'wallet_link_url';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(500) DEFAULT NULL AFTER wallet_link_use_modal')
));
PREPARE alterIfNotExistsWalletUrl FROM @preparedStatement;
EXECUTE alterIfNotExistsWalletUrl;
DEALLOCATE PREPARE alterIfNotExistsWalletUrl;

-- ============================================================================
-- Migration: Add asset_types JSON to trust_services
-- ============================================================================

SET @tablename = 'trust_services';
SET @columnname = 'asset_types';

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' JSON DEFAULT NULL AFTER description')
));
PREPARE alterIfNotExistsAssetTypes FROM @preparedStatement;
EXECUTE alterIfNotExistsAssetTypes;
DEALLOCATE PREPARE alterIfNotExistsAssetTypes;

-- ============================================================================
-- Migration: Add asset_category_config and liquidation_fee to trust_services
-- ============================================================================

SET @tablename = 'trust_services';

SET @columnname = 'asset_category_config';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (TABLE_SCHEMA = @dbname) AND (TABLE_NAME = @tablename) AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' JSON DEFAULT NULL AFTER asset_types')
));
PREPARE alterIfNotExistsAssetCatConfig FROM @preparedStatement;
EXECUTE alterIfNotExistsAssetCatConfig;
DEALLOCATE PREPARE alterIfNotExistsAssetCatConfig;

SET @columnname = 'liquidation_fee';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (TABLE_SCHEMA = @dbname) AND (TABLE_NAME = @tablename) AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(10,2) DEFAULT 0.00 AFTER is_free')
));
PREPARE alterIfNotExistsLiquidationFee FROM @preparedStatement;
EXECUTE alterIfNotExistsLiquidationFee;
DEALLOCATE PREPARE alterIfNotExistsLiquidationFee;

-- Deactivate legacy Crypto Asset Trust (merged into Smart Contract Trust)
UPDATE trust_services SET is_active = 0 WHERE service_key = 'crypto_asset_trust';

-- Per-coin liquidation fee (optional; used when liquidating individual crypto assets)
SET @tablename = 'coins';
SET @columnname = 'liquidation_fee';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (TABLE_SCHEMA = @dbname) AND (TABLE_NAME = @tablename) AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(10,2) DEFAULT 0.00 AFTER logo')
));
PREPARE alterIfNotExistsCoinLiqFee FROM @preparedStatement;
EXECUTE alterIfNotExistsCoinLiqFee;
DEALLOCATE PREPARE alterIfNotExistsCoinLiqFee;

-- ============================================================================
-- Migration: Add transaction_data JSON to transactions (deposits / liquidations)
-- ============================================================================

SET @tablename = 'transactions';
SET @columnname = 'transaction_data';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (TABLE_SCHEMA = @dbname) AND (TABLE_NAME = @tablename) AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' LONGTEXT NULL AFTER metadata')
));
PREPARE alterIfNotExistsTxData FROM @preparedStatement;
EXECUTE alterIfNotExistsTxData;
DEALLOCATE PREPARE alterIfNotExistsTxData;

-- trust_id on transactions (links deposits/liquidations to a trust)
SET @columnname = 'trust_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (TABLE_SCHEMA = @dbname) AND (TABLE_NAME = @tablename) AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT UNSIGNED DEFAULT NULL AFTER user_id')
));
PREPARE alterIfNotExistsTxTrustId FROM @preparedStatement;
EXECUTE alterIfNotExistsTxTrustId;
DEALLOCATE PREPARE alterIfNotExistsTxTrustId;

-- coin_id on transactions
SET @columnname = 'coin_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (TABLE_SCHEMA = @dbname) AND (TABLE_NAME = @tablename) AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT UNSIGNED DEFAULT NULL AFTER trust_id')
));
PREPARE alterIfNotExistsTxCoinId FROM @preparedStatement;
EXECUTE alterIfNotExistsTxCoinId;
DEALLOCATE PREPARE alterIfNotExistsTxCoinId;

-- ============================================================================
-- Migration Complete
-- ============================================================================

SELECT 'Migration completed successfully!' AS status;
