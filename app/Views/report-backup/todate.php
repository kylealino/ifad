<?php

$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_ps_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`project_title` LIKE '%General Administration and support%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_ps_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`project_title` LIKE '%General Administration and support%'
            ), 0) AS total_sub_month
        FROM tbl_saob_ps_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.program_title LIKE '%General Administration and support%' AND b.`code` != '50103010-00' AND b.`code` != '50102990-14' AND b.`code` != '50104990-06' AND b.`code` != '50104990-14'
    ) AS t;
");
$rw = $query->getRowArray();
$ps_todate_grand_total = $rw['grand_total'];

//UP TO DATE MOOE GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_mooe_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`project_title` LIKE '%General Administration%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_mooe_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`project_title` LIKE '%General Administration%'
            ), 0) AS total_sub_month
        FROM tbl_saob_mooe_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.program_title LIKE '%General Administration and support%'
    ) AS t;
");
$rw = $query->getRowArray();
$mooe_todate_grand_total = $rw['grand_total'];

//UP TO DATE CO GRAND TOTAL
$query = $this->db->query("
    SELECT 
        SUM(total_sub_month) AS grand_total
    FROM (
        SELECT
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_direct_co_dt d 
                JOIN tbl_ors_hd hd ON d.`project_id` = hd.`recid`
                WHERE d.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND d.`project_title` LIKE '%General Administration%'
            ), 0)
            +
            COALESCE((
                SELECT SUM(amount) 
                FROM tbl_ors_indirect_co_dt i 
                JOIN tbl_ors_hd hd ON i.`project_id` = hd.`recid`
                WHERE i.sub_object_code = b.particulars 
                AND hd.`ors_date` >= '$og_date_from' 
                AND hd.`ors_date` < '$og_date_to'
                AND i.`project_title` LIKE '%General Administration%'
            ), 0) AS total_sub_month
        FROM tbl_saob_co_dt AS b
        JOIN
        tbl_saob_hd saob
        on b.project_id = saob.recid
        WHERE
        saob.program_title LIKE '%General Administration and support%'
    ) AS t;
");
$rw = $query->getRowArray();
$co_todate_grand_total = $rw['grand_total'];

$todate_grand_total = $ps_todate_grand_total + $mooe_todate_grand_total + $co_todate_grand_total;