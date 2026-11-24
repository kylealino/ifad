-- PPMP supporting documents: files attached to a PPMP (the Documents tab of the PPMP window).
-- The files themselves live under writable/uploads/ppmp/<year>/ (file_name = "<year>/<random>.<ext>")
-- and are served only through myppmp?meaction=DOC-FILE after a PPMP View check.
-- Re-runnable; works on MariaDB and MySQL 8.

CREATE TABLE IF NOT EXISTS `tbl_ppmp_attachment` (
  `recid` int(11) NOT NULL AUTO_INCREMENT,
  `ppmp_id` int(11) NOT NULL,
  `file_name` varchar(100) NOT NULL DEFAULT '',
  `orig_name` varchar(255) NOT NULL DEFAULT '',
  `mime_type` varchar(150) NOT NULL DEFAULT '',
  `file_size` int(11) NOT NULL DEFAULT 0,
  `is_image` int(11) NOT NULL DEFAULT 0,
  `added_by` varchar(50) NOT NULL DEFAULT '',
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recid`),
  KEY `ppmp_id` (`ppmp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
