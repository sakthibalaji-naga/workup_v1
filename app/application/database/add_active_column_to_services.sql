-- Add active column to tblservices table
-- This migration adds an active column to the services table to enable active/inactive functionality

ALTER TABLE `tblservices`
ADD COLUMN `active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive';

-- Update existing records to be active by default
UPDATE `tblservices` SET `active` = 1 WHERE `active` IS NULL OR `active` = 0;
