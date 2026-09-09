<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$month = $this->request->getPostGet('month');
$year = $this->request->getPostGet('year');
$action = $this->request->getPostGet('action');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');
require APPPATH . 'ThirdParty/fpdf/fpdf.php';
$currentDate = date("Y-m-d");
$formattedDate = date("F j, Y", strtotime($currentDate));
$query = $this->db->query("
SELECT
    *
FROM
    `tbl_ors_hd`
WHERE 
    `recid` = '$recid'"
);

$data = $query->getRowArray();
$particulars = $data['particulars'];
$funding_source = $data['funding_source'];
$payee_name = $data['payee_name'];
$payee_office = $data['payee_office'];
$payee_address = $data['payee_address'];
$certified_a = $data['certified_a'];
$position_a = $data['position_a'];
$certified_b = $data['certified_b'];
$position_b = $data['position_b'];
$serialno = $data['serialno'];
$ors_date = $data['ors_date'];
$fund_cluster_code = $data['fund_cluster_code'];

//certify a division
$query = $this->db->query("
SELECT
    `division`
FROM
    `myua_user`
WHERE 
    `full_name` = '$certified_a'"
);
$data = $query->getRowArray();
$division_a = $data['division'];

//certify b section
$query = $this->db->query("
SELECT
    `section`
FROM
    `myua_user`
WHERE 
    `full_name` = '$certified_b'"
);
$data = $query->getRowArray();
$section_a = $data['section'];

    
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('ORS Print');
$pdf->SetFont('Times', 'B', 16);

$pdf->SetXY(0, 8);

$Y = 8;
$pdf->SetFont('Times', 'B', 10);
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'FAD-BS-001' , 'TRL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'Revision 0' , 'RL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'June 10 2024' , 'BRL', 1, 'L');

$Y = $pdf->GetY()+4;
$pdf->SetXY(170, $Y);
$pdf->SetFont('Times', 'I', 10);
$pdf->Cell(30, 4, 'Appendix 11' , 0, 1, 'R');


$Y = 32;
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', 'B', 12);

$pdf->Cell(120, 2, '' , 'TRL', 1, 'C');
$Y = 2;
$pdf->Cell(120, 5, 'OBLIGATION REQUEST AND STATUS' , 'RL', 1, 'C');

$Y += 5;
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(120, 1, '' , 'RL', 1, 'C');

$Y += 1;
$pdf->SetFont('Times', 'B', 12);
$pdf->Cell(120, 8, 'FOOD AND NUTRITION RESEARCH INSTITUTE' , 'RL', 1, 'C');

$Y = 32;
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(120, 5, 'Entity Name' , 'RL', 1, 'C');

$Y = 32;

