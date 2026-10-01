-- =====================================================================
-- Messages module (mymessages): user-to-user chats and support tickets.
--
--   Chats   - direct (2 people) or group conversations between FODS users.
--             tbl_conversation_hd / _member / _msg. A member's last_read_id
--             is the newest message they have seen (drives unread counts).
--   Tickets - requests and bug reports, with image attachments.
--             tbl_ticket_hd (header) / tbl_ticket_dt (thread: the first row
--             is the description, then replies and status changes) /
--             tbl_ticket_attachment (files under writable/uploads/tickets)
--             / tbl_ticket_seen (per-user read marker).
--
-- Everyone can chat and file tickets. New access family 37:
--   37001 Support Tickets - View    (see everyone's tickets)
--   37003 Support Tickets - Update  (work tickets: status, reply as support)
--
-- Replaces the old Support Chat (tbl_support_*), which is left untouched.
-- Safe to re-run. Deploy together with Config\AccessCodes.
-- Backup: __db_backup/fods-local (pre-messages-module-09302026).sql
-- =====================================================================

-- 1. Chats
CREATE TABLE IF NOT EXISTS `tbl_conversation_hd` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `conv_type` varchar(10) NOT NULL DEFAULT 'direct',
  `title` varchar(100) NOT NULL DEFAULT '',
  `last_msg_at` datetime DEFAULT NULL,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_conversation_member` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `conv_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `last_read_id` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`),
  UNIQUE KEY `conv_user` (`conv_id`, `username`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_conversation_msg` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `conv_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`),
  KEY `conv_id` (`conv_id`, `recid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Tickets
CREATE TABLE IF NOT EXISTS `tbl_ticket_hd` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_no` varchar(20) NOT NULL DEFAULT '',
  `category` varchar(20) NOT NULL DEFAULT 'Request',
  `priority` varchar(10) NOT NULL DEFAULT 'Normal',
  `module` varchar(100) NOT NULL DEFAULT '',
  `subject` varchar(150) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'Open',
  `handled_by` varchar(50) NOT NULL DEFAULT '',
  `updated_at` datetime DEFAULT NULL,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`),
  KEY `added_by` (`added_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_ticket_dt` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_system` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`),
  KEY `ticket_id` (`ticket_id`, `recid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_ticket_attachment` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `reply_id` int(11) NOT NULL DEFAULT 0,
  `file_name` varchar(100) NOT NULL DEFAULT '',
  `orig_name` varchar(255) NOT NULL DEFAULT '',
  `mime_type` varchar(50) NOT NULL DEFAULT '',
  `file_size` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_ticket_seen` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `last_read_id` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`),
  UNIQUE KEY `ticket_user` (`ticket_id`, `username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Access codes
INSERT INTO tbl_access_modules (access_code, access_name, added_by)
SELECT c.access_code, c.access_name, 'SYSTEM'
FROM (
  SELECT '37001' access_code, 'Support Tickets - View' access_name
  UNION ALL SELECT '37003', 'Support Tickets - Update'
) c
WHERE NOT EXISTS (SELECT 1 FROM tbl_access_modules am WHERE am.access_code = c.access_code);

-- 4. The Administrator role gets both; assign them to other roles in User Management
INSERT IGNORE INTO tbl_role_access (role_id, access_code, added_by)
SELECT r.recid, c.code, 'SYSTEM'
FROM tbl_roles r
CROSS JOIN (SELECT '37001' code UNION ALL SELECT '37003') c
WHERE r.role_name = 'Administrator'
AND NOT EXISTS (SELECT 1 FROM tbl_role_access ra WHERE ra.role_id = r.recid AND ra.access_code = c.code);

-- keep the per-user cache (tbl_user_access) in step for users with that role
INSERT INTO tbl_user_access (username, access_code, is_active, added_by)
SELECT u.username, c.code, 1, 'SYSTEM'
FROM myua_user u
JOIN tbl_roles r ON r.recid = u.role_id AND r.role_name = 'Administrator'
CROSS JOIN (SELECT '37001' code UNION ALL SELECT '37003') c
WHERE NOT EXISTS (SELECT 1 FROM tbl_user_access x WHERE x.username = u.username AND x.access_code = c.code);
