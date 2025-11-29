-- Create the task_approvals table
CREATE TABLE IF NOT EXISTS `tbltask_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `step_order` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_at` datetime DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `comments` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `staff_id` (`staff_id`),
  KEY `step_order` (`step_order`),
  CONSTRAINT `fk_task_approvals_task` FOREIGN KEY (`task_id`) REFERENCES `tbltasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_task_approvals_staff` FOREIGN KEY (`staff_id`) REFERENCES `tblstaff` (`staffid`) ON DELETE CASCADE
