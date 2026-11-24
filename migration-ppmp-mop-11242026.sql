-- PPMP mode of procurement: the new list's longest option
-- ("Direct Procurement for Science, Technology and Innovation", 57 characters)
-- does not fit varchar(50). Widens tbl_ppmp_dt.mop to varchar(100).
-- Re-runnable; works on MariaDB and MySQL 8. Saved values are kept as they are.

ALTER TABLE `tbl_ppmp_dt` MODIFY `mop` varchar(100) NOT NULL;
