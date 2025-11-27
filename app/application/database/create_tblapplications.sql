-- Minimal table definition for tblapplications used by admin Application module
-- Create this table in your application database (adjust engine/charset if needed)

-- Create table with minimal columns required by UI
CREATE TABLE IF NOT EXISTS `tblapplications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(191) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Drop legacy columns if they exist (safe to run)
-- NOTE: Backup your data before dropping columns if you need to preserve values
ALTER TABLE `tblapplications` 
  DROP COLUMN IF EXISTS `incharge_department`,
  DROP COLUMN IF EXISTS `service_id`;
