-- Add active column to tblapplications table
-- Run this SQL to add the active column with default value 1 (active)

ALTER TABLE `tblapplications`
ADD COLUMN `active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive';
