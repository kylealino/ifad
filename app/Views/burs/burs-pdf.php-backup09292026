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
    `tbl_burs_hd`
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
$burs_date = $data['burs_date'];

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
$pdf->SetTitle('BURS Print');
$pdf->SetFont('Arial', 'B', 16);

$pdf->SetXY(0, 8);

$Y = 8;
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'FAD-BS-002' , 'TRL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'Revision 0' , 'RL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'June 10 2024' , 'BRL', 1, 'L');

$Y = $pdf->GetY()+4;
$pdf->SetXY(170, $Y);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(30, 4, 'Appendix 14' , 0, 1, 'R');


$Y = 32;
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 10);

$pdf->Cell(120, 2, '' , 'TRL', 1, 'C');
$Y = 2;
$pdf->Cell(120, 5, 'BUDGET UTILIZATION REQUEST AND STATUS' , 'RL', 1, 'C');

$Y += 5;
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(120, 1, '' , 'RL', 1, 'C');

$Y += 1;
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(120, 8, 'FOOD AND NUTRITION RESEARCH INSTITUTE' , 'RL', 1, 'C');

$Y = 32;
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(120, 5, 'Entity Name' , 'RL', 1, 'C');

$Y = 32;

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 2, '' , 'T', 1, 'C');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 2, '' , 'TR', 1, 'L');
$Y += 2;
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 5, 'Serial No.:' ,0, 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, $serialno , 'R', 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(40, 5, '' , 'B', 1, 'L');
$Y += 5;
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 5, 'Date:' , 0, 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, '' , 'R', 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(40, 5, $burs_date , 'B', 1, 'L');
$Y += 5;
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 5, 'Fund Cluster:' , 0, 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, '07' , 'R', 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(40, 5, '' , 'B', 1, 'L');
$Y += 5;
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 4, '' , 'L', 1, 'C');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 4, '' , 'R', 1, 'C');

$pdf->SetFont('Arial', '', 8);
$Y +=4;
$pdf->SetXY(10, $Y);
$pdf->Cell(37, 7, 'Payee' , 1, 1, 'C');
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(47, $Y);
$pdf->Cell(153, 7, $payee_name , 1, 1, 'L');

$Y +=7;
$pdf->SetFont('Arial', '', 8);
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
$pdf->Cell(37, 9, 'Responsibility Center' , 1, 1, 'C');

$pdf->SetXY(47, $Y);
$pdf->Cell(58, 9, 'Particulars' , 1, 1, 'C');

$pdf->SetXY(105, $Y);
$pdf->Cell(30, 9, 'MFO/PAP' , 1, 1, 'C');

$pdf->SetXY(135, $Y);
$pdf->Cell(30, 9, 'UACS Object Code' , 1, 1, 'C');

$pdf->SetXY(165, $Y);
$pdf->Cell(35, 9, 'Amount' , 1, 1, 'C');

//DT BORDERING
$Y+= 9;
$pdf->SetXY(10, $Y);
$pdf->Cell(37, 88.5, '' , 1, 1, 'C');//Responsibility Center

$pdf->SetXY(47, $Y);
$pdf->Cell(58, 88.5, '' , 1, 1, 'C');//Particulars

$pdf->SetXY(105, $Y);
$pdf->Cell(30, 88.5, '' , 1, 1, 'C');//MFO/PAP

$pdf->SetXY(135, $Y);
$pdf->Cell(30, 88.5, '' , 1, 1, 'C');//UACS Object Code

$pdf->SetXY(165, $Y);
$pdf->Cell(35, 88.5, '' , 1, 1, 'C');//Amount


//HD
$query = $this->db->query("
SELECT
    *
FROM
    `tbl_burs_hd`
WHERE
    `recid` = '$recid'
"
);
$data = $query->getRowArray();
$particulars = $data['particulars'];
$total_amount = 0;

