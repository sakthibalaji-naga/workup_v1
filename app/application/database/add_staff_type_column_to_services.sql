ALTER TABLE `tblservices`
ADD COLUMN `staff_type` ENUM('department', 'all') NOT NULL DEFAULT 'department' COMMENT 'department=Filter by department, all=Show all staff';
