-- IFAD Portal, step 2 of 2 - run on the FODS database (locally `fods-staging`),
-- after migration-ifad-portal-sorf-10282026.sql ran on the SORF database.
--
-- Every SORF member needs a portal account (FODS myua_user) to sign in. A
-- member with no FODS account under the same username (e.g. the HR accounts)
-- gets one here: same username, name, office and position, NO FODS role (so
-- FODS stays closed to them), and their SORF password hash, which the portal
-- login accepts (bcrypt), so they keep their password. SORF's copy of the
-- hash is then blanked: the portal is the only place that signs in.
--
-- SET @sorf_db to the SORF database's name on this server (both databases
-- must be on the same MySQL/MariaDB server). Re-runnable: accounts that
-- already exist are left as they are.

SET @sorf_db := 'sorf-staging';

SET @s := CONCAT(
  'INSERT INTO myua_user (`username`, `hash_password`, `full_name`, `division`, `section`, `position`, `role_id`, `added_by`) ',
  'SELECT s.`username`, s.`hash_password`, LEFT(s.`full_name`, 50), s.`division`, s.`section`, LEFT(s.`position`, 50), NULL, ''IFAD-MIGRATION'' ',
  'FROM `', @sorf_db, '`.myua_user s ',
  'WHERE s.`hash_password` LIKE ''$2y$%'' ',
  'AND NOT EXISTS (SELECT 1 FROM myua_user f WHERE f.`username` = s.`username` COLLATE utf8mb4_general_ci)');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- every SORF member now signs in through the portal
SET @s := CONCAT(
  'UPDATE `', @sorf_db, '`.myua_user s SET s.`hash_password` = '''' ',
  'WHERE s.`hash_password` <> '''' ',
  'AND EXISTS (SELECT 1 FROM myua_user f WHERE f.`username` = s.`username` COLLATE utf8mb4_general_ci)');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