$pdf->SetFont('Times', 'B', 10);
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 2, '' , 'T', 1, 'C');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 2, '' , 'TR', 1, 'L');
$Y += 2;
$pdf->SetFont('Times', '', 10);
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 5, 'Serial No.:' ,0, 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(50, 5, $serialno , 'R', 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(40, 5, '' , 'B', 1, 'L');
$Y += 5;
$pdf->SetXY(130, $Y);
$pdf->SetFont('Times', '', 10);
$pdf->Cell(20, 5, 'Date:' , 0, 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, '' , 'R', 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(40, 5, $ors_date , 'B', 1, 'L');
$Y += 5;
$pdf->SetXY(130, $Y);
$pdf->SetFont('Times', '', 10);
$pdf->Cell(20, 5, 'Fund Cluster:' , 0, 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(40, 5, $fund_cluster_code , 'B', 1, 'C');
$pdf->SetXY(190, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(10, 5, '' , 'R', 1, 'C');

$Y += 5;
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 4, '' , 'L', 1, 'C');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 4, '' , 'R', 1, 'C');

$pdf->SetFont('Times', '', 10);
$Y +=4;
$pdf->SetXY(10, $Y);
$pdf->Cell(37, 7, 'Payee' , 1, 1, 'C');
$pdf->SetFont('Times', 'B', 10);
$pdf->SetXY(47, $Y);
$pdf->Cell(153, 7, $payee_name , 1, 1, 'L');

$Y +=7;
$pdf->SetFont('Times', '', 10);
$pdf->SetXY(10, $Y);
$pdf->Cell(37, 7, 'Office' , 1, 1, 'C');
$pdf->SetXY(47, $Y);
$pdf->Cell(153, 7, $payee_office , 1, 1, 'L');

$Y +=7;
$pdf->SetXY(10, $Y);
$pdf->Cell(37, 7, 'Address' , 1, 1, 'C');
$pdf->SetXY(47, $Y);
$pdf->Cell(153, 7, $payee_address , 1, 1, 'L');

$Y+= 7;
$pdf->SetXY(10, $Y);
$pdf->Cell(37, 7, 'Responsibility Center' , 1, 1, 'C');

$pdf->SetXY(47, $Y);
$pdf->Cell(58, 7, 'Particulars' , 1, 1, 'C');

$pdf->SetXY(105, $Y);
$pdf->Cell(30, 7, 'MFO/PAP' , 1, 1, 'C');

$pdf->SetXY(135, $Y);
$pdf->Cell(30, 7, 'UACS Object Code' , 1, 1, 'C');

$pdf->SetXY(165, $Y);
$pdf->Cell(35, 7, 'Amount' , 1, 1, 'C');


//HD
$query = $this->db->query("
SELECT
    *
FROM
    `tbl_ors_hd`
WHERE
    `recid` = '$recid'
"
);
$data = $query->getRowArray();
$particulars = $data['particulars'];
$total_amount = 0;

$YP = 84;
$pdf->SetXY(47, $YP);
$pdf->MultiCell(58, 4, $particulars, 0, 'L'); // full width usage

//---------------------------------------------------------   DIRECT PS RC CODE, MFO PAPS FETCHING   --------------------------------------------------------------------
// $DPSY= 85;
// $DPSY_MFO = $DPSY;
// $rc_list_psd = [];
// $mfo_list_psd = [];
// $pdf->SetXY(10, $DPSY);
// $query = $this->db->query("
//     SELECT
//         `responsibility_code`,
//         `mfopaps_code`
//     FROM
//         `tbl_ors_direct_ps_dt`
//     WHERE
//         `project_id` = '$recid'
// ");

// $data = $query->getResultArray();

// foreach ($data as $row) {
//     $rc_list_psd[] = $row['responsibility_code'];
//     $mfo_list_psd[] = $row['mfopaps_code'];
// }

// // Get unique RCs only
// $unique_rc_list_psd = array_unique($rc_list_psd);
// $unique_mfo_list_psd = array_unique($mfo_list_psd);

// $previous_rc = null;
// foreach ($unique_rc_list_psd as $rc) {
//     $pdf->MultiCell(38, 4, $rc, 0, 'L'); // full width usage
    
//     // If current RC is not the same as previous, add 5 to y-coordinate
//     if ($rc !== $previous_rc) {
//         $DPSY += 5;
//     }
//     $previous_rc = $rc;
// }
// foreach ($unique_mfo_list_psd as $mfo) {
//     $pdf->SetXY(105, $DPSY_MFO);
//     $pdf->MultiCell(30, 4, $mfo, 0, 'L'); // full width usage
//     $DPSY_MFO = $pdf->GetY($DPSY_MFO);
// }


// //DT INDIRECT PS
// if($DPSY == 85){
//     $IDPSY = $DPSY+4;
//     $IDPSY_MFO = $IDPSY;
// }else{
//     $IDPSY = $pdf->GetY($DPSY)+4;
//     $IDPSY_MFO = $IDPSY;
// }

// $rc_list_psid = [];
// $mfo_list_psid = [];
// $pdf->SetXY(10, $DPSY);
// $query = $this->db->query("
//     SELECT
//         `responsibility_code`,
//         `mfopaps_code`
//     FROM
//         `tbl_ors_indirect_ps_dt`
//     WHERE
//         `project_id` = '$recid'
// ");

// $data = $query->getResultArray();

// foreach ($data as $row) {
//     $rc_list_psid[] = $row['responsibility_code'];
//     $mfo_list_psid[] = $row['mfopaps_code'];
// }

// // Get unique RCs only
// $unique_rc_list_psid = array_unique($rc_list_psid);
// $unique_mfo_list_psid = array_unique($mfo_list_psid);

// $previous_rc = null;
// foreach ($unique_rc_list_psid as $rc) {
//     $pdf->MultiCell(38, 4, $rc, 0, 'L'); // full width usage
    
//     // If current RC is not the same as previous, add 5 to y-coordinate
//     if ($rc !== $previous_rc) {
//         $IDPSY += 5;
//     }
//     $previous_rc = $rc;
// }
// foreach ($unique_mfo_list_psid as $mfo) {
//     $pdf->SetXY(105, $IDPSY_MFO);
//     $pdf->MultiCell(30, 4, $mfo, 0, 'L'); // full width usage
//     $IDPSY_MFO = $pdf->GetY($IDPSY_MFO);
// }

// //DT DIRECT MOOE
// if($IDPSY >= 85){
//     $DMOOEY = $IDPSY;
//     $DMOOEY_MFO = $DMOOEY;
// }else{
//     $DMOOEY = $pdf->GetY($IDPSY);
//     $DMOOEY_MFO = $DMOOEY;
// }


// $rc_list_mooed = [];
// $mfo_list_mooed = [];
// $pdf->SetXY(10, $DPSY);
// $query = $this->db->query("
//     SELECT
//         `responsibility_code`,
//         `mfopaps_code`
//     FROM
//         `tbl_ors_direct_mooe_dt`
//     WHERE
//         `project_id` = '$recid'
// ");

// $data = $query->getResultArray();

// foreach ($data as $row) {
//     $rc_list_mooed[] = $row['responsibility_code'];
//     $mfo_list_mooed[] = $row['mfopaps_code'];
// }

// // Get unique RCs only
// $unique_rc_list_mooed = array_unique($rc_list_mooed);
// $unique_mfo_list_mooed = array_unique($mfo_list_mooed);

// $previous_rc = null;
// foreach ($unique_rc_list_mooed as $rc) {
//     $pdf->SetXY(10, $DMOOEY);
//     $pdf->MultiCell(38, 4, $rc, 0, 'L'); // full width usage
    
//     // If current RC is not the same as previous, add 5 to y-coordinate
//     if ($rc !== $previous_rc) {
//         $DMOOEY += 5;
//     }
//     $previous_rc = $rc;
// }

// foreach ($unique_mfo_list_mooed as $mfo) {
//     $pdf->SetXY(105, $DMOOEY_MFO);
//     $pdf->MultiCell(30, 4, $mfo, 0, 'L'); // full width usage
//     $DMOOEY_MFO = $pdf->GetY($DMOOEY_MFO);
// }

// //DT INDIRECT MOOE
// if($DMOOEY >= 85){
//     $IDMOOEY = $DMOOEY;
//     $IDMOOEY_MFO = $IDMOOEY;
// }else{
//     $IDMOOEY = $pdf->GetY($DMOOEY);
//     $IDMOOEY_MFO = $IDMOOEY;
// }

// $rc_list_mooeid = [];
// $mfo_list_mooeid = [];
// $pdf->SetXY(10, $DPSY);
// $query = $this->db->query("
//     SELECT
//         `responsibility_code`,
//         `mfopaps_code`
//     FROM
//         `tbl_ors_indirect_mooe_dt`
//     WHERE
//         `project_id` = '$recid'
// ");

// $data = $query->getResultArray();

// foreach ($data as $row) {
//     $rc_list_mooeid[] = $row['responsibility_code'];
//     $mfo_list_mooeid[] = $row['mfopaps_code'];
// }

// // Get unique RCs only
// $unique_rc_list_mooeid = array_unique($rc_list_mooeid);
// $unique_mfo_list_mooeid = array_unique($mfo_list_mooeid);

// $previous_rc = null;
// foreach ($unique_rc_list_mooeid as $rc) {
//     $pdf->SetXY(10, $IDMOOEY);
//     $pdf->MultiCell(38, 4, $rc, 0, 'L'); // full width usage
    
//     // If current RC is not the same as previous, add 5 to y-coordinate
//     if ($rc !== $previous_rc) {
//         $IDMOOEY += 5;
//     }
//     $previous_rc = $rc;
// }

// foreach ($unique_mfo_list_mooeid as $mfo) {
//     $pdf->SetXY(105, $IDMOOEY_MFO);
//     $pdf->MultiCell(30, 4, $mfo, 0, 'L'); // full width usage
//     $IDMOOEY_MFO = $pdf->GetY($IDMOOEY_MFO);
// }


//-----------------------------------------------------  UACS CODE AND AMOUNT DT FETCHING    ----------------------------------------------------------------------------------

//DT DIRECT PS
$pdf->SetFont('Times', '', 10);

$DPS = 81;
$pdf->SetXY(10, $DPS);
$pdf->Cell(37, 3, '' , 'LR', 1, 'C');

$pdf->SetXY(47, $DPS);
$pdf->Cell(58, 3, '' , 'R', 1, 'C');

$pdf->SetXY(105, $DPS);
$pdf->Cell(30, 3, '' , 'R', 1, 'C');

$pdf->SetXY(135, $DPS);
$pdf->Cell(30, 3, '' , 'R', 1, 'C');

$pdf->SetXY(165, $DPS);
$pdf->Cell(35, 3, '' , 'R', 1, 'C');

$query = $this->db->query("
SELECT
    `recid`,
    `project_id`,
    `project_title`,
    `responsibility_code`,
    `mfopaps_code`,
    `sub_object_code`,
    `uacs_code`,
    `amount`,
    `added_at`,
    `added_by`
FROM
    `tbl_ors_direct_ps_dt`
WHERE
    `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {

    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code        = $row['mfopaps_code'];
    $uacs_code           = $row['uacs_code'];
    $amount              = $row['amount'];

    // Save current Y position
    $startY = $DPS +3;

    // Add new page when reaching the bottom
    if ($startY > 250) {
        $pdf->AddPage();
        $DPS = 20;
        $startY = $DPS;
    }

    // ==========================================
    // Responsibility Center (can wrap)
    // ==========================================
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(37, 4, $responsibility_code, 'LR', 'C');

    // Height used by the MultiCell
    $endY = $pdf->GetY();
    $rowHeight = $endY - $startY;

    // Minimum row height
    if ($rowHeight < 4) {
        $rowHeight = 4;
    }

    // MFO/PAP Border
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, $rowHeight, '', 'LR', 0);

    // MFO/PAP Text
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, 4, $mfopaps_code, 0, 0, 'C');


    // UACS Border
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, $rowHeight, '', 'R', 0);

    // UACS Text
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, 4, $uacs_code, 0, 0, 'C');


    // Amount Border
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, $rowHeight, '', 0, 0);

    // Amount Text
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, 4, number_format($amount, 2), 0, 0, 'R');

    // LAST CELL Border
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, $rowHeight, '', 'R', 0);

    // LAST CELL Text
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, 4, '', 0, 0, 'R');

    $total_amount += $amount;

    // Move to the next row
    $DPS += $rowHeight;
}

//DT INDIRECT PS
$pdf->SetFont('Times', '', 10);

if ($DPS >= 85) {
    $IDPS = $DPS;
} else {
    $IDPS = $pdf->GetY();
}

$query = $this->db->query("
SELECT
    `recid`,
    `project_id`,
    `project_title`,
    `responsibility_code`,
    `mfopaps_code`,
    `sub_object_code`,
    `uacs_code`,
    `amount`,
    `added_at`,
    `added_by`
FROM
    `tbl_ors_indirect_ps_dt`
WHERE
    `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {

    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code        = $row['mfopaps_code'];
    $uacs_code           = $row['uacs_code'];
    $amount              = $row['amount'];

    // Save current Y position
    $startY = $IDPS;

    // Add new page when reaching the bottom
    if ($startY > 250) {
        $pdf->AddPage();
        $IDPS = 20;
        $startY = $IDPS;
    }

    // ==========================================
    // Responsibility Center (can wrap)
    // ==========================================
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(37, 4, $responsibility_code, 'LR', 'C');

    // Height used by the MultiCell
    $endY = $pdf->GetY();
    $rowHeight = $endY - $startY;

    // Minimum row height
    if ($rowHeight < 4) {
        $rowHeight = 4;
    }

    // MFO/PAP Border
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, $rowHeight, '', 'LR', 0);

    // MFO/PAP Text
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, 4, $mfopaps_code, 0, 0, 'C');


    // UACS Border
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, $rowHeight, '', 'R', 0);

    // UACS Text
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, 4, $uacs_code, 0, 0, 'C');


    // Amount Border
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, $rowHeight, '', 0, 0);

    // Amount Text
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, 4, number_format($amount, 2), 0, 0, 'R');

    // LAST CELL Border
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, $rowHeight, '', 'R', 0);

    // LAST CELL Text
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, 4, '', 0, 0, 'R');

    $total_amount += $amount;

    // Move to the next row
    $IDPS += $rowHeight;
}

