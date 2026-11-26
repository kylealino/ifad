-- My Account (myaccount, profile dropdown > My account): each user keeps their own
-- contact details and profile picture on myua_user.
--   email, contact_no : set by the user on My Account
--   avatar            : stored file name "<year>/<random>.<ext>" under
--                       writable/uploads/avatars/, served only through
--                       myaccount?meaction=PHOTO to signed-in users
-- Re-runnable; works on MariaDB and MySQL 8 (each column guarded by information_schema).
-- Take a dump of myua_user before running it.

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'myua_user' AND COLUMN_NAME = 'email') = 0,
  "ALTER TABLE `myua_user` ADD COLUMN `email` varchar(100) NOT NULL DEFAULT '' AFTER `position`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'myua_user' AND COLUMN_NAME = 'contact_no') = 0,
  "ALTER TABLE `myua_user` ADD COLUMN `contact_no` varchar(50) NOT NULL DEFAULT '' AFTER `email`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'myua_user' AND COLUMN_NAME = 'avatar') = 0,
  "ALTER TABLE `myua_user` ADD COLUMN `avatar` varchar(100) NOT NULL DEFAULT '' AFTER `contact_no`", "DO 0");
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
