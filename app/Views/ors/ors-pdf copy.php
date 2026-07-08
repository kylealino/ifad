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
$program_title = $data['program_title'];
$particulars = $data['particulars'];
$funding_source = $data['funding_source'];
$payee_name = $data['payee_name'];
$payee_office = $data['payee_office'];
$payee_address = $data['payee_address'];
$certified_a = $data['certified_a'];
$position_a = $data['position_a'];
$certified_b = $data['certified_b'];
$position_b = $data['position_b'];
    
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle('ORS Print');
$pdf->SetFont('Arial', 'B', 16);

$pdf->SetXY(0, 8);

$Y = 8;
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'FAD-BS-001' , 'TRL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, 'Revision 0' , 'RL', 1, 'L');

$Y = $pdf->GetY();
$pdf->SetXY(170, $Y);
$pdf->Cell(30, 4, $formattedDate , 'BRL', 1, 'L');

$Y = $pdf->GetY()+4;
$pdf->SetXY(170, $Y);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(30, 4, 'Appendix 11' , 0, 1, 'R');


$Y = 32;
$pdf->SetXY(10, $Y);
$pdf->SetFont('Arial', 'B', 10);

$pdf->Cell(120, 2, '' , 'TRL', 1, 'C');
$Y = 2;
$pdf->Cell(120, 5, 'OBLIGATION REQUEST AND STATUS' , 'RL', 1, 'C');

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
$pdf->Cell(50, 5, '01' . '-' .  $funding_source . '-' . $currentDate , 'R', 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(40, 5, '' , 'B', 1, 'L');
$Y += 5;
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 5, 'Date:' , 0, 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, $formattedDate , 'R', 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(40, 5, '' , 'B', 1, 'L');
$Y += 5;
$pdf->SetXY(130, $Y);
$pdf->Cell(20, 5, 'Fund Cluster:' , 0, 1, 'L');
$pdf->SetXY(150, $Y);
$pdf->Cell(50, 5, '01' , 'R', 1, 'L');
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
$pdf->Cell(37, 88.5, 'Responsibility Center' , 1, 1, 'C');

$pdf->SetXY(47, $Y);
$pdf->Cell(58, 88.5, 'Particulars' , 1, 1, 'C');

$pdf->SetXY(105, $Y);
$pdf->Cell(30, 88.5, 'MFO/PAP' , 1, 1, 'C');

$pdf->SetXY(135, $Y);
$pdf->Cell(30, 88.5, 'UACS Object Code' , 1, 1, 'C');

$pdf->SetXY(165, $Y);
$pdf->Cell(35, 88.5, 'Amount' , 1, 1, 'C');

$DTY = 85;
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

//DT
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
"
);
$data = $query->getResultArray();
$last_responsibility_code = '';
foreach ($data as $row) {
    $responsibility_code = $row['responsibility_code'];
    $mfopaps_code = $row['mfopaps_code'];
    $uacs_code = $row['uacs_code'];
    $amount = $row['amount'];
    
    if ($responsibility_code !== $last_responsibility_code && $responsibility_code !== null) {
        $pdf->SetXY(10, $DTY);
        $pdf->Cell(37, 4, $responsibility_code , 1, 1, 'C');
        $last_responsibility_code = $responsibility_code;
    }

    $pdf->SetXY(105, $DTY);
    $pdf->Cell(30, 4, $mfopaps_code , 1, 1, 'C');

    $pdf->SetXY(135, $DTY);
    $pdf->Cell(30, 4, $uacs_code , 1, 1, 'C');

    $pdf->SetXY(165, $DTY);
    $pdf->Cell(25, 4, number_format($amount,2), 1, 1, 'R');
    $total_amount += $amount;
    $DTY += 4;
}

$Y += 2;
$pdf->SetXY(47, $Y);
$pdf->MultiCell(58, 4, $particulars, 1, 'L'); // full width usage

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
$pdf->Cell(35, 3, number_format($total_amount, 2) , 0, 1, 'C');


$Y = 171.5;

//CERTIFY BORDERING
$pdf->SetXY(10, $Y);
$pdf->Cell(95, 60, '' , 1, 1, 'C'); //Certify A

$CY = 171.5;
$pdf->SetXY(10, $CY);
$pdf->Cell(15, 10, 'A.' , 1, 1, 'L');

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
$pdf->Cell(65, 4, '' , 'B', 1, 'L');

$CY = $pdf->GetY($CY) +4;

$pdf->SetXY(10, $CY);
$pdf->Cell(15, 4, 'Position' , 0, 1, 'L');
$pdf->SetXY(30, $CY);
$pdf->Cell(4, 4, ':' , 0, 1, 'L');
$pdf->SetXY(35, $CY);
$pdf->Cell(65, 4, '' , 'B', 1, 'L');

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
$pdf->Cell(15, 10, 'B.' , 1, 1, 'L');

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
$pdf->Cell(65, 4, '' , 'B', 1, 'L');

$CY2 = $pdf->GetY($CY2) +4;

$pdf->SetXY(105, $CY2);
$pdf->Cell(15, 4, 'Position' , 0, 1, 'L');
$pdf->SetXY(125, $CY2);
$pdf->Cell(4, 4, ':' , 0, 1, 'L');
$pdf->SetXY(130, $CY2);
$pdf->Cell(65, 4, '' , 'B', 1, 'L');

$CY2 = $pdf->GetY($CY2);
$pdf->SetXY(105, $CY2);
$pdf->SetXY(130, $CY2);
$pdf->Cell(65, 4, 'Head, Requesting Office/Authorized' , 0, 1, 'C');

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
$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(15, 5, 'C.' , 1, 1, 'L');

$pdf->SetXY(25, $Y);
$pdf->Cell(175, 5, 'STATUS OF OBLIGATION' , 1, 1, 'C');

$Y = $pdf->GetY();
$pdf->SetXY(10, $Y);
$pdf->Cell(75, 5, 'Reference' , 1, 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(115, 5, 'Amount' , 1, 1, 'C');

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
$pdf->Cell(30, 5, 'ORS/JEV/Check/' , 'R', 1, 'C');

$pdf->SetXY(85, $Y);
$pdf->Cell(22, 5, 'Obligation' , 'R', 1, 'C');

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
$pdf->Cell(30, 5, 'ADA/TRA No.' , 'R', 1, 'C');

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