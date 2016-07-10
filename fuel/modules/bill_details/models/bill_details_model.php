<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/base_module_model.php');
require_once(MODULES_PATH.'/billing/config/billing_constants.php');
require_once(APPPATH.'helpers/tcpdf/config/lang/eng.php');
require_once(APPPATH.'helpers/tcpdf/tcpdf.php');

class bill_details_model extends Base_module_model {
    function __construct()
    {
        parent::__construct('aspen_tblbilldetails');// table name
    }
	
	function example() {
		return true;
	}

	function list_items($limit = NULL, $offset = NULL, $col = 'dBillDate', $order = 'desc') {
		$this->db->select('aspen_tblbilldetails.nBillNo,aspen_tblbilldetails.dBillDate,aspen_tblpartydetails.nPartyName');
		$this->db->join('aspen_tblpartydetails', 'aspen_tblbilldetails.nPartyId = aspen_tblpartydetails.nPartyId', 'left');

	    $data = parent::list_items($limit, $offset, $col, $order);
        return $data;    	
	}

	function getLatestBillNumber() {
		$querymain = $this->db->query("select max(nBillNo) as latestBillNo from aspen_tblbilldetails");
		return $querymain->row(0)->latestBillNo;
	}	
	
	function generateDuplicateBill( $billNo='' ) {

		$sqlbilling= "select aspen_tblbilldetails.nBillNo as billnumber,
						DATE_FORMAT(aspen_tblbilldetails.dBillDate, '%d/%m/%Y') as billdate,
						aspen_tblpartydetails.nPartyName as partyname,
						aspen_tblpartydetails.nTinNumber as tinnmber,
						aspen_tblbilldetails.vOutLorryNo as trucknumber, 
						aspen_tblmatdescription.vDescription as materialdescription, 
						aspen_tblinwardentry.vInvoiceNo as invoiceno,
						DATE_FORMAT(aspen_tblinwardentry.dInvoiceDate, '%d/%m/%Y') as invoicedate ,
						aspen_tblinwardentry.fWidth as width,aspen_tblinwardentry.fThickness as thickness, 
						aspen_tblbilldetails.ntotalpcs as totalpcs,
						aspen_tblbilldetails.fTotalWeight as totalweight,
						round(aspen_tblbilldetails.fWeightAmount ) as weihtamount,
						aspen_tblbilldetails.ntotalamount as totalamount,
						aspen_tblbilldetails.nScrapSent as Scrapsent,
						round(aspen_tblbilldetails.ocwtamount) as wtamount,
						round(aspen_tblbilldetails.ocwidthamount) as widthamount,
						aspen_tblbilldetails.oclengthamount as lengthamount,
						round(aspen_tblbilldetails.fServiceTax) as servicetax,
						round(aspen_tblbilldetails.fEduTax) as edutax,
						aspen_tblbilldetails.fSHEduTax as shedutax,
						aspen_tblbilldetails.fGrantTotal as grandtotal,
						aspen_tblbilldetails.vAdditionalChargeType as additionalchargetype,
						round(aspen_tblbilldetails.fAmount) as amount,
						round(aspen_tblbilldetails.nsubtotal) as subtotal,
						aspen_tblbilldetails.grandtot_words as container,
						aspen_tblbilldetails.nServiceTaxPercent as serviceTaxPercent,
						aspen_tblbilldetails.tBillingAddress as BillingAddress,
						aspen_tblbilldetails.dFinalRate as rate,
						aspen_tblinwardentry.vIRnumber as partyid,
						aspen_tblbilldetails.vBillType as billType
					from aspen_tblbilldetails
					left join aspen_tblinwardentry on aspen_tblbilldetails.vIRnumber= aspen_tblinwardentry.vIRnumber 
					left join aspen_tblpartydetails on aspen_tblinwardentry.nPartyId = aspen_tblpartydetails.nPartyId
					LEFT JOIN aspen_tblmatdescription ON aspen_tblinwardentry.nMatId = aspen_tblmatdescription.nMatId 
					where  aspen_tblbilldetails.nBillNo='".$billNo."' ";

		$querymain = $this->db->query($sqlbilling);
		$billnumber = $querymain->row(0)->billnumber;
		if( 'Directbilling' === $querymain->row(0)->billType) {
			$this->generateDirectBillDuplicate($billnumber);
		} else if( 'Slitting' === $querymain->row(0)->billType ) { 
			$this->generateSlittingBillDuplicate($billnumber);
		}

		$billdate = $querymain->row(0)->billdate;
		$invoice =$querymain->row(0)->partyid;
		$party_name = $querymain->row(0)->partyname;
		$width = $querymain->row(0)->width;
		$thickness = $querymain->row(0)->thickness;
		$invoicedate = $querymain->row(0)->invoicedate;
		$invoiceno = $querymain->row(0)->invoiceno;
		$trucknumber = $querymain->row(0)->trucknumber;
		$material_descriptio = $querymain->row(0)->materialdescription;
		$additionalchargetype = $querymain->row(0)->additionalchargetype;
		$amount = $querymain->row(0)->amount;
		$rate = $querymain->row(0)->rate;
		$totalpcs = $querymain->row(0)->totalpcs;
		$totalweight = $querymain->row(0)->totalweight;
		$weihtamount = $querymain->row(0)->weihtamount;
		$totalamount = $querymain->row(0)->totalamount;
		$wtamount = $querymain->row(0)->wtamount;
		$widthamount = $querymain->row(0)->widthamount;
		$lengthamount = $querymain->row(0)->lengthamount;
		$servicetax = $querymain->row(0)->servicetax;
		$edutax = $querymain->row(0)->edutax;
		$shedutax = $querymain->row(0)->shedutax;
		$grandtotal = $querymain->row(0)->grandtotal;
		$subtotal = $querymain->row(0)->subtotal;
		$tin_number = $querymain->row(0)->tinnmber;
		$container = $querymain->row(0)->container;
		$serviceTaxPercent = $querymain->row(0)->serviceTaxPercent;
		$billingAddress = $querymain->row(0)->BillingAddress;
		$finalRate = $querymain->row(0)->rate;
		
		$sqlitem ="select 
					aspen_tblBillBundleAssociation.nBundleNumber as bundlenumber,
					aspen_tblcuttinginstruction.nLength as length,
					aspen_tblBillBundleAssociation.nNoOfPcs as noofpcs,
					aspen_tblbillingstatus.fWeight as wei,
					aspen_tblBillBundleAssociation.fbilledWeight as weight
					from aspen_tblBillBundleAssociation
					left join aspen_tblbilldetails on aspen_tblBillBundleAssociation.nBillNumber = aspen_tblbilldetails.nBillNo
					left join aspen_tblcuttinginstruction on aspen_tblbilldetails.vIRnumber=aspen_tblcuttinginstruction.vIRnumber and aspen_tblBillBundleAssociation.nBundleNumber = aspen_tblcuttinginstruction.nSno
					left join aspen_tblbillingstatus ON aspen_tblcuttinginstruction.vIRnumber=aspen_tblbillingstatus.vIRnumber and aspen_tblBillBundleAssociation.nBundleNumber = aspen_tblbillingstatus.nSno
					where aspen_tblbilldetails.nBillNo = $billNo
					order by aspen_tblBillBundleAssociation.nBundleNumber asc";
	
		$queryitem = $this->db->query($sqlitem);	
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdfname= 'bill_'.$invoice.'.pdf';
		$resolution= array(430, 240);
		$pdf->SetAuthor('Abhilash');
		$pdf->SetTitle('Bill');
		$pdf->SetSubject('Bill');
		$pdf->SetKeywords('Aspen, bill, Bill');
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 8);
		$pdf->AddPage();
		
