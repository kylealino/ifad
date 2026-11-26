-- Indexes for the obligation lookups (SAOB PDF, Dashboard, FODS Assistant).
-- The ORS / BURS detail tables had no index besides recid, so every per-line
-- SUM in the SAOB PDF scanned the whole table. Adds:
--   detail tables: project_id (join to the header), sub_object_code, uacs_code
--   headers:       ors_date / burs_date
--   SAOB lines:    project_id
-- Re-runnable, and runs on MariaDB and MySQL 8 (each index is created only when
-- missing, checked through information_schema). Take a backup dump first.

DROP PROCEDURE IF EXISTS fods_add_index;
DELIMITER //
CREATE PROCEDURE fods_add_index(IN p_table VARCHAR(64), IN p_index VARCHAR(64), IN p_cols VARCHAR(255))
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.TABLES
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_table)
       AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_table AND INDEX_NAME = p_index) THEN
        SET @fods_sql = CONCAT('ALTER TABLE `', p_table, '` ADD INDEX `', p_index, '` (', p_cols, ')');
        PREPARE fods_stmt FROM @fods_sql;
        EXECUTE fods_stmt;
        DEALLOCATE PREPARE fods_stmt;
    END IF;
END //
DELIMITER ;

-- ORS / BURS detail lines
CALL fods_add_index('tbl_ors_direct_ps_dt',     'idx_project', '`project_id`');
CALL fods_add_index('tbl_ors_direct_ps_dt',     'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_ors_direct_ps_dt',     'idx_uacs',    '`uacs_code`');
CALL fods_add_index('tbl_ors_direct_mooe_dt',   'idx_project', '`project_id`');
CALL fods_add_index('tbl_ors_direct_mooe_dt',   'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_ors_direct_mooe_dt',   'idx_uacs',    '`uacs_code`');
CALL fods_add_index('tbl_ors_direct_co_dt',     'idx_project', '`project_id`');
CALL fods_add_index('tbl_ors_direct_co_dt',     'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_ors_direct_co_dt',     'idx_uacs',    '`uacs_code`');
CALL fods_add_index('tbl_ors_indirect_ps_dt',   'idx_project', '`project_id`');
CALL fods_add_index('tbl_ors_indirect_ps_dt',   'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_ors_indirect_ps_dt',   'idx_uacs',    '`uacs_code`');
CALL fods_add_index('tbl_ors_indirect_mooe_dt', 'idx_project', '`project_id`');
CALL fods_add_index('tbl_ors_indirect_mooe_dt', 'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_ors_indirect_mooe_dt', 'idx_uacs',    '`uacs_code`');
CALL fods_add_index('tbl_ors_indirect_co_dt',   'idx_project', '`project_id`');
CALL fods_add_index('tbl_ors_indirect_co_dt',   'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_ors_indirect_co_dt',   'idx_uacs',    '`uacs_code`');

CALL fods_add_index('tbl_burs_direct_ps_dt',     'idx_project', '`project_id`');
CALL fods_add_index('tbl_burs_direct_ps_dt',     'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_burs_direct_ps_dt',     'idx_uacs',    '`uacs_code`');
CALL fods_add_index('tbl_burs_direct_mooe_dt',   'idx_project', '`project_id`');
CALL fods_add_index('tbl_burs_direct_mooe_dt',   'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_burs_direct_mooe_dt',   'idx_uacs',    '`uacs_code`');
CALL fods_add_index('tbl_burs_direct_co_dt',     'idx_project', '`project_id`');
CALL fods_add_index('tbl_burs_direct_co_dt',     'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_burs_direct_co_dt',     'idx_uacs',    '`uacs_code`');
CALL fods_add_index('tbl_burs_indirect_ps_dt',   'idx_project', '`project_id`');
CALL fods_add_index('tbl_burs_indirect_ps_dt',   'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_burs_indirect_ps_dt',   'idx_uacs',    '`uacs_code`');
CALL fods_add_index('tbl_burs_indirect_mooe_dt', 'idx_project', '`project_id`');
CALL fods_add_index('tbl_burs_indirect_mooe_dt', 'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_burs_indirect_mooe_dt', 'idx_uacs',    '`uacs_code`');
CALL fods_add_index('tbl_burs_indirect_co_dt',   'idx_project', '`project_id`');
CALL fods_add_index('tbl_burs_indirect_co_dt',   'idx_subobj',  '`sub_object_code`');
CALL fods_add_index('tbl_burs_indirect_co_dt',   'idx_uacs',    '`uacs_code`');

-- Headers: the month / year-to-date filters
CALL fods_add_index('tbl_ors_hd',  'idx_ors_date',  '`ors_date`');
CALL fods_add_index('tbl_burs_hd', 'idx_burs_date', '`burs_date`');

-- SAOB allotment lines
CALL fods_add_index('tbl_saob_ps_dt',   'idx_project', '`project_id`');
CALL fods_add_index('tbl_saob_mooe_dt', 'idx_project', '`project_id`');
CALL fods_add_index('tbl_saob_co_dt',   'idx_project', '`project_id`');

DROP PROCEDURE IF EXISTS fods_add_index;