//MOEE ENTRY
 $pdf->SetFont('Times', '', 10);
//DT DIRECT MOOE
if ($IDPS >= 85) {
    $DMOOE = $IDPS;
} else {
    $DMOOE = $pdf->GetY();
}

$query = $this->db->query("
SELECT
    `recid`,
    `project_id`,
    `project_title`,
    `responsibility_code`,
    `mfopaps_code`,
    `sub_object_code`,
    `uacs_code`,
    `amount`,
    `added_at`,
    `added_by`
FROM
    `tbl_ors_direct_mooe_dt`
WHERE
    `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {

    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code        = $row['mfopaps_code'];
    $uacs_code           = $row['uacs_code'];
    $amount              = $row['amount'];

    // Save current Y position
    $startY = $DMOOE;

    if ($startY > 220) {
        $pdf->SetXY(10, $DMOOE);
        $pdf->Cell(37, 0, '', 'T', 0);
        $pdf->Cell(58, 0, '', 'T', 0);
        $pdf->Cell(30, 0, '', 'T', 0);
        $pdf->Cell(30, 0, '', 'T', 0);
        $pdf->Cell(27, 0, '', 'T', 0);
        $pdf->Cell(8, 0, '', 'T', 0);
        $pdf->AddPage();
        $DMOOE = 20;
        
        $pdf->SetXY(10, $DMOOE);
        $pdf->Cell(37, 7, 'Responsibility Center', 1, 0, 'C');

        $pdf->Cell(58, 7, 'Particulars', 1, 0, 'C');

        $pdf->Cell(30, 7, 'MFO/PAP', 1, 0, 'C');

        $pdf->Cell(30, 7, 'UACS Object Code', 1, 0, 'C');

        $pdf->Cell(35, 7, 'Amount', 1, 1, 'C');
        $DMOOE += 7;
        $startY = $DMOOE;
    }
    // ==========================================
    // Responsibility Center (can wrap)
    // ==========================================
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(37, 4, $responsibility_code, 'LR', 'C');

    // Height used by the MultiCell
    $endY = $pdf->GetY();
    $rowHeight = $endY - $startY;

    // Minimum row height
    if ($rowHeight < 4) {
        $rowHeight = 4;
    }

    // MFO/PAP Border
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, $rowHeight, '', 'LR', 0);

    // MFO/PAP Text
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, 4, $mfopaps_code, 0, 0, 'C');


    // UACS Border
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, $rowHeight, '', 'R', 0);

    // UACS Text
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, 4, $uacs_code, 0, 0, 'C');

    // Amount Border
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, $rowHeight, '', 0, 0);

    // Amount Text
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, 4, number_format($amount, 2), 0, 0, 'R');

    // LAST CELL Border
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, $rowHeight, '', 'R', 0);

    // LAST CELL Text
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, 4, '', 0, 0, 'R');

    $total_amount += $amount;

    // Move to the next row
    $DMOOE += $rowHeight;
}

//DT INDIRECT MOOE
$pdf->SetFont('Times', '', 10);

if ($DMOOE >= 85) {
    $IDMOOE = $DMOOE;
} else {
    $IDMOOE = $pdf->GetY();
}

$query = $this->db->query("
SELECT
    `recid`,
    `project_id`,
    `project_title`,
    `responsibility_code`,
    `mfopaps_code`,
    `sub_object_code`,
    `uacs_code`,
    `amount`,
    `added_at`,
    `added_by`
FROM
    `tbl_ors_indirect_mooe_dt`
WHERE
    `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {

    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code        = $row['mfopaps_code'];
    $uacs_code           = $row['uacs_code'];
    $amount              = $row['amount'];

    // Save current Y position
    $startY = $IDMOOE;

    // Add new page when reaching the bottom
    if ($startY > 250) {
        $pdf->AddPage();
        $IDMOOE = 20;
        $startY = $IDMOOE;
    }

    // ==========================================
    // Responsibility Center (can wrap)
    // ==========================================
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(37, 4, $responsibility_code, 'LR', 'C');

    // Height used by the MultiCell
    $endY = $pdf->GetY();
    $rowHeight = $endY - $startY;

    // Minimum row height
    if ($rowHeight < 4) {
        $rowHeight = 4;
    }

    // MFO/PAP Border
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, $rowHeight, '', 'LR', 0);

    // MFO/PAP Text
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, 4, $mfopaps_code, 0, 0, 'C');


    // UACS Border
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, $rowHeight, '', 'R', 0);

    // UACS Text
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, 4, $uacs_code, 0, 0, 'C');


    // Amount Border
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, $rowHeight, '', 0, 0);

    // Amount Text
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, 4, number_format($amount, 2), 0, 0, 'R');

    // LAST CELL Border
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, $rowHeight, '', 'R', 0);

    // LAST CELL Text
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, 4, '', 0, 0, 'R');

    $total_amount += $amount;

    // Move to the next row
    $IDMOOE += $rowHeight;
}

