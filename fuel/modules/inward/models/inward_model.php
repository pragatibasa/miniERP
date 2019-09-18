<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH.'helpers/tcpdf/config/lang/eng.php');
require_once(APPPATH.'helpers/tcpdf/tcpdf.php');

class inward_model extends Base_module_model {

	public $required = array('nPartyName','vIRnumber', 'dReceivedDate', 'vLorryNo', 'vInvoiceNo', 'dInvoiceDate', 'nMatId', 'fWidth', 'fThickness', 'fQuantity');

	protected $key_field = 'dReceivedDate';
	function __construct(){
        parent::__construct('aspen_tblinwardentry');
    }

	function example(){
		return true;
	}

	function list_pnamelists($pname){
		$query  = $this->db->query("SELECT nPartyName FROM aspen_tblpartydetails WHERE nPartyName LIKE '$pname%' LIMIT 10");
		//echo $query;
		$arr = $query->result();
		if(!empty($arr)){
		echo '<ul>';
		foreach($arr as $row) {
	         echo '<li onClick="fill(\''.addslashes($row->nPartyName).'\');">'.$row->nPartyName.'</li>';
	    }
		echo '</ul>';
		}else{
			echo '<ul>';
			echo '<li>No Record</li>';
			echo '</ul>';
		}
	}

	function checkcoilno($REQUEST) {
		if($REQUEST){
		$pid = $REQUEST["pid"];
		//$child_name = $_REQUEST["child_name"];
		}
		$checkdata = "select * from aspen_tblinwardentry where vIRnumber = '".$pid."'  LIMIT 0,1";
		$checkquery = $this->db->query($checkdata);
		if ($checkquery->num_rows() == 0)
		{
			echo '0';
		}else{
			echo '1';
		}
	}

	function getParentCoilDetails($coilNumber) {
		$checkdata = "select * from aspen_tblinwardentry where vIRnumber = '".$coilNumber."'";
		$checkquery = $this->db->query($checkdata);
		return $checkquery->result()[0];
	}

	function getParentBundleDetails($coilNumber,$bundleNumber) {
		$checkdata = "select * from aspen_tblslittinginstruction where vIRnumber = '".$coilNumber."' and nSno = $bundleNumber";
		$checkquery = $this->db->query($checkdata);
		return $checkquery->result()[0];
	}

	function inwardbillgeneratemodel($pname='',$pid='') {
	$sqlinward = "select aspen_tblpartydetails.nPartyName as partyname ,aspen_tblinwardentry.vIRnumber as coilnumber, DATE_FORMAT(dReceivedDate, '%d-%m-%Y')  as receiveddate ,aspen_tblmatdescription.vDescription as matdescription, aspen_tblinwardentry.fThickness as thickness, aspen_tblinwardentry.fWidth as width,aspen_tblinwardentry.fQuantity as Weight, aspen_tblinwardentry.vLorryNo AS Lorryno,aspen_tblinwardentry.vInvoiceNo as invoiceno, DATE_FORMAT(dInvoiceDate, '%d-%m-%Y') as invoicedate,aspen_tblinwardentry.vStatus as status from aspen_tblinwardentry  left join aspen_tblpartydetails on aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId  where aspen_tblinwardentry.vIRnumber ='".$pid."'";
		$querymain = $this->db->query($sqlinward);

		//$invoice = 'CoilNumber_'.$pid;
		$party_name = $querymain->row(0)->partyname;
		$coil_number = $querymain->row(0)->coilnumber;
		$received_date = $querymain->row(0)->receiveddate;
		$mat_description = $querymain->row(0)->matdescription;
		$thickness = $querymain->row(0)->thickness;
		$width = $querymain->row(0)->width;
		$Weight = $querymain->row(0)->Weight;
		$Lorryno = $querymain->row(0)->Lorryno;
		$invoicedate = $querymain->row(0)->invoicedate;
		$invoiceno = $querymain->row(0)->invoiceno;

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdfname= 'inwardslip_'.$pid.'.pdf';
		$resolution= array(72, 150);
		//$pdf->AddPage('P', $resolution);
		$pdf->SetAuthor('Abhilash');
		$pdf->SetTitle('Inwardslip');
		$pdf->SetSubject('Inwardslip');
		$pdf->SetKeywords('ASPEN, ERP, Inwardslip');
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);
		$pdf->SetFont('helvetica', '', 3);
		$pdf->AddPage('P', $resolution);