	$html = '
		<table width="100%"  cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td align="center"><b>Duplicate Job Work / Delivery Challan</b></td>
			</tr>
			<tr>
				<td width="16%" align:"left"><h4>TIN:29730066589</h4></td>
				<td width="70%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>ASPEN STEEL PVT LTD</h1></td>
				<td width="25%" align:"right"><h4>Service Tax Regn. No: (BAS)/AABCA4807HST001</h4></td>
			</tr>
			<tr>
				<td align="center" width="100%"><h4>Branch At: Plot no 16E, Bidadi Industrial Area, Phase 2 Sector 1, Bidadi, Ramnagara-562105, <b>Email: aspensteel_unit2@yahoo.com </b></h4></td>
			</tr>
			<tr>
				<td align="center" width="100%"><h4>Head Office At: 54/1, Medahalli, Old Madras Road, Bangalore-560049</h4></td>
			</tr>
		</table>
		<table>
			<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=3 width=100></td>
			</tr>
			<tr>
				<td width="30%" align:"left"><h3>Duplicate of Billnumber : '.$billnumber.'</h3></td>
				<td width="51%" align="center"><h3>Coilnumber : '.$invoice.'</h3></td>
				<td width="33.33%" align:"right"><h3>Billdate : '.$billdate.'</h3></td>		
			</tr>
			<tr><td></td></tr>
			<tr>
				<td width="30%" align:"left">
					<h3>'.$billingAddress.'</h3>
				</td>
				<td width="40%" align="center"><h3> Desp. By Lorry No. : '.$trucknumber.'</h3> </td>	
				<td width="33.33%" align:"right"><h3>Delivery: Full &nbsp; Part-1&nbsp; Part-2</h3></td>
			</tr>
			<tr><td></td></tr>
			<tr>
				<td width="30%" align:"left">
					<h3>Tin Number : '.$tin_number.'</h3>
				</td>
				<td width="39%" align="center"><h3> Inward Date : 	<b> '.$invoicedate.'</b></h3> </td>
				<td width="33.33%" align:"right"><h3>Inward Challan No.:'.$invoiceno.'</h3></td>
			</tr>
		</table>';