//DT DIRECT CO
$pdf->SetFont('Times', '', 10);

if ($IDMOOE >= 85) {
    $DCO = $IDMOOE;
} else {
    $DCO = $pdf->GetY();
}

$query = $this->db->query("
SELECT
    `recid`,
    `project_id`,
    `project_title`,
    `responsibility_code`,
    `mfopaps_code`,
    `sub_object_code`,
    `uacs_code`,
    `amount`,
    `added_at`,
    `added_by`
FROM
    `tbl_ors_direct_co_dt`
WHERE
    `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {

    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code        = $row['mfopaps_code'];
    $uacs_code           = $row['uacs_code'];
    $amount              = $row['amount'];

    // Save current Y position
    $startY = $DCO;

    // Add new page when reaching the bottom
    if ($startY > 250) {
        $pdf->AddPage();
        $DCO = 20;
        $startY = $DCO;
    }

    // ==========================================
    // Responsibility Center (can wrap)
    // ==========================================
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(37, 4, $responsibility_code, 'LR', 'C');

    // Height used by the MultiCell
    $endY = $pdf->GetY();
    $rowHeight = $endY - $startY;

    // Minimum row height
    if ($rowHeight < 4) {
        $rowHeight = 4;
    }

    // MFO/PAP Border
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, $rowHeight, '', 'LR', 0);

    // MFO/PAP Text
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, 4, $mfopaps_code, 0, 0, 'C');


    // UACS Border
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, $rowHeight, '', 'R', 0);

    // UACS Text
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, 4, $uacs_code, 0, 0, 'C');


    // Amount Border
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, $rowHeight, '', 0, 0);

    // Amount Text
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, 4, number_format($amount, 2), 0, 0, 'R');

    // LAST CELL Border
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, $rowHeight, '', 'R', 0);

    // LAST CELL Text
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, 4, '', 0, 0, 'R');

    $total_amount += $amount;

    // Move to the next row
    $DCO += $rowHeight;
}

//DT INDIRECT CO
$pdf->SetFont('Times', '', 10);

if ($DCO >= 85) {
    $IDCO = $DCO;
} else {
    $IDCO = $pdf->GetY();
}

$query = $this->db->query("
SELECT
    `recid`,
    `project_id`,
    `project_title`,
    `responsibility_code`,
    `mfopaps_code`,
    `sub_object_code`,
    `uacs_code`,
    `amount`,
    `added_at`,
    `added_by`
FROM
    `tbl_ors_indirect_co_dt`
WHERE
    `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {

    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code        = $row['mfopaps_code'];
    $uacs_code           = $row['uacs_code'];
    $amount              = $row['amount'];

    // Save current Y position
    $startY = $IDCO;

    // Add new page when reaching the bottom
    if ($startY > 250) {
        $pdf->AddPage();
        $IDCO = 20;
        $startY = $IDCO;
    }

    // ==========================================
    // Responsibility Center (can wrap)
    // ==========================================
    $pdf->SetXY(10, $startY);
    $pdf->MultiCell(37, 4, $responsibility_code, 'LR', 'C');

    // Height used by the MultiCell
    $endY = $pdf->GetY();
    $rowHeight = $endY - $startY;

    // Minimum row height
    if ($rowHeight < 4) {
        $rowHeight = 4;
    }

    // MFO/PAP Border
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, $rowHeight, '', 'LR', 0);

    // MFO/PAP Text
    $pdf->SetXY(105, $startY);
    $pdf->Cell(30, 4, $mfopaps_code, 0, 0, 'C');


    // UACS Border
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, $rowHeight, '', 'R', 0);

    // UACS Text
    $pdf->SetXY(135, $startY);
    $pdf->Cell(30, 4, $uacs_code, 0, 0, 'C');


    // Amount Border
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, $rowHeight, '', 0, 0);

    // Amount Text
    $pdf->SetXY(165, $startY);
    $pdf->Cell(27, 4, number_format($amount, 2), 0, 0, 'R');

    // LAST CELL Border
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, $rowHeight, '', 'R', 0);

    // LAST CELL Text
    $pdf->SetXY(192, $startY);
    $pdf->Cell(8, 4, '', 0, 0, 'R');
    $total_amount += $amount;

    // Move to the next row
    $IDCO += $rowHeight;
}

