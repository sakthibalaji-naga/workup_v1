-- Add position column to tblapplications table
-- Run this SQL to add the position column with default value 0 (last position)

ALTER TABLE `tblapplications`
ADD COLUMN `position` TINYINT(2) NOT NULL DEFAULT 0 COMMENT 'Position order (1-9), 0 means last';