		$html .= '
		<hr color=#00CC33 size=5 width=100>
		<table cellspacing="0" cellpadding="3" border="0px" width="100%">
		<tr>
			<th style="font-weight:bold;" width="13%"><h3>Sl. No.</h3></th>
			<th style="font-weight:bold"  width="22%"><h3>Description</h3></th>
			<th style="font-weight:bold"  width="16.6%"><h3>No. Of Pcs</h3></th> 
			<th style="font-weight:bold"  width="16.6%"><h3>Qty. In M/T</h3></th> 
			<th style="font-weight:bold"  width="16.6%"><h3>Rate per M/T</h3></th>
			<th style="font-weight:bold"  width="15.6%"><h3>Amount</h3></th>		
		</tr>
		<tr>
			<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>
		</tr>	
		<tr>
			<td width="100px" align="left"><h3>'.$material_descriptio.'</h3></td>
			<td width="40px" align="left"><h3>'.$thickness.'</h3></td>		
			<td width="20px" align="right">*</td>	
			<td width="70px" align="right"><h3>'.$width.'</h3></td>		
			<td width="240px" align="right"><h3>'.$weihtamount.'</h3></td>
		</tr>';

		if ($queryitem->num_rows() > 0)
		{
			foreach($queryitem->result() as $rowitem)
			{
	$html .= '
			<tr>
				<td style="font-weight:bold;" width="13%"><h2>'.$rowitem->bundlenumber.'</h2></td>
				<td style="font-weight:bold" width="25%"><h2>LENGTH&nbsp;&nbsp;&nbsp;'.$rowitem->length.'</h2></td> 
				<td style="font-weight:bold" width="16.6%"><h2>'.$rowitem->noofpcs.'</h2></td> 
				<td style="font-weight:bold" width="33%"><h2>'.round($rowitem->weight,3).'</h2></td>
				<td style="font-weight:bold" width="15.6%"><h2></h2></td>	
			</tr>';
			}
		}		
	$html .= '
			
		</table>';	
		
	$html .= '
		<hr color=#00CC33 size=5 width=100>
		<table width="100%" cellspacing="5" cellpadding="5" border="0">
			
		<tr><td align="left"> <h3>Processing / Handling charges of coils</h3></td></tr>
			<tr>

				<td style="font-weight:bold;" width="13%"><h3>Total</h3></td>
				<td style="font-weight:bold"  width="23%"></td>
				<td style="font-weight:bold"  width="16.6%"><h3>'.$totalpcs.'</h3></td> 
				<td style="font-weight:bold" width="33%"><h3>'.round($totalweight,3).'</h3></td> 
				<td style="font-weight:bold"  width="15.6%"><h3>'.$totalamount.'</h3></td>				
			</tr>
		<tr>
		<td width="89%">
			<h3><b>Other Charges:&nbsp;'.$additionalchargetype.'</b></h3>
			</td> <td><h3>'.$amount.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
			<h3><b>For weight</b></h3>
			</td> <td><h3>'.$wtamount.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
			<h3><b>For width</b></h3>
			</td> <td><h3>'.$widthamount.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
			<h3><b>For length </b></h3>
			</td> <td><h3>'.$lengthamount.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
			<h3><b>SUBTOTAL</b></h3>
			</td> <td><h3>'.$subtotal.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
			<h3><b>Service Tax @ '.$serviceTaxPercent.'%</b></h3>
			</td> <td><h3>'.ceil($servicetax).'</h3></td>
		</tr>
		<tr>
			<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>	
		</tr>
		<tr>
		<td width="89%">
			<h3><b>Grand Total</b></h3>
			</td> <td><h3>'.$grandtotal.'</h3></td>
		</tr>
		<tr>
			<td width="25%">
				<h3>Grand Total in Words :</h3>
			</td>
			<td width="75%"><h3>'.$container.'</h3></td>
		</tr>
		<tr>
			<td width="70%">
				<h3><b>Received the above goods in good condition.</b></h3>
			</td> 
			<td width="30%"><h3> For ASPEN STEEL (P) LTD.</h3></td>
		</tr>
		<tr>
			<td></td> 
		</tr>
		<tr>
			<td width="70%">
				<h3><b>Receivers Signature</b></h3>
			</td> 
			<td width="30%"><h3> Manager/Director</h3></td>
		</tr>
		</table>';