$currentY = $IDCO;

// Fill empty rows only on first page
if ($pdf->PageNo() == 1) {

    while ($currentY < 185) {

        $pdf->SetXY(10, $currentY);
        $pdf->Cell(37, 5, '', 'L', 0, 'C');
        $pdf->Cell(58, 5, '', 'L', 0, 'C');
        $pdf->Cell(30, 5, '', 'L', 0, 'L');
        $pdf->Cell(30, 5, '', 'L', 0, 'C');
        $pdf->Cell(27, 5, '', 'L', 0, 'R');
        $pdf->Cell(8, 5, '', 'R', 1, 'R');

        $currentY += 5;
    }

    $currentYY = $currentY;
}else{
    $currentYY = $currentY;
}

$Y = $currentYY;

// ==============================
// TOP BORDER
// ==============================

$pdf->SetXY(10, $Y);
$pdf->Cell(37, 4, '' , 'LR', 0);

$pdf->SetXY(45, $Y);
$pdf->Cell(60, 4, '', 'R', 0);

$pdf->SetXY(105, $Y);
$pdf->Cell(30, 4, '', 'R', 0);

$pdf->SetXY(135, $Y);
$pdf->Cell(30, 4, '', 'R', 0);

$pdf->SetXY(165, $Y);
$pdf->Cell(27, 4, '', 'T', 0);