//---------------------------------------------------------   DIRECT PS RC CODE, MFO PAPS FETCHING   --------------------------------------------------------------------
$DPSY= 85;
$DPSY_MFO = $DPSY;
$rc_list_psd = [];
$mfo_list_psd = [];
$pdf->SetXY(10, $DPSY);
$query = $this->db->query("
    SELECT
        `responsibility_code`,
        `mfopaps_code`
    FROM
        `tbl_burs_direct_ps_dt`
    WHERE
        `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {
    $rc_list_psd[] = $row['responsibility_code'];
    $mfo_list_psd[] = $row['mfopaps_code'];
}

// Get unique RCs only
$unique_rc_list_psd = array_unique($rc_list_psd);
$unique_mfo_list_psd = array_unique($mfo_list_psd);

$previous_rc = null;
foreach ($unique_rc_list_psd as $rc) {
    $pdf->MultiCell(38, 4, $rc, 0, 'L'); // full width usage
    
    // If current RC is not the same as previous, add 5 to y-coordinate
    if ($rc !== $previous_rc) {
        $DPSY += 5;
    }
    $previous_rc = $rc;
}
foreach ($unique_mfo_list_psd as $mfo) {
    $pdf->SetXY(105, $DPSY_MFO);
    $pdf->MultiCell(30, 4, $mfo, 0, 'L'); // full width usage
    $DPSY_MFO = $pdf->GetY($DPSY_MFO);
}


//DT INDIRECT PS
if($DPSY == 85){
    $IDPSY = $DPSY;
    $IDPSY_MFO = $IDPSY;
}else{
    $IDPSY = $pdf->GetY($DPSY);
    $IDPSY_MFO = $IDPSY;
}

$rc_list_psid = [];
$mfo_list_psid = [];
$pdf->SetXY(10, $DPSY);
$query = $this->db->query("
    SELECT
        `responsibility_code`,
        `mfopaps_code`
    FROM
        `tbl_burs_indirect_ps_dt`
    WHERE
        `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {
    $rc_list_psid[] = $row['responsibility_code'];
    $mfo_list_psid[] = $row['mfopaps_code'];
}

// Get unique RCs only
$unique_rc_list_psid = array_unique($rc_list_psid);
$unique_mfo_list_psid = array_unique($mfo_list_psid);

$previous_rc = null;
foreach ($unique_rc_list_psid as $rc) {
    $pdf->MultiCell(38, 4, $rc, 0, 'L'); // full width usage
    
    // If current RC is not the same as previous, add 5 to y-coordinate
    if ($rc !== $previous_rc) {
        $IDPSY += 5;
    }
    $previous_rc = $rc;
}
foreach ($unique_mfo_list_psid as $mfo) {
    $pdf->SetXY(105, $IDPSY_MFO);
    $pdf->MultiCell(30, 4, $mfo, 0, 'L'); // full width usage
    $IDPSY_MFO = $pdf->GetY($IDPSY_MFO);
}

//DT DIRECT MOOE
if($IDPSY >= 85){
    $DMOOEY = $IDPSY;
    $DMOOEY_MFO = $DMOOEY;
}else{
    $DMOOEY = $pdf->GetY($IDPSY);
    $DMOOEY_MFO = $DMOOEY;
}


$rc_list_mooed = [];
$mfo_list_mooed = [];
$pdf->SetXY(10, $DPSY);
$query = $this->db->query("
    SELECT
        `responsibility_code`,
        `mfopaps_code`
    FROM
        `tbl_burs_direct_mooe_dt`
    WHERE
        `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {
    $rc_list_mooed[] = $row['responsibility_code'];
    $mfo_list_mooed[] = $row['mfopaps_code'];
}

// Get unique RCs only
$unique_rc_list_mooed = array_unique($rc_list_mooed);
$unique_mfo_list_mooed = array_unique($mfo_list_mooed);

$previous_rc = null;
foreach ($unique_rc_list_mooed as $rc) {
    $pdf->SetXY(10, $DMOOEY);
    $pdf->MultiCell(38, 4, $rc, 0, 'L'); // full width usage
    
    // If current RC is not the same as previous, add 5 to y-coordinate
    if ($rc !== $previous_rc) {
        $DMOOEY += 5;
    }
    $previous_rc = $rc;
}

foreach ($unique_mfo_list_mooed as $mfo) {
    $pdf->SetXY(105, $DMOOEY_MFO);
    $pdf->MultiCell(30, 4, $mfo, 0, 'L'); // full width usage
    $DMOOEY_MFO = $pdf->GetY($DMOOEY_MFO);
}

//DT INDIRECT MOOE
if($DMOOEY >= 85){
    $IDMOOEY = $DMOOEY;
    $IDMOOEY_MFO = $IDMOOEY;
}else{
    $IDMOOEY = $pdf->GetY($DMOOEY);
    $IDMOOEY_MFO = $IDMOOEY;
}

$rc_list_mooeid = [];
$mfo_list_mooeid = [];
$pdf->SetXY(10, $DPSY);
$query = $this->db->query("
    SELECT
        `responsibility_code`,
        `mfopaps_code`
    FROM
        `tbl_burs_indirect_mooe_dt`
    WHERE
        `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {
    $rc_list_mooeid[] = $row['responsibility_code'];
    $mfo_list_mooeid[] = $row['mfopaps_code'];
}

// Get unique RCs only
$unique_rc_list_mooeid = array_unique($rc_list_mooeid);
$unique_mfo_list_mooeid = array_unique($mfo_list_mooeid);

$previous_rc = null;
foreach ($unique_rc_list_mooeid as $rc) {
    $pdf->SetXY(10, $IDMOOEY);
    $pdf->MultiCell(38, 4, $rc, 0, 'L'); // full width usage
    
    // If current RC is not the same as previous, add 5 to y-coordinate
    if ($rc !== $previous_rc) {
        $IDMOOEY += 5;
    }
    $previous_rc = $rc;
}

foreach ($unique_mfo_list_mooeid as $mfo) {
    $pdf->SetXY(105, $IDMOOEY_MFO);
    $pdf->MultiCell(30, 4, $mfo, 0, 'L'); // full width usage
    $IDMOOEY_MFO = $pdf->GetY($IDMOOEY_MFO);
}

// --- DT DIRECT CO ---
if ($IDMOOEY >= 85) {
    $DCOY = $IDMOOEY;
    $DCOY_MFO = $DCOY;
} else {
    $DCOY = $pdf->GetY($IDMOOEY);
    $DCOY_MFO = $DCOY;
}

$rc_list_cod = [];
$mfo_list_cod = [];
$pdf->SetXY(10, $DCOY);
$query = $this->db->query("
    SELECT
        `responsibility_code`,
        `mfopaps_code`
    FROM
        `tbl_burs_direct_co_dt`
    WHERE
        `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {
    $rc_list_cod[] = $row['responsibility_code'];
    $mfo_list_cod[] = $row['mfopaps_code'];
}

$unique_rc_list_cod = array_unique($rc_list_cod);
$unique_mfo_list_cod = array_unique($mfo_list_cod);

$previous_rc = null;
foreach ($unique_rc_list_cod as $rc) {
    $pdf->SetXY(10, $DCOY);
    $pdf->MultiCell(38, 4, $rc, 0, 'L');

    if ($rc !== $previous_rc) {
        $DCOY += 5;
    }
    $previous_rc = $rc;
}

foreach ($unique_mfo_list_cod as $mfo) {
    $pdf->SetXY(105, $DCOY_MFO);
    $pdf->MultiCell(30, 4, $mfo, 0, 'L');
    $DCOY_MFO = $pdf->GetY($DCOY_MFO);
}

// --- DT INDIRECT CO ---
if ($DCOY >= 85) {
    $IDCOY = $DCOY;
    $IDCOY_MFO = $IDCOY;
} else {
    $IDCOY = $pdf->GetY($DCOY);
    $IDCOY_MFO = $IDCOY;
}

$rc_list_coid = [];
$mfo_list_coid = [];
$pdf->SetXY(10, $IDCOY);
$query = $this->db->query("
    SELECT
        `responsibility_code`,
        `mfopaps_code`
    FROM
        `tbl_burs_indirect_co_dt`
    WHERE
        `project_id` = '$recid'
");

$data = $query->getResultArray();

foreach ($data as $row) {
    $rc_list_coid[] = $row['responsibility_code'];
    $mfo_list_coid[] = $row['mfopaps_code'];
}

$unique_rc_list_coid = array_unique($rc_list_coid);
$unique_mfo_list_coid = array_unique($mfo_list_coid);

$previous_rc = null;
foreach ($unique_rc_list_coid as $rc) {
    $pdf->SetXY(10, $IDCOY);
    $pdf->MultiCell(38, 4, $rc, 0, 'L');

    if ($rc !== $previous_rc) {
        $IDCOY += 5;
    }
    $previous_rc = $rc;
}

foreach ($unique_mfo_list_coid as $mfo) {
    $pdf->SetXY(105, $IDCOY_MFO);
    $pdf->MultiCell(30, 4, $mfo, 0, 'L');
    $IDCOY_MFO = $pdf->GetY($IDCOY_MFO);
}


//-----------------------------------------------------  UACS CODE AND AMOUNT DT FETCHING    ----------------------------------------------------------------------------------

//DT DIRECT PS
$DPS = 85;
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
    `tbl_burs_direct_ps_dt`
WHERE
    `project_id` = '$recid'
"
);
$data = $query->getResultArray();
$last_responsibility_code = '';
foreach ($data as $row) {
    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code = $row['mfopaps_code'];
    $uacs_code = $row['uacs_code'];
    $amount = $row['amount'];
    
    // if ($responsibility_code !== $last_responsibility_code && $responsibility_code !== null) {
    //     $pdf->SetXY(10, $DPS);
    //     $pdf->MultiCell(38, 4, $responsibility_code, 0, 'L'); // full width usage
    //     $last_responsibility_code = $responsibility_code;
    // }

    // $pdf->SetXY(105, $DPS);
    // $pdf->Cell(30, 4, $mfopaps_code , 0, 1, 'C');

    $pdf->SetXY(135, $DPS);
    $pdf->Cell(30, 4, $uacs_code , 0, 1, 'C');

    $pdf->SetXY(165, $DPS);
    $pdf->Cell(25, 4, number_format($amount,2), 0, 1, 'R');
    $total_amount += $amount;
    $DPS = $pdf->GetY($DPS);
}

//DT INDIRECT PS
if($DPS == 85){
    $IDPS = $DPS;
}else{
    $IDPS = $pdf->GetY($DPS);
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
    `tbl_burs_indirect_ps_dt`
WHERE
    `project_id` = '$recid'
"
);
$data = $query->getResultArray();
$last_responsibility_code = '';
foreach ($data as $row) {
    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code = $row['mfopaps_code'];
    $uacs_code = $row['uacs_code'];
    $amount = $row['amount'];
    
    // if ($responsibility_code !== $last_responsibility_code && $responsibility_code !== null) {
    //     $pdf->SetXY(10, $IDPS);
    //     $pdf->MultiCell(37, 4, $responsibility_code, 0, 'L'); // full width usage
    //     $last_responsibility_code = $responsibility_code;
    // }

    // $pdf->SetXY(105, $IDPS);
    // $pdf->Cell(30, 4, $mfopaps_code , 0, 1, 'C');

    $pdf->SetXY(135, $IDPS);
    $pdf->Cell(30, 4, $uacs_code , 0, 1, 'C');

    $pdf->SetXY(165, $IDPS);
    $pdf->Cell(25, 4, number_format($amount,2), 0, 1, 'R');
    $total_amount += $amount;
    $IDPS = $pdf->GetY($IDPS);
}

//MOEE ENTRY

//DT DIRECT MOOE
if($IDPS >= 85){
    $DMOOE = $IDPS;
}else{
    $DMOOE = $pdf->GetY($IDPS);
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
    `tbl_burs_direct_mooe_dt`
WHERE
    `project_id` = '$recid'
"
);
$data = $query->getResultArray();
$last_responsibility_code = '';
foreach ($data as $row) {
    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code = $row['mfopaps_code'];
    $uacs_code = $row['uacs_code'];
    $amount = $row['amount'];
    
    // if ($responsibility_code !== $last_responsibility_code && $responsibility_code !== null) {
    //     $pdf->SetXY(10, $DMOOE);
    //     $pdf->MultiCell(37, 4, $responsibility_code, 0, 'L'); // full width usage
    //     $last_responsibility_code = $responsibility_code;
    // }

    // $pdf->SetXY(105, $DMOOE);
    // $pdf->Cell(30, 4, $mfopaps_code , 0, 1, 'C');

    $pdf->SetXY(135, $DMOOE);
    $pdf->Cell(30, 4, $uacs_code , 0, 1, 'C');

    $pdf->SetXY(165, $DMOOE);
    $pdf->Cell(25, 4, number_format($amount,2), 0, 1, 'R');
    $total_amount += $amount;
    $DMOOE = $pdf->GetY($DMOOE);
}

//DT INDIRECT MOOE
if($DMOOE >= 85){
    $IDMOOE = $DMOOE;
}else{
    $IDMOOE = $pdf->GetY($DMOOE);
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
    `tbl_burs_indirect_mooe_dt`
WHERE
    `project_id` = '$recid'
"
);
$data = $query->getResultArray();
$last_responsibility_code = '';
foreach ($data as $row) {
    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code = $row['mfopaps_code'];
    $uacs_code = $row['uacs_code'];
    $amount = $row['amount'];
    
    // if ($responsibility_code !== $last_responsibility_code && $responsibility_code !== null) {
    //     $pdf->SetXY(10, $IDMOOE);
    //     $pdf->MultiCell(37, 4, $responsibility_code, 0, 'L'); // full width usage
    //     $last_responsibility_code = $responsibility_code;
    // }

    // $pdf->SetXY(105, $IDMOOE);
    // $pdf->Cell(30, 4, $mfopaps_code , 0, 1, 'C');

    $pdf->SetXY(135, $IDMOOE);
    $pdf->Cell(30, 4, $uacs_code , 0, 1, 'C');

    $pdf->SetXY(165, $IDMOOE);
    $pdf->Cell(25, 4, number_format($amount,2), 0, 1, 'R');
    $total_amount += $amount;
    $IDMOOE = $pdf->GetY($IDMOOE);
}

//DT DIRECT CO
if($IDMOOE >= 85){
    $DCO = $IDMOOE;
}else{
    $DCO = $pdf->GetY($IDMOOE);
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
    `tbl_burs_direct_co_dt`
WHERE
    `project_id` = '$recid'
"
);
$data = $query->getResultArray();
$last_responsibility_code = '';
foreach ($data as $row) {
    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code = $row['mfopaps_code'];
    $uacs_code = $row['uacs_code'];
    $amount = $row['amount'];
    
    // if ($responsibility_code !== $last_responsibility_code && $responsibility_code !== null) {
    //     $pdf->SetXY(10, $DCO);
    //     $pdf->MultiCell(37, 4, $responsibility_code, 0, 'L'); // full width usage
    //     $last_responsibility_code = $responsibility_code;
    // }

    // $pdf->SetXY(105, $DCO);
    // $pdf->Cell(30, 4, $mfopaps_code , 0, 1, 'C');

    $pdf->SetXY(135, $DCO);
    $pdf->Cell(30, 4, $uacs_code , 0, 1, 'C');

    $pdf->SetXY(165, $DCO);
    $pdf->Cell(25, 4, number_format($amount,2), 0, 1, 'R');
    $total_amount += $amount;
    $DCO = $pdf->GetY($DCO);
}

//DT INDIRECT CO
if($DCO >= 85){
    $IDCO = $DCO;
}else{
    $IDCO = $pdf->GetY($DCO);
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
    `tbl_burs_indirect_co_dt`
WHERE
    `project_id` = '$recid'
"
);
$data = $query->getResultArray();
$last_responsibility_code = '';
foreach ($data as $row) {
    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code = $row['mfopaps_code'];
    $uacs_code = $row['uacs_code'];
    $amount = $row['amount'];
    
    // if ($responsibility_code !== $last_responsibility_code && $responsibility_code !== null) {
    //     $pdf->SetXY(10, $IDCO);
    //     $pdf->MultiCell(37, 4, $responsibility_code, 0, 'L'); // full width usage
    //     $last_responsibility_code = $responsibility_code;
    // }

    // $pdf->SetXY(105, $IDCO);
    // $pdf->Cell(30, 4, $mfopaps_code , 0, 1, 'C');

    $pdf->SetXY(135, $IDCO);
    $pdf->Cell(30, 4, $uacs_code , 0, 1, 'C');

    $pdf->SetXY(165, $IDCO);
    $pdf->Cell(25, 4, number_format($amount,2), 0, 1, 'R');
    $total_amount += $amount;
    $IDCO = $pdf->GetY($IDCO);
}


$Y += 2;
$pdf->SetXY(47, $Y);
$pdf->MultiCell(58, 4, $particulars, 0, 'L'); // full width usage

$Y = 168;
$pdf->SetXY(10, $Y);
$pdf->Cell(35, 3, '' , 0, 1, 'C');

$pdf->SetXY(45, $Y);
$pdf->Cell(60, 3, 'TOTAL' , 0, 1, 'R');

$pdf->SetXY(105, $Y);
$pdf->Cell(30, 3, '' , 0, 1, 'C');

$pdf->SetXY(135, $Y);
$pdf->Cell(30, 3, '' , 0, 1, 'C');

$pdf->SetXY(165, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 3, number_format($total_amount, 2) , 0, 1, 'C');
$pdf->SetFont('Arial', '', 8);


$Y = 171.5;

//CERTIFY BORDERING
$pdf->SetXY(10, $Y);
$pdf->Cell(95, 60, '' , 1, 1, 'C'); //Certify A

$CY = 171.5;
$pdf->SetXY(10, $CY);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 10, 'A.' , 1, 1, 'L');
$pdf->SetFont('Arial', '', 8);
$CY += 4;
$pdf->SetXY(27, $CY);
$pdf->MultiCell(75, 4, '     Certified: Charges to appropriate/allotment are necessary, lawful and under my direct supervision; and supporting documents valid, proper and legal.', 0, 'L'); // full width usage

$CY = $pdf->GetY($CY) +4;

$pdf->SetXY(10, $CY);
$pdf->Cell(15, 4, 'Signature' , 0, 1, 'L');
$pdf->SetXY(30, $CY);
$pdf->Cell(4, 4, ':' , 0, 1, 'L');
$pdf->SetXY(35, $CY);
$pdf->Cell(65, 4, '' , 'B', 1, 'L');

$CY = $pdf->GetY($CY) +4;

$pdf->SetXY(10, $CY);
$pdf->Cell(15, 4, 'Printed Name' , 0, 1, 'L');
$pdf->SetXY(30, $CY);
$pdf->Cell(4, 4, ':' , 0, 1, 'L');
$pdf->SetXY(35, $CY);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(65, 4, $certified_a , 'B', 1, 'C');
$pdf->SetFont('Arial', '', 8);
$CY = $pdf->GetY($CY) +4;

$pdf->SetXY(10, $CY);
$pdf->Cell(15, 4, 'Position' , 0, 1, 'L');
$pdf->SetXY(30, $CY);
$pdf->Cell(4, 4, ':' , 0, 1, 'L');
$pdf->SetXY(35, $CY);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(65, 4, $position_a . ', ' . $division_a , 'B', 1, 'C');
$pdf->SetFont('Arial', '', 8);
$CY = $pdf->GetY($CY);
$pdf->SetXY(10, $CY);
$pdf->SetXY(35, $CY);
$pdf->Cell(65, 4, 'Head, Requesting Office/Authorized' , 0, 1, 'C');

$CY = $pdf->GetY($CY);
$pdf->SetXY(10, $CY);
$pdf->SetXY(35, $CY);
$pdf->Cell(65, 4, 'Representative' , 0, 1, 'C');

$CY = $pdf->GetY($CY) +4;

$pdf->SetXY(10, $CY);
$pdf->Cell(15, 4, 'Date' , 0, 1, 'L');
$pdf->SetXY(30, $CY);
$pdf->Cell(4, 4, ':' , 0, 1, 'L');
$pdf->SetXY(35, $CY);
$pdf->Cell(65, 4, '' , 'B', 1, 'L');

$pdf->SetXY(105, $Y);
$pdf->Cell(95, 60, '' , 1, 1, 'C'); //Certify B

$CY2 = 171.5;
$pdf->SetXY(105, $CY2);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 10, 'B.' , 1, 1, 'L');
$pdf->SetFont('Arial', '', 8);
$CY2 += 4;
$pdf->SetXY(122, $CY2);
$pdf->MultiCell(65, 4, '     Certified: Allotment available and obligated for the purpose/adjustment necessary as indicated above.', 0, 'L'); // full width usage

$CY2 = $pdf->GetY($CY2) +4;

$pdf->SetXY(105, $CY2);
$pdf->Cell(15, 4, 'Signature' , 0, 1, 'L');
$pdf->SetXY(125, $CY2);
$pdf->Cell(4, 4, ':' , 0, 1, 'L');
$pdf->SetXY(130, $CY2);
$pdf->Cell(65, 4, '' , 'B', 1, 'L');

$CY2 = $pdf->GetY($CY2) +4;

$pdf->SetXY(105, $CY2);
$pdf->Cell(15, 4, 'Printed Name' , 0, 1, 'L');
$pdf->SetXY(125, $CY2);
$pdf->Cell(4, 4, ':' , 0, 1, 'L');
$pdf->SetXY(130, $CY2);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(65, 4, $certified_b , 'B', 1, 'C');
$pdf->SetFont('Arial', '', 8);

$CY2 = $pdf->GetY($CY2) +4;

$pdf->SetXY(105, $CY2);
$pdf->Cell(15, 4, 'Position' , 0, 1, 'L');
$pdf->SetXY(125, $CY2);
$pdf->Cell(4, 4, ':' , 0, 1, 'L');
$pdf->SetXY(130, $CY2);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(65, 4, $position_b . ', ' . $section_a , 'B', 1, 'C');
$pdf->SetFont('Arial', '', 8);

$CY2 = $pdf->GetY($CY2);
$pdf->SetXY(105, $CY2);
$pdf->SetXY(130, $CY2);
$pdf->Cell(65, 4, 'Head, Budget Division/Unit/Authorized' , 0, 1, 'C');

$CY2 = $pdf->GetY($CY2);
$pdf->SetXY(105, $CY2);
$pdf->SetXY(130, $CY2);
$pdf->Cell(65, 4, 'Representative' , 0, 1, 'C');

$CY2 = $pdf->GetY($CY2) +4;

$pdf->SetXY(105, $CY2);
$pdf->Cell(15, 4, 'Date' , 0, 1, 'L');
$pdf->SetXY(30, $CY2);
$pdf->Cell(4, 4, ':' , 0, 1, 'L');
$pdf->SetXY(130, $CY2);
$pdf->Cell(65, 4, '' , 'B', 1, 'L');

//STATUS OF OBGLIGATION
$Y = $pdf->GetY($CY2)+4;
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 5, 'C.' , 1, 1, 'L');
$pdf->SetFont('Arial', '', 8);

$pdf->SetXY(25, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(175, 5, 'STATUS OF OBLIGATION' , 1, 1, 'C');
$pdf->SetFont('Arial', '', 8);

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(75, 5, 'Reference' , 1, 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(115, 5, 'Amount' , 1, 1, 'C');
$pdf->SetFont('Arial', '', 8);
//ROW 1
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 5, '' , 'RL', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(30, 5, '' , 'R', 1, 'C');

$pdf->SetXY(55, $Y);
$pdf->Cell(30, 5, '' , 'R', 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(22, 5, '' , 'R', 1, 'C');

$pdf->SetXY(107, $Y);
$pdf->Cell(22, 5, '' , 'R', 1, 'C');

$pdf->SetXY(129, $Y);
$pdf->Cell(22, 5, '' , 'R', 1, 'C');

$pdf->SetXY(151, $Y);
$pdf->Cell(49, 5, 'Balance' , 'RB', 1, 'C');

//ROW 2
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 5, 'Date' , 'LR', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(30, 5, 'Particulars' , 'R', 1, 'C');

$pdf->SetXY(55, $Y);
$pdf->Cell(30, 5, 'BURS/JEV/RCI/' , 'R', 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(22, 5, 'Utilization' , 'R', 1, 'C');

$pdf->SetXY(107, $Y);
$pdf->Cell(22, 5, 'Payable' , 'R', 1, 'C');

$pdf->SetXY(129, $Y);
$pdf->Cell(22, 5, 'Payment' , 'R', 1, 'C');

$pdf->SetXY(151, $Y);
$pdf->Cell(15, 5, 'Not Yet' , 'R', 1, 'C');

$pdf->SetXY(166, $Y);
$pdf->Cell(34, 5, 'Due and' , 'R', 1, 'C');

//ROW 3
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 5, '' , 'LR', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(30, 5, '' , 'R', 1, 'C');

$pdf->SetXY(55, $Y);
$pdf->Cell(30, 5, 'RADAI/RTRAI No./' , 'R', 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(22, 5, '' , 'R', 1, 'C');

$pdf->SetXY(107, $Y);
$pdf->Cell(22, 5, '' , 'R', 1, 'C');

$pdf->SetXY(129, $Y);
$pdf->Cell(22, 5, '' , 'R', 1, 'C');

$pdf->SetXY(151, $Y);
$pdf->Cell(15, 5, 'Due' , 'R', 1, 'C');

$pdf->SetXY(166, $Y);
$pdf->Cell(34, 5, 'Demandable' , 'RB', 1, 'C');

//ROW 4
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 5, '' , 'RL', 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(30, 5, '' , 'R', 1, 'C');

$pdf->SetXY(55, $Y);
$pdf->Cell(30, 5, '' , 'R', 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(22, 5, '(a)' , 1, 1, 'C');

$pdf->SetXY(107, $Y);
$pdf->Cell(22, 5, '(b)' , 1, 1, 'C');

$pdf->SetXY(129, $Y);
$pdf->Cell(22, 5, '(c)' , 1, 1, 'C');

$pdf->SetXY(151, $Y);
$pdf->Cell(15, 5, '(a-b)' , 1, 1, 'C');

$pdf->SetXY(166, $Y);
$pdf->Cell(34, 5, '(b-c)' , 1, 1, 'C');

//ROW 4
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 15, '' , 1, 1, 'C');

$pdf->SetXY(25, $Y);
$pdf->Cell(30, 15, '' , 1, 1, 'C');

$pdf->SetXY(55, $Y);
$pdf->Cell(30, 15, '' , 1, 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(22, 15, '' , 1, 1, 'C');

$pdf->SetXY(107, $Y);
$pdf->Cell(22, 15, '' , 1, 1, 'C');

$pdf->SetXY(129, $Y);
$pdf->Cell(22, 15, '' , 1, 1, 'C');

$pdf->SetXY(151, $Y);
$pdf->Cell(15, 15, '' , 1, 1, 'C');

$pdf->SetXY(166, $Y);
$pdf->Cell(34, 15, '' , 1, 1, 'C');

$pdf->Output();
exit;
?>