	$pdf->writeHTML($html, true, 0, true, true);
	$pdf->Ln();
		$pdf->lastPage();
		$pdf->Output($pdfname, 'I');

	}

	function generateDirectBillDuplicate( $billNo ) {

		$sqlrpt = "select aspen_tblbilldetails.vOutLorryNo as lorryno,
					 aspen_tblbilldetails.fTotalWeight as totalweight,
					 aspen_tblbilldetails.ntotalpcs as totalpcs,
					 aspen_tblbilldetails.ntotalamount as totamount, 
					aspen_tblpartydetails.nPartyName as pname, 
					aspen_tblmatdescription.vDescription as description, 
					aspen_tblinwardentry.fWidth as wid,
					 aspen_tblinwardentry.fThickness as thic, 
					aspen_tblinwardentry.vIRnumber as coilno,
					aspen_tblbilldetails.dBillDate as billdate,
					aspen_tblinwardentry.dReceivedDate as inwarddate,
					aspen_tblpartydetails.nTinNumber as tin_number,
					aspen_tblbilldetails.nServiceTaxPercent as serviceTaxPercent,
						aspen_tblbilldetails.tBillingAddress as BillingAddress,
						aspen_tblbilldetails.dFinalRate as rate,
						aspen_tblinwardentry.vIRnumber as partyid,
						aspen_tblinwardentry.vInvoiceNo as invoiceno,
						aspen_tblbilldetails.vAdditionalChargeType,
						aspen_tblbilldetails.fAmount as txtamount_mt,
						aspen_tblbilldetails.nsubtotal as nsubtotal,
						aspen_tblbilldetails.fServiceTax as ServiceTax,
						aspen_tblbilldetails.fGrantTotal as GrantTotal,
						aspen_tblbilldetails.fGrantTotal as container
					from aspen_tblbilldetails 
					left join aspen_tblinwardentry on aspen_tblbilldetails.vIRnumber = aspen_tblinwardentry.vIRnumber
					LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId=aspen_tblinwardentry.nPartyId 
					LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId 
					where aspen_tblbilldetails.nBillNo=$billNo";

		$querymain = $this->db->query($sqlrpt);
		
		$inwarddate = date('d-m-Y',strtotime($querymain->row(0)->inwarddate));
		$tin_number = $querymain->row(0)->tin_number;
		$billdate = date( 'd-m-Y',strtotime($querymain->row(0)->billdate));
		$serviceTaxPercent = $querymain->row(0)->serviceTaxPercent;
		$billingAddress = $querymain->row(0)->BillingAddress;
		$finalRate = $querymain->row(0)->rate;
		$pname = $querymain->row(0)->pname;
		$partyid = $querymain->row(0)->partyid;
		$txtoutward_num = $querymain->row(0)->lorryno;
		$inv_no = $querymain->row(0)->invoiceno;

		$mat_desc = $querymain->row(0)->description;
		$thic = $querymain->row(0)->thic;
		$wid = $querymain->row(0)->wid;
		$totalweight_check = $querymain->row(0)->totalweight;
		$txthandling = $querymain->row(0)->invoiceno;
		$totalamt = $querymain->row(0)->invoiceno;
		$txtadditional_type = $querymain->row(0)->vAdditionalChargeType;
		$txtamount_mt = $querymain->row(0)->txtamount_mt;
		$totalamt = $querymain->row(0)->nsubtotal;
		$txtservicetax = $querymain->row(0)->ServiceTax;
		$txtgrandtotal = $querymain->row(0)->GrantTotal;
		$container = $querymain->row(0)->container;

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdfname= 'loadingslip_'.$pname.'.pdf';
		$resolution= array(72, 150);
		$pdf->SetAuthor('ASPEN');
		$pdf->SetTitle('Invoice');
		$pdf->SetSubject('Invoice');
		$pdf->SetKeywords('Aspen, bill, invoice');
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 7);
		$pdf->AddPage();
		//$coilno='',$partyname='',$description='',$lorryno='',$totalpcs='',$totalweight='',$totamount=''
		