		$html = '
		<table align="center" width="100%" cellspacing="0" cellpadding="5"  border="0.1">
			<tr>
				<td align="center">
					<h1><b>INWARD SLIP</b></h1>
				</td>
			</tr>	
				<tr>
				<td ></td>
				
		</tr>
			<tr>
				<td align="left">
				<h2><b>Party Name: </b> '.$party_name.'</h2></td>
				
			</tr><tr><td></td></tr>
			<tr>
				<td align="left">
				<h2><b>Coil Number: </b> '.$coil_number.'</h2></td>
			</tr>			
			<tr><td></td></tr>
			<tr>
				<td align="left">
				<h2><b>Received Date: </b> '.$received_date.'</h2></td>
			</tr><tr><td></td></tr>
			<tr>
				<td align="left">
				<h2><b>Invoice Number: </b> '.$invoiceno.'</h2></td>
			</tr><tr><td></td></tr>
			<tr>
				<td align="left">
				<h2><b>Invoice Date: </b> '.$invoicedate.'</h2></td>
			</tr><tr><td></td></tr>
			<tr>
				<td align="left">
					<h2><b>Material Description: </b> '.$mat_description.'</h2></td>
			</tr><tr><td></td></tr>
			<tr>
				<td align="left">
					<h2><b>Lorry Number: </b> '.$Lorryno.'</h2></td>
			</tr>
<tr><td></td></tr>
			<tr>
				<td align="left">
					<h2><b>Thickness(mm) :</b> '.$thickness.'</h2></td>
			</tr><tr><td></td></tr>
			<tr>
				<td align="left">
					<h2><b>Width(mm) :</b> '.$width.'</h2></td>
			</tr><tr><td></td></tr>	
			<tr>
				<td align="left">
					<h2><b>Weight(Kgs) :</b> '.$Weight.'</h2></td>
			</tr>	
		</table>';
		$pdf->writeHTML($html, true, 0, true, true);
		$pdf->Ln();
		$pdf->lastPage();
		$pdf->Output($pdfname, 'I');
	}


	function saveinwardentry($pid,$pname, $date3,$lno,$icno,$date4,$coil,$fWidth,
							$fThickness,$fLength,$fQuantity,$status,$hno,$pna,$ppartyId,$parentBundleNumber,$grade,$cast)
	{


		$updateSql = '';
		if(!empty($ppartyId)) {
			$ppartyId = $ppartyId;
			$updateSql = "update aspen_tblinwardentry set fpresent = ( fpresent - $fQuantity )  where vIRnumber ='". $ppartyId . "'";
			$query = $this->db->query($updateSql);
		} else {
			$ppartyId = "NULL";
		}

		if(empty($parentBundleNumber)) {
			$parentBundleNumber = "NULL";
		}

		$sql = "Insert into aspen_tblinwardentry (
		nPartyId,vIRnumber,dReceivedDate,dBillDate,vLorryNo,vInvoiceNo,dInvoiceDate,nMatId,fWidth,fThickness,fLength,fQuantity,vStatus,
		vHeatnumber,vPlantname,fpresent,billedweight,dSysDate,vprocess,vParentIRNumber,vParentBundleNumber,vGrade,vCast) 
		VALUES((SELECT aspen_tblpartydetails.nPartyId FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),  '". $pid. "','". $date3. "', CURDATE(),'". $lno. "','". $icno. "','". $date4. "',(SELECT aspen_tblmatdescription.nMatId  FROM aspen_tblmatdescription where aspen_tblmatdescription.vDescription = '". $coil. "'),'". $fWidth. "','". $fThickness. "','". $fLength. "','". $fQuantity. "','". $status. "','". $hno. "','". $pna. "','". $fQuantity. "',0,now(),'','".$ppartyId."','".$parentBundleNumber."','".$grade."','".$cast."' )";

		$sql1 = "Insert into aspen_hist_tblinwardentry (
		nPartyId,vIRnumber,dReceivedDate,dBillDate,vLorryNo,vInvoiceNo,dInvoiceDate,nMatId,fWidth,fThickness,fLength,fQuantity,vStatus,
		vHeatnumber,vPlantname,fpresent,billedweight,dSysDate) 
		VALUES((SELECT aspen_tblpartydetails.nPartyId  FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),  '". $pid. "','". $date3. "', CURDATE(),'". $lno. "','". $icno. "','". $date4. "',(SELECT aspen_tblmatdescription.nMatId  FROM aspen_tblmatdescription where aspen_tblmatdescription.vDescription = '". $coil. "'),'". $fWidth. "','". $fThickness. "','". $fLength. "','". $fQuantity. "','". $status. "','". $hno. "','". $pna. "','". $fQuantity. "',0,now())";

		$query = $this->db->query($sql);
		//$query1 = $this->db->query($sql1);

		$strSql = "select * from aspen_tblpartydetails where nPartyName = '". $pname. "'";
		$query = $this->db->query($strSql);

		if($query->result()[0]->nInwardUpdates) {
			sendSMS($query->result()[0]->nInwardUpdates,'Received Coil No '.$pid.'%n'.$coil.' '.$fThickness.'mm x '.$fWidth.'mm '.$fQuantity.'kgs%nOn '.date('d/m/Y').'%nVehicle no '.$lno.'%nRef:'.$icno);
		}
        if($query->result()[0]->vemailaddress) {
            $strEmailHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                            <title>Inward Received</title>
                            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                            <meta name="viewport" content="width=device-width, initial-scale=1.0 " />
                            <style>
                            </style>
                            </head>';

            $strEmailHtml .= '<h4>Dear Customer,</h4>';
            $strEmailHtml .= '<h4>The following info is for your  perusal:</h4>';
            $strEmailHtml .= '<table style="width:80%; border-collapse: collapse;" cellpadding="5">
                            <tr>
                                <td style="border: 1px solid black;">Coil Number</td>
                                <td style="border: 1px solid black;">'.$pid.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Material Description</td>
                                <td style="border: 1px solid black;">'.$coil.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Thickness</td>
                                <td style="border: 1px solid black;">'.$fThickness.' mm</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Width</td>
                                <td style="border: 1px solid black;">'.$fWidth.' mm</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Quantity</td>
                                <td style="border: 1px solid black;">'.$fQuantity.' kgs</td>
                            </tr>                            <tr>
                                <td style="border: 1px solid black;">Received Date</td>
                                <td style="border: 1px solid black;">'.date('d/m/Y').'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Vehicle no</td>
                                <td style="border: 1px solid black;">'.$lno.'</td>
                            </tr>' .
                '               <tr>
                                <td style="border: 1px solid black;">Invoice Number</td>
                                <td style="border: 1px solid black;">'.$icno.'</td>
                            </tr>
                          </table>';
            $strEmailHtml .= '<p>For Aspen Steel Pvt ltd</p>
                          <p>Please contact our unit coordinator for any clarification.</p>
                          <p>Customer Service team<br/>
                          Unit 2 (Bidadi)<br/>
                          8217766390/7008898426</p>';

            $strEmailHtml .= '<p style="color:#999999;">This is a system generated mail. Please reply to aspen.bidadi@gmail.com for more details.</p>';

            sendEmail($query->result()[0]->vemailaddress, 'New coil received for '.$pname, $strEmailHtml);
        }
	}

	function mat() {
		$sql = "select * from aspen_tblmatdescription";
		$query = $this->db->query($sql);
		$arr='';
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}


	function party() {
		$sql = "select nPartyName from aspen_tblpartydetails";
		$query = $this->db->query($sql);
		$arr='';
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function getNextCoilNumber() {
		$strSql = 'select COALESCE(max(vIRnumber),1) as max_coil_number from aspen_tblinwardentry;';
		$query = $this->db->query($strSql);
		return $query->result()[0];
	}

    function exportInwardData() {
	    $strSql = "SELECT 
                        ai.*, ap.*, am.*
                    FROM
                        aspen_tblinwardentry ai
                            LEFT JOIN
                        aspen_tblpartydetails ap ON ap.nPartyId = ai.nPartyId
                            LEFT JOIN
                        aspen_tblmatdescription am ON am.nMatId = ai.nMatId";

        return $this->db->query($strSql);
    }
}

class inwardmodel extends Base_module_record {

}