$pdf->SetXY(192, $Y);
$pdf->Cell(8, 4, '', 'TR', 0);


// ==============================
// TOTAL ROW
// ==============================

$Y += 4;

$pdf->SetXY(10, $Y);
$pdf->Cell(37, 4, '', 'LBR', 0);

$pdf->SetXY(45, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(60, 4, 'Total', 'BR', 0, 'R');

$pdf->SetXY(105, $Y);
$pdf->SetFont('Times', '', 10);
$pdf->Cell(30, 4, '', 'BR', 0);

$pdf->SetXY(135, $Y);
$pdf->Cell(30, 4, '', 'BR', 0);

$pdf->SetXY(165, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(27, 4, number_format($total_amount, 2), 'B', 0, 'R');

$pdf->SetXY(192, $Y);
$pdf->SetFont('Times', '', 10);
$pdf->Cell(8, 4, '', 'BR', 0);


//CERT BOX A AND B
$Y = $pdf->GetY()+4;
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(7.5, 6, 'A.' , 'LRB', 1, 'L');

$pdf->SetXY(25, $Y);
$pdf->SetFont('Times', 'B', 9);
$pdf->Cell(15, 6, 'Certified:' , 0, 1, 'L');

$pdf->SetXY(40, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(65, 6, 'Charges to appropriation/allotment are' , 0, 1, 'L');

$pdf->SetXY(105, $Y);
$pdf->SetFont('Times', 'B', 9);
$pdf->Cell(7.5, 6, 'B.' , 1, 1, 'L');

$pdf->SetXY(120, $Y);
$pdf->SetFont('Times', 'B', 9);
$pdf->Cell(15, 6, 'Certified:' , 0, 1, 'L');

$pdf->SetXY(135, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(65, 6, 'Allotment available and obligated for' , 'R', 1, 'L');
//2nd row
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(15, 5, '' , 'L', 1, 'L');

$pdf->SetXY(25, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(80, 5, 'necessary, lawful and under my direct' , 0, 1, 'L');

$pdf->SetXY(105, $Y);
$pdf->SetFont('Times', 'B', 9);
$pdf->Cell(15, 5, '' , 'L', 1, 'L');

$pdf->SetXY(120, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(80, 5, 'the purpose/adjustment necessary as indicated' , 'R', 1, 'L');

//3rd row
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(15, 5, '' , 'L', 1, 'L');

$pdf->SetXY(25, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(80, 5, 'supervision;and supporting documents valid, proper' , 0, 1, 'L');

$pdf->SetXY(105, $Y);
$pdf->SetFont('Times', 'B', 9);
$pdf->Cell(15, 5, '' , 'L', 1, 'L');

$pdf->SetXY(120, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(80, 5, 'above' , 'R', 1, 'L');

//4th row
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', '', 10);
$pdf->Cell(95, 5, '' , 'LR', 1, 'L');
$pdf->SetXY(105, $Y);
$pdf->SetFont('Times', '', 10);
$pdf->Cell(95, 5, '' , 'LR', 1, 'L');

//signature row
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(25, 5, 'Signature' , 'L', 1, 'L');
$pdf->SetXY(35, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(3, 5, ':' , 0, 1, 'L');
$pdf->SetXY(40, $Y);
$pdf->SetFont('Times', 'B', 11);
$pdf->Cell(58, 5, '' , 'B', 1, 'C');
$pdf->SetXY(98, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(7, 5, '' , 'R', 1, 'L');

$pdf->SetXY(105, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(25, 5, 'Signature' , 'L', 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(3, 5, ':' , 0, 1, 'L');
$pdf->SetXY(135, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(58, 5, '' , 'B', 1, 'L');
$pdf->SetXY(193, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(7, 5, '' , 'R', 1, 'L');

//2nd row
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(25, 5, 'Printed Name' , 'L', 1, 'L');
$pdf->SetXY(35, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(3, 5, ':' , 0, 1, 'L');
$pdf->SetXY(40, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(58, 5, $certified_a , 'B', 1, 'C');
$pdf->SetXY(98, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(7, 5, '' , 'R', 1, 'L');

$pdf->SetXY(105, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(25, 5, 'Printed Name' , 'L', 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(3, 5, ':' , 0, 1, 'L');
$pdf->SetXY(135, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(58, 5, $certified_b , 'B', 1, 'C');
$pdf->SetXY(193, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(7, 5, '' , 'R', 1, 'L');

//position
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(25, 5, 'Position' , 'L', 1, 'L');
$pdf->SetXY(35, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(3, 5, ':' , 0, 1, 'L');
$pdf->SetXY(40, $Y);
$pdf->SetFont('Times', '', 8);
$pdf->Cell(58, 5, $position_a . ', ' . $division_a , 'B', 1, 'C');
$pdf->SetXY(98, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(7, 5, '' , 'R', 1, 'L');

$pdf->SetXY(105, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(25, 5, 'Position' , 'L', 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(3, 5, ':' , 0, 1, 'L');
$pdf->SetXY(135, $Y);
$pdf->SetFont('Times', '', 8);
$pdf->Cell(58, 5, $position_b , 'B', 1, 'C');
$pdf->SetXY(193, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(7, 5, '' , 'R', 1, 'L');

//HEAD
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(25, 5, 'Position' , 'L', 1, 'L');
$pdf->SetXY(35, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(3, 5, '' , 0, 1, 'L');
$pdf->SetXY(40, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(58, 5, 'Head, Requesting Office/Authorized Rep.' , 0, 1, 'C');
$pdf->SetXY(98, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(7, 5, '' , 'R', 1, 'L');

$pdf->SetXY(105, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(25, 5, '' , 'L', 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(3, 5, '' , 0, 1, 'L');
$pdf->SetXY(135, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(58, 5, 'Head, Budget Division/Unit/Authorized Rep.' , 0, 1, 'C');
$pdf->SetXY(193, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(7, 5, '' , 'R', 1, 'L');

//DATE
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(25, 5, 'Date' , 'L', 1, 'L');
$pdf->SetXY(35, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(3, 5, ':' , 0, 1, 'L');
$pdf->SetXY(40, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(58, 5, '' , 'B', 1, 'L');
$pdf->SetXY(98, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(7, 5, '' , 'R', 1, 'L');

$pdf->SetXY(105, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(25, 5, 'Date' , 'L', 1, 'L');
$pdf->SetXY(130, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(3, 5, ':' , 0, 1, 'L');
$pdf->SetXY(135, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(58, 5, '' , 'B', 1, 'L');
$pdf->SetXY(193, $Y);
$pdf->SetFont('Times', '', 9);
$pdf->Cell(7, 5, '' , 'R', 1, 'L');

//last row
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', '', 10);
$pdf->Cell(95, 3, '' , 'LRB', 1, 'L');
$pdf->SetXY(105, $Y);
$pdf->SetFont('Times', '', 10);
$pdf->Cell(95, 3, '' , 'LRB', 1, 'L');

//STATUS OF OBGLIGATION
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(7.5, 5, 'C.' , 1, 1, 'L');
$pdf->SetFont('Times', '', 10);

$pdf->SetXY(17.5, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(182.5, 5, 'STATUS OF OBLIGATION' , 1, 1, 'C');
$pdf->SetFont('Times', '', 10);

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Times', 'B', 10);
$pdf->Cell(75, 3, 'Reference' , 1, 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(115, 3, 'Amount' , 1, 1, 'C');
$pdf->SetFont('Times', '', 10);
//ROW 1
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3, '' , 'RL', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(30, 3, '' , 'R', 1, 'C');

$pdf->SetXY(55, $Y);
$pdf->Cell(30, 3, '' , 'R', 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(22, 3, '' , 'R', 1, 'C');

$pdf->SetXY(107, $Y);
$pdf->Cell(22, 3, '' , 'R', 1, 'C');

$pdf->SetXY(129, $Y);
$pdf->Cell(22, 3, '' , 'R', 1, 'C');

$pdf->SetXY(151, $Y);
$pdf->Cell(49, 3, 'Balance' , 'RB', 1, 'C');

//ROW 2
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3, 'Date' , 'LR', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(30, 3, 'Particulars' , 'R', 1, 'C');

$pdf->SetXY(55, $Y);
$pdf->Cell(30, 3, 'ORS/JEV/Check/' , 'R', 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(22, 3, 'Obligation' , 'R', 1, 'C');

$pdf->SetXY(107, $Y);
$pdf->Cell(22, 3, 'Payable' , 'R', 1, 'C');

$pdf->SetXY(129, $Y);
$pdf->Cell(22, 3, 'Payment' , 'R', 1, 'C');

$pdf->SetXY(151, $Y);
$pdf->Cell(15, 3, 'Not Yet' , 'R', 1, 'C');

$pdf->SetXY(166, $Y);
$pdf->Cell(34, 3, 'Due and' , 'R', 1, 'C');

//ROW 3
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3, '' , 'LR', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(30, 3, '' , 'R', 1, 'C');

$pdf->SetXY(55, $Y);
$pdf->Cell(30, 3, 'ADA/TRA No.' , 'R', 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(22, 3, '' , 'R', 1, 'C');

$pdf->SetXY(107, $Y);
$pdf->Cell(22, 3, '' , 'R', 1, 'C');

$pdf->SetXY(129, $Y);
$pdf->Cell(22, 3, '' , 'R', 1, 'C');

$pdf->SetXY(151, $Y);
$pdf->Cell(15, 3, 'Due' , 'R', 1, 'C');

$pdf->SetXY(166, $Y);
$pdf->Cell(34, 3, 'Demandable' , 'RB', 1, 'C');

//ROW 4
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 3, '' , 'RL', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(30, 3, '' , 'R', 1, 'C');

$pdf->SetXY(55, $Y);
$pdf->Cell(30, 3, '' , 'R', 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(22, 3, '(a)' , 1, 1, 'C');

$pdf->SetXY(107, $Y);
$pdf->Cell(22, 3, '(b)' , 1, 1, 'C');

$pdf->SetXY(129, $Y);
$pdf->Cell(22, 3, '(c)' , 1, 1, 'C');

$pdf->SetXY(151, $Y);
$pdf->Cell(15, 3, '(a-b)' , 1, 1, 'C');

$pdf->SetXY(166, $Y);
$pdf->Cell(34, 3, '(b-c)' , 1, 1, 'C');

//ROW 4
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 5, '' , 1, 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(30, 5, '' , 1, 1, 'C');

$pdf->SetXY(55, $Y);
$pdf->Cell(30, 5, '' , 1, 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(22, 5, '' , 1, 1, 'C');

$pdf->SetXY(107, $Y);
$pdf->Cell(22, 5, '' , 1, 1, 'C');

$pdf->SetXY(129, $Y);
$pdf->Cell(22, 5, '' , 1, 1, 'C');

$pdf->SetXY(151, $Y);
$pdf->Cell(15, 5, '' , 1, 1, 'C');

$pdf->SetXY(166, $Y);
$pdf->Cell(34, 5, '' , 1, 1, 'C');

$pdf->Output();
exit;
?>