$html = '<table width="100%"  cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td align="center"><b>Duplicate Job Work / Delivery Challan</b></td>
			</tr>
			<tr>
				<td width="16%" align:"left"><h4>TIN:29730066589</h4></td>
				<td width="70%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>ASPEN STEEL PVT LTD</h1></td>
				<td width="25%" align:"right"><h4>Service Tax Regn. No: (BAS)/AABCA4807HST001</h4></td>
		</tr>
		<tr>
			<td align="center" width="100%"><h4>Branch At: Plot no 16E, Bidadi Industrial Area, Phase 2 Sector 1, Bidadi, Ramnagara-562105, <b>Email: aspensteel_unit2@yahoo.com </b></h4></td>
		</tr>
		<tr>
			<td align="center" width="100%"><h4>Head Office At: 54/1, Medahalli, Old Madras Road, Bangalore-560049</h4></td>
		</tr>		
		</table>
		<table width="100%" cellspacing="0" cellpadding="0" >
			<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=3 width=100></td>
			</tr>
			<tr>
				<td width="30%" align:"left"><h3>Duplicate of Billnumber : '.$billNo.'</h3></td>
				<td width="40%" align="center"><h3>Coilnumber : '.$partyid.'</h3></td>
				<td width="33.33%" align:"right"><h3>Billdate : '.$billdate.' </h3></td>
			</tr>
			<tr><td></td></tr>
			<tr>
				<td width="30%" align:"left">
					<h3>'.$billingAddress.'</h3>
				</td>
				<td width="40%" align="center"><h3> Desp. By Lorry No. : '.$txtoutward_num.'</h3> </td>
				<td width="33.33%" align:"right"><h3>Delivery: Full &nbsp; Part-1&nbsp; Part-2</h3></td>
			</tr>
			<tr><td></td></tr>
			<tr>	
				<td width="30%" align:"left">
					<h3>Tin Number : '.$tin_number.'</h3>
				</td>
				<td width="40%" align="center"><h3> Inward Date : 	'.$inwarddate.'<b> </b></h3> </td>
				<td width="33.33%" align:"right"><h3>Inward Challan No. : '.$inv_no.'</h3></td>
			</tr></table>';
	

		$html .= '
		<hr color=#00CC33 size=5 width=100>
		<table cellspacing="0" cellpadding="3" border="0px" width="100%">
		<tr>
				<th style="font-weight:bold;" width="13%"><h3>Sl. No.</h3></th>
				<th style="font-weight:bold" width="40%"><h3>Description</h3></th>
				<th style="font-weight:bold" width="16.6%"><h3>Qty. In M/T</h3></th> 
				<th style="font-weight:bold"  width="16.6%"><h3>Rate per M/T</h3></th>
				<th style="font-weight:bold"  width="16.6%"><h3>Amount</h3></th>
				
			</tr>
		<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>
				
		</tr>	
		<tr>
		<td width="13%" align="left"><h3>1</h3></td>
		<td width="70px" align="left"><h3>'.$mat_desc.'</h3></td>
		<td width="50px" align="left"><h3>'.$thic.'</h3></td>		
		<td width="30px" align="right">*</td>	
		<td width="50px" align="right"><h3>'.$wid.'</h3></td>		
		<td width="110px" align="right"><h3>'.round($totalweight_check,3).'</h3></td>  
		<td width="110px" align="right"><h3>'.$txthandling.'</h3></td> 
		</tr>	
						
		</table>';	

		$html .= '
		<hr color=#00CC33 size=5 width=100>
		<table width="100%" cellspacing="5" cellpadding="5" border="0">	
			<tr><td align="left"> <h3>Processing / Handling charges of coils</h3></td></tr>
			<tr>
				<td style="font-weight:bold;" width="13%"><h3>Total</h3></td>
				<td style="font-weight:bold"  width="23%"></td>
				<td style="font-weight:bold"  width="16.6%"><h3></h3></td> 
				<td style="font-weight:bold" width="18%"><h3>'.round($totalweight_check,3).'</h3></td> 
				<td style="font-weight:bold"  width="15.6%"><h3>'.$txthandling.'</h3></td>  
				<td style="font-weight:bold"  width="15.6%"><h3>'.$totalamt.'</h3></td> 
			</tr>
		<tr>
		<td width="90%">
			<h3><b>Other Charges:&nbsp;'.$txtadditional_type.'</b></h3>
			</td> <td><h3>'.$txtamount_mt.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
			<h3><b>For weight</b></h3>
			</td> <td><h3>--</h3></td>
		</tr>
		<tr>
		<td width="89%">
			<h3><b>For width</b></h3>
			</td> <td><h3>--</h3></td>
		</tr>
		<tr>
		<td width="89%">
			<h3><b>For length </b></h3>
			</td> <td><h3>--</h3></td>
		</tr>
		<tr>
		<td width="89%">
			<h3><b>SUBTOTAL</b></h3>
			</td> <td><h3>'.$totalamt.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
			<h3><b>Service Tax @ '.$serviceTaxPercent.'%</b></h3>
			</td> <td><h3>'.ceil($txtservicetax).'</h3></td>
		</tr>
		<tr>
			<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>	
		</tr>
		<tr>
		<td width="89%">
			<h3><b>Grand Total</b></h3>
			</td> <td><h3>'.$txtgrandtotal.'</h3></td>
		</tr>
		<tr>
		<td width="25%">
			<h3>Grand Total in Words :</h3>
			</td> 	<td width="75%"><h3>'.$container.'</h3></td>
		</tr>
		<tr>
			<td width="70%">
				<h3><b>Received the above goods in good condition.</b></h3>
				</td> 
				<td width="30%"><h3> For ASPEN STEEL (P) LTD.</h3></td>
		</tr>
		<tr><td></td></tr>
		<tr>
			<td width="70%">
				<h3><b>Receivers Signature</b></h3>
				</td> 
				<td width="30%"><h3> Manager/Director</h3></td>
		</tr>
		
		</table>';
		$pdf->writeHTML($html, true, 0, true, true);
		$pdf->Ln();
		$pdf->lastPage();
		$pdf->Output($pdfname, 'I');		
	}

	function generateSlittingBillDuplicate( $billNo ) {
	
		$sqlbilling= "select aspen_tblbilldetails.vIRnumber as partyid,aspen_tblbilldetails.nBillNo as billnumber,DATE_FORMAT(aspen_tblbilldetails.dBillDate, '%d-%m-%Y') as billdate,aspen_tblpartydetails.nPartyName as partyname,aspen_tblpartydetails.nTinNumber as tinnmber,aspen_tblpartydetails.vAddress1 as address1,aspen_tblpartydetails.vAddress2 as address2,aspen_tblpartydetails.vCity as city,aspen_tblbilldetails.vOutLorryNo as trucknumber,aspen_tblmatdescription.vDescription as materialdescription,aspen_tblinwardentry.vInvoiceNo as invoiceno,DATE_FORMAT(aspen_tblinwardentry.dInvoiceDate, '%d-%m-%Y') as invoicedate ,aspen_tblinwardentry.fWidth as width,aspen_tblinwardentry.fThickness as thickness,aspen_tblbillingstatus.nSno as Sno,aspen_tblbillingstatus.nActualNo as Length,aspen_tblpricetype1.nAmount as rate,aspen_tblbillingstatus.nActualNo as noofpcs,DATE_FORMAT(aspen_tblinwardentry.dReceivedDate, '%d-%m-%Y') as inwardDate,
		aspen_tblbillingstatus.fbilledWeight as weight,aspen_tblbilldetails.ntotalpcs as totalpcs,aspen_tblbilldetails.fTotalWeight as totalweight,round(aspen_tblbilldetails.fWeightAmount) as weihtamount,aspen_tblbilldetails.ntotalamount as totalamount,aspen_tblbilldetails.nScrapSent as Scrapsent,round(aspen_tblbilldetails.ocwtamount) as wtamount,round(aspen_tblbilldetails.ocwidthamount) as widthamount,aspen_tblbilldetails.oclengthamount as lengthamount,round(aspen_tblbilldetails.fServiceTax) as servicetax,round(aspen_tblbilldetails.fEduTax) as edutax,aspen_tblbilldetails.fSHEduTax as shedutax,aspen_tblbilldetails.fGrantTotal as grandtotal,aspen_tblbilldetails.vAdditionalChargeType as additionalchargetype,round(aspen_tblbilldetails.fAmount) as amount,aspen_tblbilldetails.vAdditionalChargeType1 as additionalchargetype1,round(aspen_tblbilldetails.fAmount1) as amount1,round(aspen_tblbilldetails.nsubtotal) as subtotal,aspen_tblbilldetails.grandtot_words as container,aspen_tblbilldetails.nServiceTaxPercent as serviceTaxPercent 
		from aspen_tblinwardentry 
		LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId 
		LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
		left join aspen_tblpricetype1 on aspen_tblpricetype1.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
		LEFT JOIN aspen_tblbilldetails ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
		LEFT JOIN aspen_tbladditionalbillchargetype ON aspen_tbladditionalbillchargetype.nBillNo=aspen_tblbilldetails.nBillNo where aspen_tblbilldetails.nBillNo = $billNo LIMIT 1 ";

		$querymain = $this->db->query($sqlbilling);
	
		$billnumber = $querymain->row(0)->billnumber;
		$billdate = $querymain->row(0)->billdate;
		$invoice = $querymain->row(0)->partyid;
		$partyid = $querymain->row(0)->partyid;
		$party_name = $querymain->row(0)->partyname;
		$width = $querymain->row(0)->width;
		$thickness = $querymain->row(0)->thickness;
		$invoicedate = $querymain->row(0)->invoicedate;
		$address_one = $querymain->row(0)->address1;
		$address_two = $querymain->row(0)->address2;
		$invoiceno = $querymain->row(0)->invoiceno;
		$city = $querymain->row(0)->city;
		$tinnmber = $querymain->row(0)->tinnmber;
		$inwardDate = $querymain->row(0)->inwardDate;
		$trucknumber = $querymain->row(0)->trucknumber;
		$material_description = $querymain->row(0)->materialdescription;
		$additionalchargetype = $querymain->row(0)->additionalchargetype;
		$amount = $querymain->row(0)->amount;
		$additionalchargetype1 = $querymain->row(0)->additionalchargetype1;
		$amount1 = $querymain->row(0)->amount1;
		$Sno = $querymain->row(0)->Sno;
		$rate = $querymain->row(0)->rate;
		$Length = $querymain->row(0)->Length;
		$noofpcs = $querymain->row(0)->noofpcs;
		$weight = $querymain->row(0)->weight;
		$Scrapsent = $querymain->row(0)->Scrapsent;
		$totalpcs = $querymain->row(0)->totalpcs;
		$totalweight = $querymain->row(0)->totalweight;
		$weihtamount = $querymain->row(0)->weihtamount;
		$totalamount = $querymain->row(0)->totalamount;
		$wtamount = $querymain->row(0)->wtamount;
		$widthamount = $querymain->row(0)->widthamount;
		$lengthamount = $querymain->row(0)->lengthamount;
		$servicetax = $querymain->row(0)->servicetax;
		$edutax = $querymain->row(0)->edutax;
		$shedutax = $querymain->row(0)->shedutax;
		$grandtotal = $querymain->row(0)->grandtotal;
		$subtotal = $querymain->row(0)->subtotal;
		$tin_number = $querymain->row(0)->tinnmber;
		$container = $querymain->row(0)->container;
		$serviceTaxPercent = $querymain->row(0)->serviceTaxPercent;
	
		$strSqlSlittingBundleDetails = "select aspen_tblBillBundleAssociation.nBundleNumber,
						aspen_tblinwardentry.fThickness,
						aspen_tblmatdescription.vDescription as description,
						round((aspen_tblslittinginstruction.nWeight/1000),3) as weight,
						$weihtamount as rate,
						round(( $weihtamount * (aspen_tblslittinginstruction.nWeight/1000) ),2) as amount
						from aspen_tblinwardentry
						left join aspen_tblslittinginstruction on  aspen_tblslittinginstruction.vIRnumber = aspen_tblinwardentry.vIRnumber 
						left join aspen_tblBillBundleAssociation on aspen_tblslittinginstruction.nSno = aspen_tblBillBundleAssociation.nBundleNumber
						left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
						where aspen_tblBillBundleAssociation.nBillNumber = $billnumber and aspen_tblinwardentry.vIRnumber=$invoice order by aspen_tblslittinginstruction.nSno";

		$queryBundleDetails = $this->db->query($strSqlSlittingBundleDetails);

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdfname= 'cuttingslip_'.$partyid.'.pdf';
		$pdf->SetAuthor('ASPEN');
		$pdf->SetTitle('Invoice');
		$pdf->SetSubject('Invoice');
		$pdf->SetKeywords('Aspen, bill, invoice');
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 8);
		$pdf->AddPage();		
		
		$html = '<table width="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td width="16%" align:"left"><h4>TIN:29730066589</h4></td>
						<td width="70%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>ASPEN STEEL PVT LTD</h1></td>
						<td width="25%" align:"right"><h4>Service Tax Regn. No: (BAS)/AABCA4807HST001</h4></td>
					</tr>
					<tr>	
						<td align="center" width="100%"><h4>Aspen Steel Pvt Ltd, Plot no 16E, Bidadi Industrial Area, Phase 2 Sector 1, Bidadi, Ramnagara-562105, <b>Email: aspensteel_unit2@yahoo.com </b></h4></td>
					</tr>
					<tr>
						<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>
					</tr>
					<tr>
						<td width="30%" align:"left"><h3>Billnumber : '.$billnumber.'</h3></td>
						<td width="40%" align="center"><h3>Coilnumber : '.$partyid.'</h3></td>
						<td width="33.33%" align:"right"><h3>Billdate : '.$billdate.' </h3></td>
					</tr>
				</table>
				<table width="100%" cellspacing="0" cellpadding="0" >
					<tr>
						<td align="left"></td>
						<td></td>
						<td></td>				
					</tr>
					<tr>
						<td width="30%" align:"left">
							<h3>To M/s., &nbsp; '.$party_name.' , '.$address_one.' &nbsp;'.$address_two.',&nbsp;'.$city.'</h3>
						</td>
						<td width="40%" align="center"><h3> Desp. By Lorry No. : '.$trucknumber.'</h3></td>
						<td width="33.33%" align:"right"><h3>Delivery: Full &nbsp; Part-1&nbsp; Part-2</h3></td>
					</tr>
					<tr>
						<td align="left"></td>
						<td></td>
						<td></td>				
					</tr>';
		$html .= '<tr>
					<td width="30%" align:"left"><h3>Tin Number : '.$tinnmber.'</h3></td>
					<td width="40%" align="center"><h3> Inward Date : '.$inwardDate.'<b> </b></h3> </td>
					<td width="33.33%" align:"right"><h3>Inward Challan No.:'.$invoiceno.'</h3></td>
				</tr>';
		$html .= '<tr>
					<td align="center">&nbsp;</td>
					<td align="center">&nbsp;</td>
					<td align="center">&nbsp;</td>
				</tr>';
	
		$html .= '</table>';
		$html .= '<table cellspacing="0" cellpadding="5" border="0px" width="100%">
					<tr>
						<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>
					</tr>
					<tr>
						<th style="font-weight:bold;" width="13%"><h4>Sl. No.</h4></th>
						<th style="font-weight:bold"  width="40%"><h4>Description</h4></th>
						<th style="font-weight:bold" width="16.6%"><h4>Qty. In M/T</h4></th> 
						<th style="font-weight:bold"  width="16.6%"><h4>Rate per M/T</h4></th>
						<th style="font-weight:bold"  width="16.6%"><h4>Amount</h4></th>
					</tr>
					<tr><td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td></tr>';

					if($queryBundleDetails->num_rows() > 0) {
						foreach($queryBundleDetails->result() as $rowitem) {
							$html .= '<tr>
										<td width="13%"><b>'.$rowitem->nBundleNumber.'</b></td>
										<td width="40%"><b>'.$rowitem->description.'</b></td>
										<td width="16.6%"><b>'.$rowitem->weight.'</b></td> 
										<td width="16.6%"><b>'.$rowitem->rate.'</b></td> 
										<td width="33%"><b>'.$rowitem->amount.'</b></td>
										<td width="15.6%"></td>	
									</tr>';
						}
					}		
	$html .= '</table>';
	$html .= '
		<table width="800px" cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td width="300px"></td>
				<td><hr width="310"></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td width="300px" align="left"><b>TOTAL: </b></td>
				<td width="105px" align="center"><b>'.round(($totalweight/1000),3).'</b></td>
				<td width="110px" align="center"><b>'.$weihtamount.'</b></td>
				<td width="95px" align="center"><b>'.$totalamount.'</b></td>	
			</tr>
			<tr><td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</td></tr>
			<tr>
				<td width="420px" align="left"><b>Scrap pieces</b></td>
				<td width="150px" align="right"><b>'.$Scrapsent.'</b></td>
			</tr>
			<tr>
				<td width="420px" align="left"><b>Additional Charges : '.$additionalchargetype.'</b></td>
				<td width="160px" align="right"><b>'.$amount.'</b></td>
			</tr>
			<tr>
				<td width="420px" align="left"><b>Strapping Charge : '.$additionalchargetype1.'</b></td>
				<td width="160px" align="right"><b>'.$amount1.'</b></td>
			</tr>
			<tr>
				<td width="450px" border="0" align="left"></td>
				<td><hr width=100%></td>	
			</tr>
			<tr>
				<td width="550px" border="0" align="left"><b>Subtotal </b></td>
				<td><b>'.$subtotal.'</b>&nbsp;&nbsp;</td>				
			</tr>
			<tr>
				<td width="550px" border="0" align="left"><b>Service Tax @ '.$serviceTaxPercent.'%</b></td>
				<td><b>'.$servicetax.'</b>&nbsp;&nbsp;</td>				
			</tr>
			<tr>
				<td width="550px" border="0" align="left"><b>Grand Total</b></td>
				<td><b>'.$grandtotal.'</b>&nbsp;&nbsp;</td>				
			</tr>
			<tr>
				<td width="200px" border="0" align="left"><b>Grand Total In words</b></td>
				<td width="300px" border="0" align="left"><b>'.$container.'</b>&nbsp;</td>
			</tr>
			<tr>
				<td width="65%">
					<b>Received the above goods in good condition.</b>
				</td> 
				<td width="25%"><b>For ASPEN STEEL (P) LTD.</b></td>
			</tr>
			<tr><td></td></tr>
			<tr>
				<td width="70%">
					<b>Receivers Signature</b>
				</td> 
				<td width="30%"><b>Manager/Director</b></td>
			</tr>
		</table>';

		$pdf->writeHTML($html, true, 0, true, true);
		$pdf->Ln();
		$pdf->lastPage();
		$pdf->Output($pdfname, 'I');		
	}

	function processCancelBill( $billNo ) {
		
	}

	function processDeleteBill( $billNo ) {

	}
} 

class Billings_model extends Base_module_record {
 
}
