-- IFAD Portal, step 1 of 2 - run on the SORF database (locally `sorf-staging`).
--
-- SORF now runs inside the IFAD Portal and everyone signs in with their portal
-- (FODS) account. A SORF member row (sorf.myua_user) and everything a person
-- did in SORF are filed under their username, so a SORF account whose person
-- has a FODS account under another username is renamed to that FODS username.
--
-- CHECK THE PAIRS BELOW against the real SORF and FODS accounts before running
-- it on another server: old SORF username -> the same person's FODS username.
-- Re-runnable: once renamed, the old usernames are gone and nothing changes.
-- Then run migration-ifad-portal-fods-10282026.sql on the FODS database.

CREATE TEMPORARY TABLE ifad_user_map (
  `old_username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL PRIMARY KEY,
  `new_username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO ifad_user_map (`old_username`, `new_username`) VALUES
  ('CDS-KYLE', 'ADMIN-KYLE'),
  ('CDS-JOVY', 'ADMIN-JOVY'),
  ('kylealino1004', 'ADMIN-KYLE');

-- the member row itself (its password is no longer used: the portal signs in)
UPDATE myua_user u JOIN ifad_user_map m ON m.`old_username` = u.`username` COLLATE utf8mb4_general_ci
  SET u.`username` = m.`new_username`, u.`hash_password` = '';

-- every other place SORF files a username
UPDATE myua_user t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_division t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_document_type t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_employee t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_request_attachment t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_request_doc t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_request_doc t JOIN ifad_user_map m ON m.`old_username` = t.`released_by` COLLATE utf8mb4_general_ci SET t.`released_by` = m.`new_username`;
UPDATE tbl_request_dt t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_request_hd t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_request_hd t JOIN ifad_user_map m ON m.`old_username` = t.`handled_by` COLLATE utf8mb4_general_ci SET t.`handled_by` = m.`new_username`;
UPDATE tbl_request_hd t JOIN ifad_user_map m ON m.`old_username` = t.`noted_by_user` COLLATE utf8mb4_general_ci SET t.`noted_by_user` = m.`new_username`;
UPDATE tbl_request_lto t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_request_lto_expense t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_request_msg t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_request_seen t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_request_seen t JOIN ifad_user_map m ON m.`old_username` = t.`username` COLLATE utf8mb4_general_ci SET t.`username` = m.`new_username`;
UPDATE tbl_roles t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_role_permission t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;
UPDATE tbl_section t JOIN ifad_user_map m ON m.`old_username` = t.`added_by` COLLATE utf8mb4_general_ci SET t.`added_by` = m.`new_username`;

DROP TEMPORARY TABLE ifad_user_map;
