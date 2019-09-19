<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/base_module_model.php');
require_once(MODULES_PATH.'/billing/config/billing_constants.php');
require_once(APPPATH.'helpers/tcpdf/config/lang/eng.php');
require_once(APPPATH.'helpers/tcpdf/tcpdf.php');

class Billing_model extends Base_module_model {
    private $companyData;

    function __construct() {
        parent::__construct('aspen_tblbilldetails');// table name
        $CI =& get_instance();
        $this->companyData = $CI->fuel_auth->company_data();
    }

	function example(){
		return true;
	}

	function checkbillno($POST) {
      if($POST) {
         $billid = $POST["billid"];
      }
      $checkdata = "select * from aspen_tblbilldetails where nBillNo = '".$billid."'  LIMIT 0,1";
      $checkquery = $this->db->query($checkdata);
      if ($checkquery->num_rows() == 0) {
           echo '0';
      } else {
           echo '1';
      }
    }


	function totalweight_checkmodels($partyid, $bundleIds) {
		$sqlfb = "select round(sum(aspen_tblslittinginstruction.nWeight),2) as totalWeight from aspen_tblslittinginstruction
					where aspen_tblslittinginstruction.vIRnumber='$partyid' and aspen_tblslittinginstruction.nSno in ($bundleIds)";

		$query = $this->db->query($sqlfb);
		return $query->result()[0]->totalWeight;
	}


    function getNextBillNo(){
	  $sqlpcheck= "select max(nBillNo)+1 as nextBillNo from aspen_tblbilldetails where nBillNo < 865930";

	  $query = $this->db->query($sqlpcheck);
       if ($query->num_rows() > 0) {
             foreach ($query->result() as $row)
             {
                $nextBillNo = $row->nextBillNo;
             }
             return $nextBillNo;
        }
	  return NULL;
	}

	function getServiceTaxPercent() {
		$queryServiceTax = $this->db->query("select * from aspen_tbltaxdetails where vTypeOfTax = 'SERVICE TAX'");
		$doubleServiceTaxPercent = '';
		if($queryServiceTax->num_rows() > 0) {
			$row = $queryServiceTax->result();
			$doubleServiceTaxPercent = $row[0]->nPercentage;
		}
		return $doubleServiceTaxPercent;
	}

	function billingpreviewviewmodel($pid, $pname,$nsno){
		if(isset($pid) && isset($pname)) {
			$partyname = $pname;
			$partyid = $pid;
			$nsno = $nsno;
		}
        $sqlpcheck= "Select vprocess from aspen_tblinwardentry where vIRnumber='".$pid."'";
        $query = $this->db->query($sqlpcheck);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		json_encode($arr);
		//echo $row->vprocess;
	foreach ($arr as $row){
	if( $row->vprocess =='Cutting')
	{
		$sql="SELECT aspen_tblinwardentry.vIRnumber,  aspen_tblmatdescription.vDescription, aspen_tblinwardentry.fThickness, aspen_tblinwardentry.fWidth, aspen_tblinwardentry.fQuantity,aspen_tblinwardentry.vInvoiceNo, aspen_tblinwardentry.fLength,aspen_tblcuttinginstruction.nSno,aspen_tblpartydetails.vCusrateadd,aspen_tblpartydetails.vCusraterm
		FROM aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
		LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId
		LEFT JOIN aspen_tblcuttinginstruction ON aspen_tblcuttinginstruction.vIRnumber= aspen_tblinwardentry.vIRnumber ";
		if(!empty($partyname) && !empty($partyid))
		{
		$sql.="WHERE aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$partyid."'  and aspen_tblcuttinginstruction.nSno IN( SELECT case when aspen_tblcuttinginstruction.nSno = '' then 0 else aspen_tblcuttinginstruction.nSno end from aspen_tblcuttinginstruction)";
		$query = $this->db->query($sql);
		}
	}
		else if($row->vprocess =='')
		{
		$sql="SELECT aspen_tblinwardentry.vIRnumber,  aspen_tblmatdescription.vDescription, aspen_tblinwardentry.fThickness, aspen_tblinwardentry.fWidth, aspen_tblinwardentry.fQuantity,aspen_tblinwardentry.vInvoiceNo, aspen_tblinwardentry.fLength,aspen_tblcuttinginstruction.nSno,aspen_tblpartydetails.vCusrateadd,aspen_tblpartydetails.vCusraterm
		FROM aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
		LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId
		LEFT JOIN aspen_tblcuttinginstruction ON aspen_tblcuttinginstruction.vIRnumber= aspen_tblinwardentry.vIRnumber ";
		if(!empty($partyname) && !empty($partyid))
		{
		$sql.="WHERE aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$partyid."'";
		$query = $this->db->query($sql);
		}
		}
		}
		$arr='';
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
		      $arr[] =$row;
		   }
		}
		return json_encode($arr[0]);
	}

	function billingpreviewviewcntrlr_slit($pid, $pname,$slno){
		if(isset($pid) && isset($pname)) {
			$partyname = $pname;
			$partyid = $pid;
			$slno = $slno;
		}
		$sql="SELECT aspen_tblinwardentry.vIRnumber,  aspen_tblmatdescription.vDescription, aspen_tblinwardentry.fThickness, aspen_tblinwardentry.fWidth, aspen_tblinwardentry.fQuantity,aspen_tblinwardentry.vInvoiceNo, aspen_tblinwardentry.fLength,aspen_tblslittinginstruction.nSno,aspen_tblpartydetails.vCusrateadd,aspen_tblpartydetails.vCusraterm
		FROM aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
		LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId
		LEFT JOIN aspen_tblslittinginstruction ON aspen_tblslittinginstruction.vIRnumber= aspen_tblinwardentry.vIRnumber ";
		if(!empty($partyname) && !empty($partyid))
		{
		$sql.="WHERE aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$partyid."'  and aspen_tblslittinginstruction.nSno IN( SELECT case when aspen_tblslittinginstruction.nSno = '' then 0 else aspen_tblslittinginstruction.nSno end from aspen_tblslittinginstruction)";
		$query = $this->db->query($sql);
		}
		$arr='';
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
		      $arr[] =$row;
		   }
		}
		return json_encode($arr[0]);
	}

	function billingpreviewviewcntrlrrecoil($pid, $pname,$recno){
		if(isset($pid) && isset($pname)) {
			$partyname = $pname;
			$partyid = $pid;
			$recno = $recno;
		}
		$sqlre="SELECT aspen_tblinwardentry.vIRnumber,  aspen_tblmatdescription.vDescription, aspen_tblinwardentry.fThickness, aspen_tblinwardentry.fWidth, aspen_tblinwardentry.fQuantity,aspen_tblinwardentry.vInvoiceNo, aspen_tblinwardentry.fLength,aspen_tblrecoiling.nSno,aspen_tblpartydetails.vCusrateadd,aspen_tblpartydetails.vCusraterm
		FROM aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
		LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId
		LEFT JOIN aspen_tblrecoiling ON aspen_tblrecoiling.vIRnumber= aspen_tblinwardentry.vIRnumber ";
		if(!empty($partyname) && !empty($partyid))
		{
		$sqlre.="WHERE aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$partyid."'  and aspen_tblrecoiling.nSno IN( SELECT case when aspen_tblrecoiling.nSno = '' then 0 else aspen_tblrecoiling.nSno end from aspen_tblrecoiling)";
		$query = $this->db->query($sqlre);
		}
		$arr='';
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
		      $arr[] =$row;
		   }
		}
		return json_encode($arr[0]);
	}
	function billingpreviewviewcntrlr_semidata($pid, $pname){
		if(isset($pid) && isset($pname)) {
			$partyname = $pname;
			$partyid = $pid;
		}
		$sqldir="SELECT aspen_tblinwardentry.vIRnumber,  aspen_tblmatdescription.vDescription, aspen_tblinwardentry.fThickness, aspen_tblinwardentry.fWidth, (aspen_tblinwardentry.fQuantity-sum(aspen_tblbillingstatus.fWeight)) as weight,aspen_tblinwardentry.vInvoiceNo, aspen_tblinwardentry.fLength,aspen_tblpartydetails.vCusrateadd,aspen_tblpartydetails.vCusraterm
		FROM aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
		LEFT JOIN aspen_tblbillingstatus ON aspen_tblbillingstatus.vIRnumber=aspen_tblinwardentry.vIRnumber
		LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId ";
		if(!empty($partyname) && !empty($partyid))
		{
		$sqldir.="WHERE aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$partyid."'";
		$query = $this->db->query($sqldir);
		}
		$arr='';
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
		      $arr[] =$row;
		   }
		}
		return json_encode($arr[0]);
	}
	function billingpreviewviewcntrlr_dirdata($pid, $pname){
		if(isset($pid) && isset($pname)) {
			$partyname = $pname;
			$partyid = $pid;
		}

		$sqlCheckInwardEntryStatus = 'SELECT vStatus from aspen_tblinwardentry WHERE vIRnumber="'.$partyid.'"';
		$checkInwardsStatusQuery = $this->db->query($sqlCheckInwardEntryStatus );
		$checkInwardsStatusRow =  $checkInwardsStatusQuery->result();

		if( $checkInwardsStatusRow[0]->vStatus == 'RECEIVED' ) {
			$sqldir="SELECT aspen_tblinwardentry.vIRnumber,  aspen_tblmatdescription.vDescription, aspen_tblinwardentry.fThickness, aspen_tblinwardentry.fWidth, aspen_tblinwardentry.fpresent as fQuantity,aspen_tblinwardentry.vInvoiceNo, aspen_tblinwardentry.fLength,aspen_tblpartydetails.vCusrateadd,aspen_tblpartydetails.vCusraterm
			FROM aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
			LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId ";
			if(!empty($partyname) && !empty($partyid))
				$sqldir.="WHERE aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$partyid."'";
		} else {

			$sqldir = "SELECT inw.vIRnumber,aspen_tblmatdescription.vDescription,inw.fThickness,inw.fWidth,abs(round((inw.fpresent - round((t.fQuantity+p.nWeight),2)))) as fQuantity,inw.vInvoiceNo,inw.vStatus FROM aspen_tblinwardentry inw LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = inw.nMatId LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = inw.nPartyId left join (select coalesce(aspen_tblcuttinginstruction.vIRnumber,'$partyid') as vIRnumber,coalesce(round(sum(nBundleweight-(nBundleweight*nBilledNumber/nNoOfPieces)),2),0) as fQuantity from aspen_tblcuttinginstruction left join aspen_tblbillingstatus on aspen_tblcuttinginstruction.vIRnumber = aspen_tblbillingstatus.vIRnumber and aspen_tblcuttinginstruction.nSno = aspen_tblbillingstatus.nSno where aspen_tblcuttinginstruction.vIRnumber ='$partyid' ) t on t.vIRnumber = inw.vIRnumber left join (select coalesce(aspen_tblslittinginstruction.vIRnumber,'$partyid') as vIRnumber,coalesce(sum(nWeight),0) as nWeight from aspen_tblslittinginstruction LEFT JOIN aspen_tblbillingstatus ON aspen_tblslittinginstruction.vIRnumber=aspen_tblbillingstatus.vIRnumber WHERE aspen_tblslittinginstruction.nSno = aspen_tblbillingstatus.nSno and aspen_tblslittinginstruction.vIRnumber='$partyid' and aspen_tblbillingstatus.vBillingStatus != 'Billed') p on p.vIRnumber = inw.vIRnumber where inw.vIRnumber='$partyid'";
		}

		$query = $this->db->query($sqldir);
		$arr='';
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
		      $arr[] =$row;
		   }
		}
		return json_encode($arr[0]);
	}


	function otherchargecntrlrmodel($partyid='',$mat_desc='',$wei='',$txttotalweight='')
	{
		$sql = "select Distinct nAmount as rate,aspen_tblinwardentry.fQuantity as weighttext,
'".$txttotalweight."' as weight,round(nAmount * '".$txttotalweight."') as amount from aspen_tblweight
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblweight.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$wei."' between nMinWeight and nMaxWeight and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' group by aspen_tblbillingstatus.nActualNo";
		$query = $this->db->query($sql);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function lengthchargecntrlrmodel($partyid='',$mat_desc='',$len='',$actualnumberbundle='')
	{
		$sql = "select aspen_tblcuttinginstruction.nLength AS length ,nAmount as rate,aspen_tblbillingstatus.nActualNo as noofpcs,
aspen_tblbillingstatus.fbilledWeight as weight,round(nAmount * fbilledWeight) as amount from aspen_tbllength
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tbllength.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
LEFT JOIN aspen_tblcuttinginstruction ON aspen_tblbillingstatus.vIRnumber = aspen_tblcuttinginstruction.vIRnumber where aspen_tblcuttinginstruction.nLength between nMinLength and nMaxLength and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblbillingstatus.nSno = aspen_tblcuttinginstruction.nSno and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN(".$actualnumberbundle.") order by aspen_tblbillingstatus.nActualNo asc ";
		$query = $this->db->query($sql);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function finalbillingcalculatemodel($bundleid,$partyid){
	$sqlfb = "select
	round(sum(aspen_tblbillingstatus.fbilledWeight),4)as weight from aspen_tblbillingstatus
	left join aspen_tblinwardentry on aspen_tblbillingstatus.vIRnumber=aspen_tblinwardentry.vIRnumber where aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$bundleid.")";
		$query = $this->db->query($sqlfb);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function totalamount_calculatemodel($bundleid,$partyid,$txttotalweight,$thic='',$mat_desc='',$actualnumberbundle='',$cust_add='',$cust_rm=''){
	$sqltot = "select round(SUM((nAmount+'".$cust_add."'-'".$cust_rm."') * fbilledWeight)) as total from aspen_tblpricetype1
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblpricetype1.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$thic."' between nMinThickness and nMaxThickness and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$actualnumberbundle.")  order by aspen_tblbillingstatus.nActualNo asc";

		$query = $this->db->query($sqltot);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function finaltotalmodel($bundleid,$partyid){
		$sqlto = "select round(sum(nAmount * fbilledWeight),2)
	round(sum(aspen_tblbillingstatus.fbilledWeight),2)as weight from aspen_tblbillingstatus
	left join aspen_tblinwardentry on aspen_tblbillingstatus.vIRnumber=aspen_tblinwardentry.vIRnumber where aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$bundleid.")";
		$query = $this->db->query($sqlto);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function lenvalue($pid)
	{
		$sqllv = "SELECT COUNT(DISTINCT (nLength)) as value
       FROM  aspen_tblcuttinginstruction
         WHERE vIRnumber='".$pid."'";
		$query = $this->db->query($sqllv);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;

	}
	function presentweight($pid)
	{
		$sqllv = "SELECT fpresent as weight
       FROM  aspen_tblinwardentry
         WHERE vIRnumber='".$pid."'";
		$query = $this->db->query($sqllv);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}


	function handling($pid, $mat_desc)
	{
		$sqllv=" SELECT aspen_tblinwardentry.vIRnumber,
				aspen_tbl_directbill.nhandlingcharges as value,
				aspen_tblmatdescription.vDescription
				FROM aspen_tblmatdescription
		Left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId  = aspen_tblmatdescription.nMatId
		Left join aspen_tbl_directbill on aspen_tbl_directbill.nMatId  = aspen_tblmatdescription.nMatId
		where aspen_tblmatdescription.vDescription='".$mat_desc."'  AND aspen_tblinwardentry.vIRnumber='".$pid."'";
		$query = $this->db->query($sqllv);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;

	}

	function finalbillingcalculatenoofpcsmodel($bundleid,$partyid){
	$sqlfb = "select
	sum(aspen_tblbillingstatus.nActualNo ) as pcs from aspen_tblbillingstatus
	left join aspen_tblinwardentry on aspen_tblbillingstatus.vIRnumber=aspen_tblinwardentry.vIRnumber where aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$bundleid.")";
		$query = $this->db->query($sqlfb);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function totalengthvalue($actualnumberbundle,$partyid,$mat_desc){
	$sqlfb = "select sum(round(nAmount * fbilledWeight)) as rate from aspen_tbllength
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tbllength.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
LEFT JOIN aspen_tblcuttinginstruction ON aspen_tblbillingstatus.vIRnumber = aspen_tblcuttinginstruction.vIRnumber where aspen_tblcuttinginstruction.nLength between nMinLength and nMaxLength and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblbillingstatus.nSno = aspen_tblcuttinginstruction.nSno and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN(".$actualnumberbundle.") order by aspen_tblbillingstatus.nActualNo asc";

		$query = $this->db->query($sqlfb);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function totaweightvalue($txttotalweight,$partyid,$mat_desc,$wei){
	$sqlwt = "select round(nAmount * '".$txttotalweight."') as wtrate from aspen_tblweight
		left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblweight.nMatId
		left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$wei."' between nMinWeight and nMaxWeight and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' order by aspen_tblbillingstatus.nActualNo asc Limit 1";

		$query = $this->db->query($sqlwt);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function totawidthvalue($txttotalweight,$partyid,$mat_desc,$wid){
	$sqlwd = "select round(nAmount * '".$txttotalweight."') as widrate from aspen_tblwidth
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblwidth.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$wid."' between nMinWidth and nMaxWidth and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' order by aspen_tblbillingstatus.nActualNo asc Limit 1";

		$query = $this->db->query($sqlwd);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function widthcntrlrmodel($partyid='',$mat_desc='',$wid='',$txttotalweight='')
	{
		$sql = "select Distinct nAmount as rate,aspen_tblbillingstatus.nActualNo as noofpcs,
'".$txttotalweight."' as weight,round(nAmount * '".$txttotalweight."') as amount from aspen_tblwidth
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblwidth.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$wid."' between nMinWidth and nMaxWidth and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' group by aspen_tblwidth.nAmount";
		$query = $this->db->query($sql);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}



	function totalrate_checkmodel($partyid='',$cust_add='',$cust_rm='',$txthandling='',$mat_desc='')
	{
	$sqlfb = "select distinct ('".$txthandling."'+ '".$cust_add."'- '".$cust_rm."') as rate from aspen_tbl_directbill
	left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tbl_directbill.nMatId
	left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
	left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
	where aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."'";

		$query = $this->db->query($sqlfb);

		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function totalrate_slitmodel($partyid='',$cust_add='',$cust_rm='',$txthandling='') {
		$sqlfb = "select (nAmount+'".$cust_add."' -'".$cust_rm."') as finalRate from aspen_tblinwardentry
					left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId
					left join aspen_tblslit_thicknessrate on aspen_tblslit_thicknessrate.nMatId = aspen_tblmatdescription.nMatId
					where aspen_tblinwardentry.vIRnumber ='$partyid' and aspen_tblinwardentry.fThickness
					between aspen_tblslit_thicknessrate.nMinThickness and aspen_tblslit_thicknessrate.nMaxThickness;";

		$query = $this->db->query($sqlfb);
		return $query->result()[0]->finalRate;
	}

	function totalraterecoilmodel($partyid='',$cust_add='',$cust_rm='',$txthandling='',$mat_desc='')
	{
	$sqlre = "select sum(nAmount+'".$cust_add."' -'".$cust_rm."') as amount from aspen_tblrecoil_thicknessrate
		left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblrecoil_thicknessrate.nMatId
		left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblrecoiling on aspen_tblrecoiling.vIRnumber=aspen_tblinwardentry.vIRnumber
		where aspen_tblrecoiling.vIRnumber='".$partyid."'";
		$query = $this->db->query($sqlre);

		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}
	function totalamtrecoil($partyid='',$cust_add='',$cust_rm='',$txthandling='',$mat_desc='',$txtrecoilweight='')
	{
	$sqlre = "select sum(round((nAmount+ '".$cust_add."'- '".$cust_rm."') * '".$txtrecoilweight."') ) as amtrec from aspen_tblrecoil_thicknessrate
		left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblrecoil_thicknessrate.nMatId
		left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblrecoiling on aspen_tblrecoiling.vIRnumber=aspen_tblinwardentry.vIRnumber
		where aspen_tblrecoiling.vIRnumber='".$partyid."'";
		$query = $this->db->query($sqlre);

		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function totalamtslit($partyid='',$cust_add='',$cust_rm='',$txthandling='',$mat_desc='',$bundleIds = '' ) {
		$sqlre = "select sum((round((nAmount + '".$cust_add."' - '".$cust_rm."') * (aspen_tblslittinginstruction.nWeight/1000),2))) as amtslit from aspen_tblslit_thicknessrate
				  left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblslit_thicknessrate.nMatId
				  left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
				  left join aspen_tblslittinginstruction on aspen_tblslittinginstruction.vIRnumber=aspen_tblinwardentry.vIRnumber
				  where aspen_tblslittinginstruction.vIRnumber ='$partyid' and aspen_tblslittinginstruction.nSno in ( $bundleIds )
				and aspen_tblinwardentry.fThickness between aspen_tblslit_thicknessrate.nMinThickness and aspen_tblslit_thicknessrate.nMaxThickness;";

		$query = $this->db->query($sqlre);

		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function totalamt($partyid='',$cust_add='',$cust_rm='',$txthandling='',$mat_desc='',$wei='')
	{
		$sqlfb = "select round(('".$wei."' * ('".$txthandling."'+ '".$cust_add."'-'".$cust_rm."'))/1000) as amt from aspen_tbl_directbill
	left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tbl_directbill.nMatId
	left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
	 where aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' ";
			$query = $this->db->query($sqlfb);

		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function totalnorecoilmodel($partyid='',$cust_add='',$cust_rm='',$txthandling='',$mat_desc='',$wei='')
	{
		$sqlfb = "select sum(aspen_tblrecoiling.nNoOfRecoils) as noofrecoil from aspen_tblrecoiling
	left join aspen_tblinwardentry on aspen_tblinwardentry.vIRnumber=aspen_tblrecoiling.vIRnumber
	left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId
	 where aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblrecoiling.vIRnumber='".$partyid."' ";
			$query = $this->db->query($sqlfb);

		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}



	function directbillingmodel($partyid='',$mat_desc='',$wei='',$txthandling='',$cust_add='',$cust_rm='')
	{
	if($cust_add!=0){
	$sql = "select ('".$txthandling."'+ '".$cust_add."') as rate,
'".$wei."' as weight,round(('".$wei."' * '".$txthandling."'+ '".$cust_add."')/1000) as amount from aspen_tbl_directbill
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tbl_directbill.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
 where aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' order by aspen_tblbillingstatus.nActualNo asc";
}
else if($cust_rm!=0){
	$sql = "select ('".$txthandling."'+ '".$cust_rm."') as rate,
'".$wei."' as weight,round(('".$wei."' * '".$txthandling."'+ '".$cust_add."')/1000) as amount from aspen_tbl_directbill
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tbl_directbill.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
 where aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' order by aspen_tblbillingstatus.nActualNo asc";

}
else{
$sql = "select nAmount as rate,aspen_tblbillingstatus.nActualNo as noofpcs,
aspen_tblbillingstatus.fbilledWeight as weight,round(nAmount * fbilledWeight) as amount from aspen_tblpricetype1
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblpricetype1.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where  aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."'  order by aspen_tblbillingstatus.nActualNo asc";

}	//	echo $sql;die();
		$query = $this->db->query($sql);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}


	function finalbillingmodel($partyid='',$mat_desc='',$thic='',$actualnumberbundle='',$cust_add='',$cust_rm='')
	{
	if($cust_add!=0){
	$sql = "select (nAmount+ '".$cust_add."') as rate,aspen_tblbillingstatus.nActualNo as noofpcs,
aspen_tblbillingstatus.fbilledWeight as weight,round((nAmount+ '".$cust_add."') * fbilledWeight) as amount from aspen_tblpricetype1
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblpricetype1.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$thic."' between nMinThickness and nMaxThickness and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$actualnumberbundle.") order by aspen_tblbillingstatus.nActualNo asc";
}
else if($cust_rm!=0){
	$sql = "select (nAmount - '".$cust_rm."') as rate,aspen_tblbillingstatus.nActualNo as noofpcs,
aspen_tblbillingstatus.fbilledWeight as weight,round((nAmount- '".$cust_rm."') * fbilledWeight) as amount from aspen_tblpricetype1
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblpricetype1.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$thic."' between nMinThickness and nMaxThickness and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$actualnumberbundle.") order by aspen_tblbillingstatus.nActualNo asc";

}
else{
$sql = "select nAmount as rate,aspen_tblbillingstatus.nActualNo as noofpcs,
aspen_tblbillingstatus.fbilledWeight as weight,round(nAmount * fbilledWeight) as amount from aspen_tblpricetype1
left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblpricetype1.nMatId
left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$thic."' between nMinThickness and nMaxThickness and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$actualnumberbundle.") order by aspen_tblbillingstatus.nActualNo asc";
}

		$query = $this->db->query($sql);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function finalbillingcntrlrrecoilmodel($mat_desc='',$thic='',$partyid='',$txtrecoilid='',$cust_add='',$cust_rm='',$txtrecoilweight='')
	{
	if($cust_add!=0)
	{
		$sql = "select  Distinct aspen_tblrecoiling.nSno,(aspen_tblrecoiling.nBundleweight/1000) as weight,aspen_tblrecoiling.nNoOfRecoils as numberofrecoils,(nAmount+ '".$cust_add."') as rate,
	round((nAmount+ '".$cust_add."') * '".$txtrecoilweight."') as amount from aspen_tblrecoil_thicknessrate
	left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblrecoil_thicknessrate.nMatId
	left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
	LEFT JOIN aspen_tblrecoiling  ON aspen_tblrecoiling.vIRnumber=aspen_tblinwardentry.vIRnumber
	left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$thic."' between nMinThickness and nMaxThickness and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$txtrecoilid.") order by aspen_tblbillingstatus.nActualNo asc";
	}
	else if($cust_rm!=0){
		$sql = "select Distinct aspen_tblrecoiling.nSno,(aspen_tblrecoiling.nBundleweight/1000) as weight,aspen_tblrecoiling.nNoOfRecoils as numberofrecoils,(nAmount - '".$cust_rm."') as rate,
	round((nAmount - '".$cust_rm."') * '".$txtrecoilweight."') as amount from aspen_tblrecoil_thicknessrate
	left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblrecoil_thicknessrate.nMatId
	left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
	LEFT JOIN aspen_tblrecoiling  ON aspen_tblrecoiling.vIRnumber=aspen_tblinwardentry.vIRnumber
	left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$thic."' between nMinThickness and nMaxThickness and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$txtrecoilid.") order by aspen_tblbillingstatus.nActualNo asc";

	}
	else{
	$sql = "select Distinct aspen_tblrecoiling.nSno,(aspen_tblrecoiling.nBundleweight/1000) as weight,aspen_tblrecoiling.nNoOfRecoils as numberofrecoils,nAmount as rate,
	round(nAmount * '".$txtrecoilweight."') as amount from aspen_tblrecoil_thicknessrate
	left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblrecoil_thicknessrate.nMatId
	left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
	LEFT JOIN aspen_tblrecoiling  ON aspen_tblrecoiling.vIRnumber=aspen_tblinwardentry.vIRnumber
	left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$thic."' between nMinThickness and nMaxThickness and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$txtrecoilid.") order by aspen_tblbillingstatus.nActualNo asc";

	}
		$query = $this->db->query($sql);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function finalbillingcntrlrslitmodel($partyid='',$mat_desc='',$thic='',$txtbundleids='',$cust_add='',$cust_rm='')
	{
	if($cust_add!=0){
		$sql = "select
		DISTINCT aspen_tblslittinginstruction.nSno,((aspen_tblslittinginstruction.nWidth / aspen_tblinwardentry.fWidth)* aspen_tblinwardentry.fQuantity/1000) as weight,(nAmount+ '".$cust_add."') as rate,round((nAmount + '".$cust_add."') * ((aspen_tblslittinginstruction.nWidth / aspen_tblinwardentry.fWidth)* aspen_tblinwardentry.fQuantity/1000)) as amount from aspen_tblslit_thicknessrate
		left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblslit_thicknessrate.nMatId
		left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblslittinginstruction on aspen_tblslittinginstruction.vIRnumber=aspen_tblinwardentry.vIRnumber
		left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$thic."' between nMinThickness and nMaxThickness and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$txtbundleids.") order by aspen_tblbillingstatus.nActualNo asc";
	}
	else if($cust_rm!=0){
		$sql = "select
		DISTINCT aspen_tblslittinginstruction.nSno,((aspen_tblslittinginstruction.nWidth / aspen_tblinwardentry.fWidth)* aspen_tblinwardentry.fQuantity/1000) as weight,(nAmount - '".$cust_rm."') as rate,round((nAmount - '".$cust_rm."') * ((aspen_tblslittinginstruction.nWidth / aspen_tblinwardentry.fWidth)* aspen_tblinwardentry.fQuantity/1000)) as amount from aspen_tblslit_thicknessrate
		left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblslit_thicknessrate.nMatId
		left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblslittinginstruction on aspen_tblslittinginstruction.vIRnumber=aspen_tblinwardentry.vIRnumber
		left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$thic."' between nMinThickness and nMaxThickness and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$txtbundleids.") order by aspen_tblbillingstatus.nActualNo asc";

	}
	else {
		$sql = "select
					aspen_tblslittinginstruction.nSno,
					round((aspen_tblslittinginstruction.nWeight/1000),3) as weight,
					nAmount as rate,
					ceil(round(( nAmount * (aspen_tblslittinginstruction.nWeight/1000) ),2)) as amount
					from aspen_tblslittinginstruction
					left join aspen_tblinwardentry on aspen_tblslittinginstruction.vIRnumber=aspen_tblinwardentry.vIRnumber
					left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId
					left join aspen_tblslit_thicknessrate on aspen_tblslit_thicknessrate.nMatId = aspen_tblmatdescription.nMatId
					where aspen_tblslittinginstruction.vIRnumber = '$partyid'
					and aspen_tblslittinginstruction.nSno in ( $txtbundleids )
					and aspen_tblinwardentry.fThickness
					between aspen_tblslit_thicknessrate.nMinThickness and aspen_tblslit_thicknessrate.nMaxThickness;";
	}

		$query = $this->db->query($sql);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}

	function directbilling($partyid='',$mat_desc='',$cust_add='',$cust_rm='',$txthandling='',$wei)
	{
	if($cust_add!=0){
		$sql = "select distinct ('".$txthandling."'+ '".$cust_add."') as rate,
		('".$wei."'/1000) as weight,round(('".$wei."' * ('".$txthandling."'+ '".$cust_add."')  )/1000) as amount from aspen_tbl_directbill
		left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tbl_directbill.nMatId
		left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
		 where aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' order by aspen_tblbillingstatus.nActualNo asc";
	}
	else if($cust_rm!=0){
		$sql = "select  distinct ('".$txthandling."'- '".$cust_rm."') as rate,
		('".$wei."'/1000) as weight,round(('".$wei."' * ('".$txthandling."'- '".$cust_rm."'))/1000) as amount from aspen_tbl_directbill
		left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tbl_directbill.nMatId
		left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
		 where aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' order by aspen_tblbillingstatus.nActualNo asc";

	}
	else{
		$sql = "select  distinct ('".$txthandling."') as rate,
		('".$wei."'/1000) as weight,round(('".$wei."' * '".$txthandling."')/1000) as amount from aspen_tbl_directbill
		left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tbl_directbill.nMatId
		left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
		 where aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' order by aspen_tblbillingstatus.nActualNo asc";
	}	//echo $sql; die();
		$query = $this->db->query($sql);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}


  function billbundle($pid)
 {
 if(isset( $_POST['pid'])) {
  $pid = $_POST['pid'];
  }
  $sql = "select aspen_tblcuttinginstruction.nSno as bundlenumber,aspen_tblcuttinginstruction.nBundleweight as weight,aspen_tblcuttinginstruction.nNoOfPieces as actualnumber,aspen_tblcuttinginstruction.nLength as length,aspen_tblbillingstatus.nActualNo as notobebilled from aspen_tblcuttinginstruction
	LEFT JOIN aspen_tblbillingstatus ON aspen_tblbillingstatus.vIRnumber = aspen_tblcuttinginstruction.vIRnumber
	WHERE aspen_tblcuttinginstruction.vIRnumber='".$pid."'";

    $query = $this->db->query($sql);

    $arra='';
    if ($query->num_rows() > 0)
    {
    foreach ($query->result() as $row)
    {
    $arra[] =$row;
    }
    }
    return $arra;
  }

function bundlelistdetails($partyid = '',$nsno = '') {
	$sqlbu = "select aspen_tblbillingstatus.nSno as bundlenumber,aspen_tblcuttinginstruction.nBundleweight as weight ,aspen_tblcuttinginstruction.nNoOfPieces as actualnumber,aspen_tblcuttinginstruction.nLength as length,aspen_tblbillingstatus.nActualNo as notobebilled,aspen_tblbillingstatus.fbilledWeight as billedweight,
	if( aspen_tblbillingstatus.nbalance = 0, aspen_tblcuttinginstruction.nNoOfPieces , aspen_tblbillingstatus.nbalance) AS balance
	 from aspen_tblcuttinginstruction
	LEFT JOIN aspen_tblbillingstatus  ON aspen_tblcuttinginstruction.vIRnumber=aspen_tblbillingstatus.vIRnumber
	WHERE aspen_tblcuttinginstruction.nSno = aspen_tblbillingstatus.nSno and  aspen_tblbillingstatus.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN(".$nsno.")  group by aspen_tblbillingstatus.nSno";
// print_r($sqlbu);exit;
	/*	$sqlins = "UPDATE aspen_tblbillingstatus SET fbilledWeight='". $nsno. "' WHERE vIRnumber='".$partyid."' and nSno='".$nsno."'";*/
	$query = $this->db->query($sqlbu);
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

function listbundledetailsslit($partyid = '',$slno = '') {
	$sqlbu = "select aspen_tblslittinginstruction.nSno as slitnumber,
				aspen_tblslittinginstruction.nLength as length,
				aspen_tblslittinginstruction.nWidth as Width,
				aspen_tblslittinginstruction.nWeight as Weight,
				 aspen_tblslittinginstruction.dDate as sdate
			from aspen_tblslittinginstruction
			where aspen_tblslittinginstruction.nSno IN($slno)
			and aspen_tblslittinginstruction.vStatus='Ready To Bill'
			and aspen_tblslittinginstruction.vIRnumber ='$partyid'";

	$query = $this->db->query($sqlbu);
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


		function listbundledetailsrecoil($partyid = '',$recno = '')
	{
	$sqlbu = "select Distinct aspen_tblbillingstatus.nSno as recoilnumber,aspen_tblrecoiling.nNoOfRecoils as numberofrecoils ,aspen_tblrecoiling.dStartDate as stdate,aspen_tblrecoiling.dEndDate as enddate,nBundleweight as weight  from aspen_tblrecoiling
	LEFT JOIN aspen_tblbillingstatus  ON aspen_tblrecoiling.vIRnumber=aspen_tblbillingstatus.vIRnumber
	LEFT JOIN aspen_tblinwardentry  ON aspen_tblrecoiling.vIRnumber=aspen_tblinwardentry.vIRnumber
	WHERE aspen_tblrecoiling.nSno = aspen_tblbillingstatus.nSno and  aspen_tblbillingstatus.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN(".$recno.")  group by aspen_tblbillingstatus.nSno";
	$query = $this->db->query($sqlbu);
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

	function billdetailsprocessingcharges($partyid = '')
	{
	$sqlbdr = "select nBillNo as billnumber,dBillDate as billdata,vIRnumber as coilnumber,fTotalWeight as totalweight,fWeightAmount as weightamount,fServiceTax as servicetax,fEduTax as edutax,fSHEduTax as shedtax,fGrantTotal as granttotal,nScrapSent as scrapsent,vOutLorryNo as outlorryno,nPartyId as partyid,vBillType as billtype,BillStatus as billstatus from aspen_tblbilldetails where vIRnumber='".$partyid."' ";
	$query = $this->db->query($sqlbdr);

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

	/*function additionalbillingmodel($txtadditional_type,$txtamount_mt){
		$sql=$this->db->query("Insert into aspen_tbladditionalbillchargetype  (dBillDate,vAdditionalChargeType,fAmount)
		VALUES(now(),'". $txtadditional_type. "','". $txtamount_mt. "')");

	}*/

	function listloadlengthchargemodel($partyid = '')
	{
	$sqlwe = "select nMatId as materialdescription,nMinLength as minlength,nMaxLength as maxlength, nAmount as amount from aspen_tbllength ";
	$query = $this->db->query($sqlwe);
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

	function billgeneratemodelslitprint($coilno='',$partyname='',$description='',$lorryno='',$totalrates='',$totalweight_checks='',$totalamts='') {
	 $sqlrpt = "select aspen_tblbilldetails.vOutLorryNo as lorryno,
	 aspen_tblbilldetails.fTotalWeight as totalweight_checks,
	 aspen_tblbilldetails.ntotalpcs as totalrates,
	 aspen_tblbilldetails.ntotalamount as totalamts,
	 aspen_tblpartydetails.nPartyName as partyname,
	 aspen_tblmatdescription.vDescription as description,
	 aspen_tblinwardentry.vIRnumber as coilno
	 from aspen_tblinwardentry
	  LEFT JOIN aspen_tblbilldetails  ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
	  LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId
	  LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
	  where
		aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$coilno."'";

  $querymain = $this->db->query($sqlrpt);


  $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
  $pdfname= 'loadingslip_'.$partyname.'.pdf';
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

   $html = '
		<table align="center" width="100%" cellspacing="0" cellpadding="5"  border="0.1">
			<tr>

				<td width="100%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>LOADING SLIP</h1></td>

		</tr>
			<tr>
				<td width="30%" align="left">
				<h1><b>Party Name: </b></h1></td><td align="left" width="70%"><h1> '.$partyname.'</h1></td>
			</tr>
			<tr>
				<td align="left">
				<h1><b>Coil Number: </b></h1></td><td align="left"><h1> '.$coilno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Material Description: </b></h1></td><td align="left"><h1> '.$description.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Lorry Number: </b></h1></td><td align="left"><h1> '.$lorryno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Pieces :</b></h1></td><td align="left"><h1> '.$totalrates.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Amount:</b> </h1></td><td align="left"><h1>'.$totalamts.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Weight : </b></h1></td><td align="left"><h1> '.$totalweight_checks.' Tonnes</h1></td>
			</tr>
		</table>';
  	$html .= '
		<table cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td align="left">&nbsp;</td>
				<td align="right">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" align="center" style="font-size:45px; font-style:italic; font-family: fantasy;"><b>**ASPEN BANGALORE**</b></td>
			</tr>
		</table>';


  $pdf->writeHTML($html, true, 0, true, true);
  $pdf->Ln();
  $pdf->lastPage();
  $pdf->Output($pdfname, 'I');
 }


function billgeneratemodelslit($coilno='',$partyname='',$description='',$lorryno='',$totalrates='',$totalweight_checks='',$totalamtsslit='',$actualnumberbundle) {
	 $sqlrpt = "select aspen_tblbilldetails.vOutLorryNo as lorryno,
	 aspen_tblpartydetails.nPartyName as partyname,
	 aspen_tblmatdescription.vDescription as description,
	 aspen_tblinwardentry.vIRnumber as coilno,
	 aspen_tblinwardentry.fThickness as thickness
	 from aspen_tblinwardentry
	  LEFT JOIN aspen_tblbilldetails ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
	  LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId
	  LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
	  where
		aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$coilno."'";

  $querymain = $this->db->query($sqlrpt);

	$sqlbundle ="select Distinct aspen_tblbillingstatus.nSno as bundlenumber,
	aspen_tblslittinginstruction.nLength as length,
	aspen_tblslittinginstruction.nWidth,
	aspen_tblslittinginstruction.nWeight
	 from aspen_tblslittinginstruction
	LEFT JOIN aspen_tblbillingstatus  ON aspen_tblslittinginstruction.vIRnumber=aspen_tblbillingstatus.vIRnumber
	WHERE aspen_tblslittinginstruction.nSno = aspen_tblbillingstatus.nSno and  aspen_tblbillingstatus.vIRnumber='".$coilno."'  and aspen_tblbillingstatus.nSno IN( ".$actualnumberbundle.") order by aspen_tblbillingstatus.nSno asc";

	$queryitem = $this->db->query($sqlbundle);

  $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
  $pdfname= 'loadingslip_'.$partyname.'.pdf';
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

  $html = '
		<table align="center" width="100%" cellspacing="0" cellpadding="5"  border="0.1">
			<tr>

				<td width="100%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>LOADING SLIP</h1></td>

		</tr>
			<tr>
				<td width="30%" align="left">
				<h1><b>Party Name: </b></h1></td><td align="left" width="70%"><h1>'.$partyname.'</h1></td>
			</tr>
			<tr>
				<td align="left">
				<h1><b>Coil Number: </b></h1></td><td align="left"><h1>'.$coilno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Thickness: </b></h1></td><td align="left"><h1>'.$querymain->row(0)->thickness.'mm</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Material Description: </b></h1></td><td align="left"><h1>'.$description.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Lorry Number: </b></h1></td><td align="left"><h1>'.$lorryno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Weight : </b></h1></td><td align="left"><h1>'.round(($totalweight_checks/1000),3).' Tonnes</h1></td>
			</tr>
		</table>';

					$html .= '
		<table cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td align="left">&nbsp;</td>
				<td align="right">&nbsp;</td>
			</tr>

		</table>';

		$html .= '
			<table align="center" cellspacing="0" cellpadding="5" border="1px" width="100%">
			<tr>
				<th style="font-weight:bold" width="10%"><h1>Sl. No.</h1></th>
				<th style="font-weight:bold" width="30%"><h1>DESCRIPTION</h1></th>
				<th style="font-weight:bold" width="20%"><h1>Length</h1></th>
				<th style="font-weight:bold" width="20%"><h1>Width</h1></th>
				<th style="font-weight:bold" width="20%"><h1>Weight in M/T</h1></th>
			</tr>';

		if ($queryitem->num_rows() > 0)
		{
			foreach($queryitem->result() as $rowitem)
			{
		$html .= '
			<tr>
				<td><h1>'.$rowitem->bundlenumber.'</h1></td>
				<td><h1>LENGTH &nbsp;&nbsp;&nbsp;'.$rowitem->length.'</h1></td>
				<td><h1>'.$rowitem->length.'</h1></td>
				<td><h1>'.$rowitem->nWidth.'</h1></td>
				<td><h1>'.round(($rowitem->nWeight/1000),3).'</h1></td>
			</tr>';
			}
		}else{
		$html .= '
			<tr>
				<td align="center">&nbsp;</td>
				<td align="center">&nbsp;</td>
				<td align="center" >&nbsp;</td>
				<td align="center">&nbsp;</td>
				<td align="right">&nbsp;</td>
			</tr>';
		}
		$html .= '

		</table>';


  	$html .= '
		<table cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td align="left">&nbsp;</td>
				<td align="right">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" align="center" style="font-size:45px; font-style:italic; font-family: fantasy;"><b>**ASPEN BANGALORE**</b></td>
			</tr>
		</table>';
  $pdf->writeHTML($html, true, 0, true, true);
  $pdf->Ln();
  $pdf->lastPage();
  $pdf->Output($pdfname, 'I');
 }



 function billgeneratemodelrecoil($coilno='',$partyname='',$description='',$lorryno='',$totalrates='',$totalnorecoil='',$totalamount='') {
	 $sqlrpt = "select aspen_tblbilldetails.vOutLorryNo as lorryno,
	 aspen_tblbilldetails.fTotalWeight as totalnorecoil,
	 aspen_tblbilldetails.ntotalpcs as totalrates,
	 aspen_tblbilldetails.fGrantTotal as totalamount,
	 aspen_tblpartydetails.nPartyName as partyname,
	 aspen_tblmatdescription.vDescription as description,
	 aspen_tblinwardentry.vIRnumber as coilno
	 from aspen_tblinwardentry
	  LEFT JOIN aspen_tblbilldetails  ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
	  LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId
	  LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
	  where
		aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$coilno."'";



  $querymain = $this->db->query($sqlrpt);


  $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
  $pdfname= 'loadingslip_'.$partyname.'.pdf';
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

   $html = '
		<table align="center" width="100%" cellspacing="0" cellpadding="5"  border="0.1">
			<tr>

				<td width="100%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>LOADING SLIP</h1></td>

		</tr>
			<tr>
				<td width="30%" align="left">
				<h1><b>Party Name: </b></h1></td><td align="left" width="70%"><h1> '.$partyname.'</h1></td>
			</tr>
			<tr>
				<td align="left">
				<h1><b>Coil Number: </b></h1></td><td align="left"><h1> '.$coilno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Material Description: </b></h1></td><td align="left"><h1> '.$description.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Lorry Number: </b></h1></td><td align="left"><h1> '.$lorryno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Rates :</b></h1></td><td align="left"><h1> '.$totalrates.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Amount:</b> </h1></td><td align="left"><h1>'.$totalamount.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>No. of Recoil : </b></h1></td><td align="left"><h1> '.$totalnorecoil.' Tonnes</h1></td>
			</tr>
		</table>';
  	$html .= '
		<table cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td align="left">&nbsp;</td>
				<td align="right">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" align="center" style="font-size:45px; font-style:italic; font-family: fantasy;"><b>**ASPEN BANGALORE**</b></td>
			</tr>
		</table>';

  $pdf->writeHTML($html, true, 0, true, true);
  $pdf->Ln();
  $pdf->lastPage();
  $pdf->Output($pdfname, 'I');
 }






	function billgeneratemodelrecoilprint($coilno='',$partyname='',$description='',$lorryno='',$totalrates='',$totalnorecoil='',$txtamount='') {
	 $sqlrpt = "select aspen_tblbilldetails.vOutLorryNo as lorryno,
	 aspen_tblbilldetails.fTotalWeight as totalnorecoil,
	 aspen_tblbilldetails.ntotalpcs as totalrates,
	 aspen_tblbilldetails.ntotalamount as txtamount,
	 aspen_tblpartydetails.nPartyName as partyname,
	 aspen_tblmatdescription.vDescription as description,
	 aspen_tblinwardentry.vIRnumber as coilno
	 from aspen_tblinwardentry
	  LEFT JOIN aspen_tblbilldetails  ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
	  LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId
	  LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
	  where
		aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$coilno."'";



  $querymain = $this->db->query($sqlrpt);


  $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
  $pdfname= 'loadingslip_'.$partyname.'.pdf';
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

     $html = '
		<table align="center" width="100%" cellspacing="0" cellpadding="5"  border="0.1">
			<tr>

				<td width="100%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>LOADING SLIP</h1></td>

		</tr>
			<tr>
				<td width="30%" align="left">
				<h1><b>Party Name: </b></h1></td><td align="left" width="70%"><h1> '.$partyname.'</h1></td>
			</tr>
			<tr>
				<td align="left">
				<h1><b>Coil Number: </b></h1></td><td align="left"><h1> '.$coilno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Material Description: </b></h1></td><td align="left"><h1> '.$description.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Lorry Number: </b></h1></td><td align="left"><h1> '.$lorryno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Rates :</b></h1></td><td align="left"><h1> '.$totalrates.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Amount:</b> </h1></td><td align="left"><h1>'.$txtamount.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>No. of Recoil : </b></h1></td><td align="left"><h1> '.$totalnorecoil.' Tonnes</h1></td>
			</tr>
		</table>';
  	$html .= '
		<table cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td align="left">&nbsp;</td>
				<td align="right">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" align="center" style="font-size:45px; font-style:italic; font-family: fantasy;"><b>**ASPEN BANGALORE**</b></td>
			</tr>
		</table>';
  $pdf->writeHTML($html, true, 0, true, true);
  $pdf->Ln();
  $pdf->lastPage();
  $pdf->Output($pdfname, 'I');
 }


	function billgeneratemodelsemi($coilno='',$partyname='',$description='',$lorryno='',$totalpcs='',$totalweight='',$totamount='') {
	 $sqlrpt = "select aspen_tblinwardentry.fThickness,
	 					aspen_tblinwardentry.fWidth
	 from aspen_tblinwardentry
	  where
		aspen_tblinwardentry.vIRnumber='".$coilno."'";

  $querymain = $this->db->query($sqlrpt);

  $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
  $pdfname= 'loadingslip_'.$partyname.'.pdf';
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

  $html = '
		<table align="center" width="100%" cellspacing="0" cellpadding="5"  border="0.1">
			<tr>

				<td width="100%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>LOADING SLIP</h1></td>

		</tr>
			<tr>
				<td width="30%" align="left">
				<h1><b>Party Name: </b></h1></td><td align="left" width="70%"><h1> '.$partyname.'</h1></td>
			</tr>
			<tr>
				<td align="left">
				<h1><b>Coil Number: </b></h1></td><td align="left"><h1> '.$coilno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Material Description: </b></h1></td><td align="left"><h1> '.$description.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Lorry Number: </b></h1></td><td align="left"><h1> '.$lorryno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Thickness:</b></h1></td><td align="left"><h1> '.$querymain->row(0)->fThickness.' </h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Width:</b> </h1></td><td align="left"><h1>'.$querymain->row(0)->fWidth.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Weight: </b></h1></td><td align="left"><h1> '.$totalweight.' Tonnes</h1></td>
			</tr>
		</table>';
  	$html .= '
		<table cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td align="left">&nbsp;</td>
				<td align="right">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" align="center" style="font-size:45px; font-style:italic; font-family: fantasy;"><b>**ASPEN BANGALORE**</b></td>
			</tr>
		</table>';
  $pdf->writeHTML($html, true, 0, true, true);
  $pdf->Ln();
  $pdf->lastPage();
  $pdf->Output($pdfname, 'I');
 }





	function billgeneratemodeldirectprint($coilno='',$partyname='',$description='',$lorryno='',$totalpcs='',$totalweight='',$totamount='') {
	 $sqlrpt = "select aspen_tblbilldetails.vOutLorryNo as lorryno,
	 aspen_tblbilldetails.fTotalWeight as totalweight_check,
	 aspen_tblbilldetails.ntotalpcs as totalpcs,
	 aspen_tblbilldetails.ntotalamount as totalamt,
	 aspen_tblpartydetails.nPartyName as partyname,
	 aspen_tblmatdescription.vDescription as description,
	 aspen_tblinwardentry.vIRnumber as coilno
	 from aspen_tblinwardentry
	  LEFT JOIN aspen_tblbilldetails  ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
	  LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId
	  LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
	  where
		aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$coilno."'";

  //$sql12="UPDATE aspen_tblbillingstatus SET vBillingStatus='Billed' WHERE vIRnumber='".$coilno."'   ";


  $sql15="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.vStatus= 'Billed'
		where aspen_tblinwardentry.vIRnumber='".$coilno."' ";

		$query4 = $this->db->query($sql15);


 // $querymain = $this->db->query($sql12);

    $querymain1 = $this->db->query($sqlrpt);
  $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
  $pdfname= 'loadingslip_'.$partyname.'.pdf';
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

  $html = '
		<table align="center" width="100%" cellspacing="0" cellpadding="5"  border="0.1">
			<tr>

				<td width="100%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>LOADING SLIP</h1></td>

		</tr>
			<tr>
				<td width="30%" align="left">
				<h1><b>Party Name: </b></h1></td><td align="left" width="70%"><h1> '.$partyname.'</h1></td>
			</tr>
			<tr>
				<td align="left">
				<h1><b>Coil Number: </b></h1></td><td align="left"><h1> '.$coilno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Material Description: </b></h1></td><td align="left"><h1> '.$description.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Lorry Number: </b></h1></td><td align="left"><h1> '.$lorryno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Pieces :</b></h1></td><td align="left"><h1> '.$totalpcs.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Amount:</b> </h1></td><td align="left"><h1>'.$totamount.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Weight : </b></h1></td><td align="left"><h1> '.$totalweight.' Tonnes</h1></td>
			</tr>
		</table>';
  	$html .= '
		<table cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td align="left">&nbsp;</td>
				<td align="right">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" align="center" style="font-size:45px; font-style:italic; font-family: fantasy;"><b>**ASPEN BANGALORE**</b></td>
			</tr>
		</table>';

  $pdf->writeHTML($html, true, 0, true, true);
  $pdf->Ln();
  $pdf->lastPage();
  $pdf->Output($pdfname, 'I');
 }

	function listloadweightgchargemodel($partyid = '')
	{
	$sqlwe = "select nMatId as materialdescription,nMinWeight as minweight,nMaxWeight as maxweight, nAmount as amount from aspen_tblweight ";
	$query = $this->db->query($sqlwe);
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

	function editupdatemodel(){
		if(isset($bundlenumber) && isset($billed)&& isset($bundleweightcalculate)) {
			$bundlenumber =  $_POST['bundlenumber'];
			$billed =  $_POST['billed'];
			$pid =  $_POST['pid'];
			$bundleweightcalculate =  $_POST['bundleweightcalculate'];
		}
		$sql =   $this->db->query("UPDATE aspen_tblbillingstatus  SET nActualNo='". $_POST['billed']. "',fbilledWeight= '". $_POST['bundleweightcalculate']. "'  WHERE vIRnumber='".$_POST['pid']."' and nSno='".$_POST['bundlenumber']."' ");
	//	$query = $this->db->query($sql);
	//	echo $sql;//die();
	}

/*	function listadditionalchargemodel($partyid = ''){
		$sqlal = "select aspen_tbladditionalcharge.vType as additionalchargetype,aspen_tbladditionalcharge.nAmount as amount,additionalchargetype.nValue as value
		WHERE additionalchargetype.nMatId='".$partyid."'";
		$query = $this->db->query($sqlal);
		$arr='';
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
		      $arr[] =$row;
		   }
		}
		return $arr;
	}*/

	function savebilldetails_model($billid,$partyid,$txtamount,$txttotalweight,$txtscrap,$txtoutward_num,$txttotalpcs,$mat_desc,$thic,$actualnumberbundle,$pname,$wid,$len,$wei,$txttotallength,$txtweighttotal,$txtwidthtotal,$txtadditional_type,$txtamount_mt,$txtnsubtotal,$txtservicetax,$txteductax,$txtsecedutax,$txtRateTotal,$txtgrandtotal,$container,$driverContact) {

		$sqlServiceTaxNAddressDetails = "select nPercentage from aspen_tbltaxdetails where vTypeOfTax = 'SERVICE TAX'
											union
										select CONCAT_WS('-',CONCAT_WS(',', 'To M/s.',nPartyName,vAddress1,vAddress2,vCity),nPinId) from aspen_tblpartydetails
										right join aspen_tblinwardentry on aspen_tblinwardentry.nPartyId = aspen_tblpartydetails.nPartyId
										where  aspen_tblinwardentry.vIRnumber='$partyid'";

		$resObjServiceTaxDetails = $this->db->query($sqlServiceTaxNAddressDetails);
		$serviceTaxPercent = $resObjServiceTaxDetails->result()[0]->nPercentage;
		$strBillingAddress = $resObjServiceTaxDetails->result()[1]->nPercentage;

		$sql = "Insert into aspen_tblbilldetails (
		   nBillNo,dBillDate, vIRnumber, fTotalWeight, fWeightAmount, fServiceTax, fEduTax, fSHEduTax, fGrantTotal, nScrapSent, vOutLorryNo, nPartyId, vBillType, BillStatus, ntotalpcs, ntotalamount, ocwtamount, ocwidthamount, oclengthamount,vAdditionalChargeType,fAmount,nsubtotal,grandtot_words,nServiceTaxPercent,tBillingAddress,dFinalRate,vDriverContact)
		  VALUES('". $billid. "',now(),'". $partyid. "','". $txttotalweight. "',(select DISTINCT nAmount as rate from aspen_tblpricetype1
		left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblpricetype1.nMatId
		left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
		where '".$thic."' between nMinThickness and nMaxThickness and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$actualnumberbundle.") order by aspen_tblbillingstatus.nActualNo asc),'". $txtservicetax. "','". $txteductax. "','". $txtsecedutax. "','". $txtgrandtotal. "','". $txtscrap. "','". $txtoutward_num. "',(SELECT aspen_tblpartydetails.nPartyId  FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),'Cutting','Billing','".$txttotalpcs."' ,'".$txtamount."','". $txtweighttotal. "','". $txtwidthtotal. "','". $txttotallength. "','". $txtadditional_type. "','". $txtamount_mt. "','". $txtnsubtotal. "','". $container. "',".$serviceTaxPercent.",'".$strBillingAddress."',".$txtRateTotal.",'".$driverContact."')";

		$sql54 = "Insert into aspen_hist_tbl_billdetails (
		 nBillNo,dBillDate, vIRnumber, fTotalWeight, fWeightAmount, fServiceTax, fEduTax, fSHEduTax, fGrantTotal, nScrapSent, vOutLorryNo, nPartyId, vBillType, BillStatus, ntotalpcs, ntotalamount, ocwtamount, ocwidthamount, oclengthamount,vAdditionalChargeType,fAmount,nsubtotal,grandtot_words,fStatus)
		  VALUES('". $billid. "',now(),'". $partyid. "','". $txttotalweight. "',(select DISTINCT nAmount as rate from aspen_tblpricetype1
		left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId=aspen_tblpricetype1.nMatId
		left join aspen_tblinwardentry on aspen_tblinwardentry.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber where '".$thic."' between nMinThickness and nMaxThickness and aspen_tblmatdescription.vDescription= '".$mat_desc."' and aspen_tblinwardentry.vIRnumber='".$partyid."' and aspen_tblbillingstatus.nSno IN( ".$actualnumberbundle.") order by aspen_tblbillingstatus.nActualNo asc),'". $txtservicetax. "','". $txteductax. "','". $txtsecedutax. "','". $txtgrandtotal. "','". $txtscrap. "','". $txtoutward_num. "',(SELECT aspen_tblpartydetails.nPartyId  FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),'Cutting','Billing','".$txttotalpcs."' ,'".$txtamount."','". $txtweighttotal. "','". $txtwidthtotal. "','". $txttotallength. "','". $txtadditional_type. "','". $txtamount_mt. "','". $txtnsubtotal. "','". $container. "', 'Billed Completely')";

		$sql12="UPDATE aspen_tblbillingstatus p,
				(select aspen_tblcuttinginstruction.nSno,
					IF(aspen_tblbillingstatus.nbalance = 0, aspen_tblcuttinginstruction.nNoOfPieces - ( aspen_tblbillingstatus.nbalance +  aspen_tblbillingstatus.nActualNo ), aspen_tblbillingstatus.nbalance - aspen_tblbillingstatus.nActualNo ) as nbalance
				from aspen_tblcuttinginstruction
				right join aspen_tblbillingstatus on aspen_tblbillingstatus.vIRnumber = aspen_tblcuttinginstruction.vIRnumber and aspen_tblcuttinginstruction.nSno = aspen_tblbillingstatus.nSno
				WHERE aspen_tblcuttinginstruction.vIRnumber = '$partyid' AND aspen_tblcuttinginstruction.nSno IN ( $actualnumberbundle ) ) p1
				set p.nbalance = p1.nbalance, p.vBillingStatus='Billed', p.nBilledNumber = p.nBilledNumber + p.nActualNo
				where p.nSno = p1.nSno and p.vIRnumber = '$partyid'";

		$sqlBundleBillingAssociationInsert = "Insert into aspen_tblBillBundleAssociation(nBundleNumber,nNoOfPcs,fbilledWeight,nBillNumber) select nSno as nBundleNumber, nActualNo as nNoOfPcs, fbilledWeight as fbilledWeight, $billid from aspen_tblbillingstatus where nSno IN ( $actualnumberbundle ) and vIRnumber='$partyid'";
		$sql13="UPDATE aspen_tblcuttinginstruction SET vStatus='Billed' WHERE vIRnumber='".$partyid."' and
		aspen_tblcuttinginstruction.nSno IN( ".$actualnumberbundle.")";

		$sql14="UPDATE aspen_tblinwardentry p,
						( select
							(aspen_tblinwardentry.fpresent - sum(aspen_tblbillingstatus.fbilledWeight*1000)) as present
						  from aspen_tblinwardentry
						  left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber = aspen_tblbillingstatus.vIRnumber
						  where
							aspen_tblbillingstatus.vIRnumber='".$partyid."' and
							aspen_tblbillingstatus.nSno IN ( ".$actualnumberbundle." )  ) p1
				SET p.fpresent = p1.present
				where p.vIRnumber='".$partyid."'";

		$sql148="UPDATE aspen_hist_tblinwardentry
		SET aspen_hist_tblinwardentry.fpresent=
		aspen_hist_tblinwardentry.fQuantity-(select (SUM( aspen_tblbillingstatus.fbilledWeight ) *1000)
		from aspen_tblbillingstatus
		where aspen_tblbillingstatus.vIRnumber='".$partyid."'  group by aspen_tblbillingstatus.vIRnumber)
		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql146="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.billedweight=(select (SUM( aspen_tblbillingstatus.fbilledWeight ) *1000)
		from aspen_tblbillingstatus
		where aspen_tblbillingstatus.vIRnumber='".$partyid."'  group by aspen_tblbillingstatus.vIRnumber)
		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";


		$sql179="UPDATE aspen_hist_tblinwardentry
		SET aspen_hist_tblinwardentry.billedweight=(select (SUM( aspen_tblbillingstatus.fbilledWeight ) *1000)
		from aspen_tblbillingstatus
		where aspen_tblbillingstatus.vIRnumber='".$partyid."'  group by aspen_tblbillingstatus.vIRnumber)
		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";
	//	echo $sql179;

		$sql76="UPDATE aspen_tblinwardentry SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";
		$sql767="UPDATE aspen_hist_tblinwardentry SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";

		$sql156="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.dBillDate=(select CURDATE())
		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql1567= "UPDATE aspen_hist_tblinwardentry
		SET aspen_hist_tblinwardentry.dBillDate=(select CURDATE())
		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql33="Select fpresent from aspen_tblinwardentry where aspen_tblinwardentry.vIRnumber='".$partyid."' ";
		$query76 = $this->db->query($sql76);
		$query767 = $this->db->query($sql767);

		$query148 = $this->db->query($sql148);
		$query = $this->db->query($sql);
		$query5412 = $this->db->query($sql1567);
		$query146 = $this->db->query($sql146);
		$query556 = $this->db->query($sql156);
		$query54 = $this->db->query($sql54);
		$query549 = $this->db->query($sql179);
		$query1 = $this->db->query($sql12);
		$queryBundleBillingAssociationInsert = $this->db->query($sqlBundleBillingAssociationInsert);
		$query2 = $this->db->query($sql13);
		$query3 = $this->db->query($sql14);
		$query33 = $this->db->query($sql33);
		if($query33==1){
			$sql15="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.vStatus= 'Billed'
		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$query4 = $this->db->query($sql15);

			$strSql = "select ai.*,ap.*,am.* from aspen_tblinwardentry as ai 
			left join aspen_tblmatdescription as am on ai.nMatId = am.nMatId 
			left join aspen_tblpartydetails as ap on ap.nPartyId = ai.nPartyId
			where ai.vIRnumber = '$partyid'";
			$strquery = $this->db->query($strSql);

            $strBundleSql = "select * from aspen_tblBillBundleAssociation as abb 
				left join aspen_tblcuttinginstruction ac on ac.nSno = abb.nBundleNumber and ac.vIRnumber = '$partyid'
				 where nBillNumber = $billid";

            $queryBundle = $this->db->query($strBundleSql);
            $strBundle = '';
            $strBundle1 = '';
            if ($queryBundle->num_rows() > 0) {
                $index = 1;
                foreach($queryBundle->result() as $key => $row) {
                    $strBundle .= '%n'.$index.') '.$row->nLength.'mm - '.$row->nNoOfPcs.'Nos - '.($row->fbilledWeight*1000).'kgs';
                    $strBundle1 .= $index.') '.$row->nLength.'mm - '.$row->nNoOfPcs.'Nos - '.($row->fbilledWeight*1000).'kgs';
                    $index++;
                }
            }

			if($strquery->result()[0]->nBillingUpdates) {
				sendSMS($strquery->result()[0]->nBillingUpdates,'Bill raised for coil no '.$partyid.'%n'.$strquery->result()[0]->vDescription.' '.$strquery->result()[0]->fThickness.'mm x '.$strquery->result()[0]->fWidth.'mm'.$strBundle.'%nAppx weight '.($txttotalweight*1000).'kgs%nLoaded in '.$txtoutward_num.'%nOn:'.date('d/m/Y').'%nInvoice no:'.$billid.'%nDriver no:'.$driverContact);
			}

            if($strquery->result()[0]->vemailaddress) {

                $strEmailHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                            <title>Cutting bill for coil number '.$partyid.'</title>
                            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                            <meta name="viewport" content="width=device-width, initial-scale=1.0 " />
                            <style>
                            </style>
                            </head>';

                $strEmailHtml .= '<h4>Dear Customer,</h4>';
                $strEmailHtml .= '<h4>A Bill has been generated for coil number '.$partyid.'. The following info is for your  perusal:</h4>';
                $strEmailHtml .= '<table style="width:80%; border-collapse: collapse;" cellpadding="5">
                            <tr>
                                <td style="border: 1px solid black;">Coil Number</td>
                                <td style="border: 1px solid black;">'.$partyid.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Material Description</td>
                                <td style="border: 1px solid black;">'.$strquery->result()[0]->vDescription.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Thickness</td>
                                <td style="border: 1px solid black;">'.$strquery->result()[0]->fThickness.' mm</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Width</td>
                                <td style="border: 1px solid black;">'.$strquery->result()[0]->fWidth.' mm</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Appx weight</td>
                                <td style="border: 1px solid black;">'.$txttotalweight.' M/T</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Loaded in vehicle no</td>
                                <td style="border: 1px solid black;">'.$txtoutward_num.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">On</td>
                                <td style="border: 1px solid black;">'.date('d/m/Y').'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Invoice no</td>
                                <td style="border: 1px solid black;">'.$billid.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Driver no</td>
                                <td style="border: 1px solid black;">'.$driverContact.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Bill Details</td>
                                <td style="border: 1px solid black;word-wrap: break-word;overflow-wrap: break-word;">'.$strBundle1.'</td>
                            </tr>
                          </table>';

                $strEmailHtml .= '<p>For '.$this->companyData->company_name.'</p>
                          <p>Please contact our unit coordinator for any clarification.</p>
                          <p>Customer Service team<br/>
                          Unit 2 (Bidadi)<br/>
                          8217766390/7008898426</p>';

                $strEmailHtml .= '<p style="color:#999999;">This is a system generated mail. Please reply to aspen.bidadi@gmail.com for more details.</p>';
                $pdfname = $this->finalbillgeneratemodel($partyid,$actualnumberbundle,$cust_add, $cust_rm,$billid,$_POST['gstType'], true);
                sendEmail($strquery->result()[0]->vemailaddress, 'Bill generated for coil number '.$partyid, $strEmailHtml, $pdfname);
            }
		}
		else {
			return 0;
		}
	}


	function directbillingbill($billid,$partyid,$pname,$cust_add,$cust_rm,$mat_desc,$thic,$wid,$len,$wei,$inv_no,$totalweight_check,$totalrate,$totalamt,$txthandling,$txtadditional_type,$txtamount_mt,$txtoutward_num,$txtscrap,$txtservicetax,$txteductax,$txtsecedutax,$txtgrandtotal,$container,$driverContact){

		$sqlServiceTaxNAddressDetails = "select nPercentage from aspen_tbltaxdetails where vTypeOfTax = 'SERVICE TAX'
											union
										select CONCAT_WS('-',CONCAT_WS(',', 'To M/s.',nPartyName,vAddress1,vAddress2,vCity),nPinId) from aspen_tblpartydetails
										right join aspen_tblinwardentry on aspen_tblinwardentry.nPartyId = aspen_tblpartydetails.nPartyId
										where  aspen_tblinwardentry.vIRnumber='$partyid'";

		$resObjServiceTaxDetails = $this->db->query($sqlServiceTaxNAddressDetails);
		$serviceTaxPercent = $resObjServiceTaxDetails->result()[0]->nPercentage;
		$strBillingAddress = $resObjServiceTaxDetails->result()[1]->nPercentage;

		$sql = "Insert into aspen_tblbilldetails (
		   nBillNo,dBillDate, vIRnumber, fTotalWeight, fWeightAmount, fServiceTax, fEduTax, fSHEduTax, fGrantTotal, nScrapSent, vOutLorryNo, nPartyId, vBillType, BillStatus, ntotalpcs, ntotalamount, ocwtamount, ocwidthamount, oclengthamount,vAdditionalChargeType,fAmount,nsubtotal,grandtot_words,nServiceTaxPercent,tBillingAddress,vDriverContact)
		  VALUES('". $billid. "',now(),'". $partyid. "','". $totalweight_check. "','". $totalrate. "','". $txtservicetax. "','". $txteductax. "','". $txtsecedutax. "','". $txtgrandtotal. "','". $txtscrap. "','". $txtoutward_num. "',(SELECT aspen_tblpartydetails.nPartyId  FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),'Directbilling','Billing',0,'0','0','0','0','". $txtadditional_type. "','". $txtamount_mt. "','". $totalamt. "','". $container. "',".$serviceTaxPercent.",'".$strBillingAddress."','".$driverContact."')";

		 $sql54 = "Insert into aspen_hist_tbl_billdetails (
		   nBillNo,dBillDate, vIRnumber, fTotalWeight, fWeightAmount, fServiceTax, fEduTax, fSHEduTax, fGrantTotal, nScrapSent, vOutLorryNo, nPartyId, vBillType, BillStatus, ntotalpcs, ntotalamount, ocwtamount, ocwidthamount, oclengthamount,vAdditionalChargeType,fAmount,nsubtotal,grandtot_words,fStatus)
		  VALUES('". $billid. "',now(),'". $partyid. "','". $totalweight_check. "','". $totalrate. "','". $txtservicetax. "','". $txteductax. "','". $txtsecedutax. "','". $txtgrandtotal. "','". $txtscrap. "','". $txtoutward_num. "',(SELECT aspen_tblpartydetails.nPartyId  FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),'Directbilling','Billing',0,'0','0','0','0','". $txtadditional_type. "','". $txtamount_mt. "','". $totalamt. "','". $container. "' , 'Billed Completely')";

		$sql12="UPDATE aspen_tblbillingstatus SET vBillingStatus='Billed' WHERE vIRnumber='".$partyid."'";
		$sql13="UPDATE aspen_tblinwardentry SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";

		$sql83="UPDATE aspen_hist_tblinwardentry SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";

		$sql185="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.billedweight = aspen_tblinwardentry.billedweight + ( select (aspen_tblbilldetails.fTotalWeight * 1000)
		from aspen_tblbilldetails
		where aspen_tblbilldetails.nBillNo='".$billid."'  )
		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql88="UPDATE aspen_hist_tblinwardentry
		SET aspen_hist_tblinwardentry.billedweight=
		aspen_hist_tblinwardentry.billedweight + ( select (aspen_tblbilldetails.fTotalWeight) * 1000
		from aspen_tblbilldetails
		where aspen_tblbilldetails.nBillNo='".$billid."'  )
		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";
	//	echo $sql179;

		$sql14="UPDATE aspen_hist_tblinwardentry
		SET aspen_hist_tblinwardentry.fpresent=
		aspen_hist_tblinwardentry.fpresent - $wei
		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql66="UPDATE aspen_tblinwardentry
				SET aspen_tblinwardentry.fpresent=
				aspen_tblinwardentry.fpresent - $wei
				where aspen_tblinwardentry.vIRnumber='".$partyid."'";

		$sql87="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.dBillDate=(select CURDATE())
		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql86= "UPDATE aspen_hist_tblinwardentry
		SET aspen_hist_tblinwardentry.dBillDate=(select CURDATE())
		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";


		$sql33="Select fpresent from aspen_tblinwardentry where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$query83 = $this->db->query($sql83);
		$query85 = $this->db->query($sql185);
		$query88 = $this->db->query($sql88);
		$query87 = $this->db->query($sql87);
		$query86 = $this->db->query($sql86);
		$query = $this->db->query($sql);
		$query54 = $this->db->query($sql54);
		$query1 = $this->db->query($sql12);

		$query4 = $this->db->query($sql13);
		$query33 = $this->db->query($sql33);
		$query85 = $this->db->query($sql185);
		$query88 = $this->db->query($sql88);
		$query3 = $this->db->query($sql14);
		$query66 = $this->db->query($sql66);
			
		$strSql = "select ai.*,ap.*,am.* from aspen_tblinwardentry as ai 
		left join aspen_tblmatdescription as am on ai.nMatId = am.nMatId 
		left join aspen_tblpartydetails as ap on ap.nPartyId = ai.nPartyId
		where ai.vIRnumber = '$partyid'";
		$strquery = $this->db->query($strSql);

		if($strquery->result()[0]->nBillingUpdates) {
			sendSMS($strquery->result()[0]->nBillingUpdates,'Bill raised for coil no '.$partyid.'%n'.$strquery->result()[0]->vDescription.' '.$strquery->result()[0]->fThickness.'mm x '.$strquery->result()[0]->fWidth.'mm%nAppx weight '.$totalweight_check.'kgs%nLoaded in '.$txtoutward_num.'%nOn:'.date('d/m/Y').'%nInvoice no:'.$billid.'%nDriver no:'.$driverContact);
		}

        if($strquery->result()[0]->vemailaddress) {
            $strEmailHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                            <title>Slitting bill for coil number '.$partyid.'</title>
                            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                            <meta name="viewport" content="width=device-width, initial-scale=1.0 " />
                            <style>
                            </style>
                            </head>';

            $strEmailHtml .= '<h4>Dear Customer,</h4>';
            $strEmailHtml .= '<h4>A Bill has been generated for coil number '.$partyid.'. The following info is for your  perusal:</h4>';
            $strEmailHtml .= '<table style="width:80%; border-collapse: collapse;" cellpadding="5">
                            <tr>
                                <td style="border: 1px solid black;">Coil Number</td>
                                <td style="border: 1px solid black;">'.$partyid.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Material Description</td>
                                <td style="border: 1px solid black;">'.$strquery->result()[0]->vDescription.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Thickness</td>
                                <td style="border: 1px solid black;">'.$strquery->result()[0]->fThickness.' mm</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Width</td>
                                <td style="border: 1px solid black;">'.$strquery->result()[0]->fWidth.' mm</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Appx weight</td>
                                <td style="border: 1px solid black;">'.$totalweight_check.' M/T</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Loaded in vehicle no</td>
                                <td style="border: 1px solid black;">'.$txtoutward_num.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">On</td>
                                <td style="border: 1px solid black;">'.date('d/m/Y').'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Invoice no</td>
                                <td style="border: 1px solid black;">'.$billid.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Driver no</td>
                                <td style="border: 1px solid black;">'.$driverContact.'</td>
                            </tr>
                          </table>';

            $strEmailHtml .= '<p>For '.$this->companyData->company_name.'.</p>
                          <p>Please contact our unit coordinator for any clarification.</p>
                          <p>Customer Service team<br/>
                          Unit 2 (Bidadi)<br/>
                          8217766390/7008898426</p>';

            $strEmailHtml .= '<p style="color:#999999;">This is a system generated mail. Please reply to aspen.bidadi@gmail.com for more details.</p>';

            $pdfname = $this->billing_direct($billid,$partyid, $_POST['gstType'], true);
            sendEmail($strquery->result()[0]->vemailaddress, 'Bill generated for coil number '.$partyid, $strEmailHtml, $pdfname);
        }

	}



	/*function semibill($billid,$partyid,$pname,$cust_add,$cust_rm,$mat_desc,$thic,$wid,$len,$wei,$inv_no,$totalweight_check,$totalrate,$totalamt,$txthandling,$txtadditional_type,$txtamount_mt,$txtoutward_num,$txtscrap,$txtservicetax,$txteductax,$txtsecedutax,$txtgrandtotal,$container,$txtnsubtotal){
		$sql = "Insert into aspen_tblbilldetails (nBillNo,
		   dBillDate, vIRnumber, fTotalWeight, fWeightAmount, fServiceTax, fEduTax, fSHEduTax, fGrantTotal, nScrapSent, vOutLorryNo, nPartyId, vBillType, BillStatus, ntotalpcs, ntotalamount, ocwtamount, ocwidthamount, oclengthamount,vAdditionalChargeType,fAmount,nsubtotal,grandtot_words)
		  VALUES('". $billid. "',now(),'". $partyid. "','". $totalweight_check. "','". $totalrate. "','". $txtservicetax. "','". $txteductax. "','". $txtsecedutax. "','". $txtgrandtotal. "','". $txtscrap. "','". $txtoutward_num. "',(SELECT aspen_tblpartydetails.nPartyId  FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),'SemiFinished','Billing',0,'0','0','0','0','". $txtadditional_type. "','". $txtamount_mt. "','". $txtnsubtotal. "','". $container. "')";

		$sql54 = "Insert into aspen_hist_tbl_billdetails (nBillNo,
		   dBillDate, vIRnumber, fTotalWeight, fWeightAmount, fServiceTax, fEduTax, fSHEduTax, fGrantTotal, nScrapSent, vOutLorryNo, nPartyId, vBillType, BillStatus, ntotalpcs, ntotalamount, ocwtamount, ocwidthamount, oclengthamount,vAdditionalChargeType,fAmount,nsubtotal,grandtot_words)
		  VALUES('". $billid. "',now(),'". $partyid. "','". $totalweight_check. "','". $totalrate. "','". $txtservicetax. "','". $txteductax. "','". $txtsecedutax. "','". $txtgrandtotal. "','". $txtscrap. "','". $txtoutward_num. "',(SELECT aspen_tblpartydetails.nPartyId  FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),'SemiFinished','Billing',0,'0','0','0','0','". $txtadditional_type. "','". $txtamount_mt. "','". $txtnsubtotal. "','". $container. "')";


		$sql12="UPDATE aspen_tblbillingstatus SET vBillingStatus='Billed' WHERE vIRnumber='".$partyid."'";
		$sql19="UPDATE aspen_tblcuttinginstruction SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";

		$sql14="UPDATE aspen_tblinwardentry
		SET fpresent= 0
		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql83="UPDATE aspen_hist_tblinwardentry
		SET fpresent= 0
		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";


		$sql87="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.dBillDate=(select CURDATE())
		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql86= "UPDATE aspen_hist_tblinwardentry
		SET aspen_hist_tblinwardentry.dBillDate=(select CURDATE())
		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";


		$sql1334="UPDATE aspen_tblinwardentry SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";
		$sql834="UPDATE aspen_hist_tblinwardentry SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";

		$sql33="Select fpresent from aspen_tblinwardentry where aspen_tblinwardentry.vIRnumber='".$partyid."' ";


			$query83 = $this->db->query($sql83);
		$query87 = $this->db->query($sql87);
		$query86= $this->db->query($sql86);

		$query334 = $this->db->query($sql1334);
		$query834 = $this->db->query($sql834);

		$query = $this->db->query($sql);
		$query54 = $this->db->query($sql54);
		$query1 = $this->db->query($sql12);
		$query2 = $this->db->query($sql19);
		$query3 = $this->db->query($sql14);
		$query33 = $this->db->query($sql33);

	}*/


	function semibill($billid,$partyid,$pname,$cust_add,$cust_rm,$mat_desc,$thic,$wid,$len,$wei,$inv_no,$totalweight_check,$totalrate,$totalamt,$txthandling,$txtadditional_type,$txtamount_mt,$txtoutward_num,$txtscrap,$txtservicetax,$txteductax,$txtsecedutax,$txtgrandtotal,$container,$txtnsubtotal){
		$sql = "Insert into aspen_tblbilldetails (nBillNo,
		   dBillDate, vIRnumber, fTotalWeight, fWeightAmount, fServiceTax, fEduTax, fSHEduTax, fGrantTotal, nScrapSent, vOutLorryNo, nPartyId, vBillType, BillStatus, ntotalpcs, ntotalamount, ocwtamount, ocwidthamount, oclengthamount,vAdditionalChargeType,fAmount,nsubtotal,grandtot_words)
		  VALUES('". $billid. "',now(),'". $partyid. "','". $totalweight_check. "','". $totalrate. "','". $txtservicetax. "','". $txteductax. "','". $txtsecedutax. "','". $txtgrandtotal. "','". $txtscrap. "','". $txtoutward_num. "',(SELECT aspen_tblpartydetails.nPartyId  FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),'SemiFinished','Billing',0,'0','0','0','0','". $txtadditional_type. "','". $txtamount_mt. "','". $txtnsubtotal. "','". $container. "')";

		$sql54 = "Insert into aspen_hist_tbl_billdetails (nBillNo,
		   dBillDate, vIRnumber, fTotalWeight, fWeightAmount, fServiceTax, fEduTax, fSHEduTax, fGrantTotal, nScrapSent, vOutLorryNo, nPartyId, vBillType, BillStatus, ntotalpcs, ntotalamount, ocwtamount, ocwidthamount, oclengthamount,vAdditionalChargeType,fAmount,nsubtotal,grandtot_words,fStatus)
		  VALUES('". $billid. "',now(),'". $partyid. "','". $totalweight_check. "','". $totalrate. "','". $txtservicetax. "','". $txteductax. "','". $txtsecedutax. "','". $txtgrandtotal. "','". $txtscrap. "','". $txtoutward_num. "',(SELECT aspen_tblpartydetails.nPartyId  FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),'SemiFinished','Billing',0,'0','0','0','0','". $txtadditional_type. "','". $txtamount_mt. "','". $txtnsubtotal. "','". $container. "' , 'Billed Completely')";


		$sql12="UPDATE aspen_tblbillingstatus SET vBillingStatus='Billed' WHERE vIRnumber='".$partyid."'";
		$sql19="UPDATE aspen_tblcuttinginstruction SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";


	/*	$sql14="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.fpresent=
		aspen_tblinwardentry.fQuantity -(select (aspen_tblinwardentry.fpresent + (aspen_tblbilldetails.fTotalWeight)*1000 )
		from aspen_tblbilldetails
		LEFT JOIN aspen_tblbilldetails  ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
		where aspen_tblbilldetails.nBillNo='".$billid."'  )
		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql83="UPDATE aspen_hist_tblinwardentry
		SET aspen_hist_tblinwardentry.fpresent=
		aspen_hist_tblinwardentry.fQuantity-( (aspen_hist_tblinwardentry.fpresent) + (select (aspen_tblbilldetails.fTotalWeight)*1000
		from aspen_tblbilldetails
		where aspen_tblbilldetails.nBillNo='".$billid."'  ) )
		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";
	//	echo $sql83;*/

		$sql87="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.dBillDate=(select CURDATE())
		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql86= "UPDATE aspen_hist_tblinwardentry
		SET aspen_hist_tblinwardentry.dBillDate=(select CURDATE())
		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";


		$sql1334="UPDATE aspen_tblinwardentry SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";
		$sql834="UPDATE aspen_hist_tblinwardentry SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";

		$sql33="Select fpresent from aspen_tblinwardentry where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql1477="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.billedweight = aspen_tblinwardentry.billedweight + ( select (aspen_tblbilldetails.fTotalWeight * 1000)
		from aspen_tblbilldetails
		where aspen_tblbilldetails.nBillNo='".$billid."'  )
		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		//echo $sql1477;

		$sql144="UPDATE aspen_hist_tblinwardentry
		SET aspen_hist_tblinwardentry.billedweight=
		aspen_hist_tblinwardentry.billedweight + ( select (aspen_tblbilldetails.fTotalWeight) * 1000
		from aspen_tblbilldetails
		where aspen_tblbilldetails.nBillNo='".$billid."'  )
		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";



			$sql83="UPDATE aspen_hist_tblinwardentry
		SET aspen_hist_tblinwardentry.fpresent=
		aspen_hist_tblinwardentry.fQuantity  - aspen_hist_tblinwardentry.billedweight

		where aspen_hist_tblinwardentry.vIRnumber='".$partyid."' ";
		//echo $sql83;

	$sql14="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.fpresent=
		aspen_tblinwardentry.fQuantity  - aspen_tblinwardentry.billedweight

		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

	//	echo $sql14;

		$query87 = $this->db->query($sql87);
		$query86= $this->db->query($sql86);

		$query334 = $this->db->query($sql1334);
		$query834 = $this->db->query($sql834);

		$query = $this->db->query($sql);
		$query54 = $this->db->query($sql54);
		$query1 = $this->db->query($sql12);
		$query2 = $this->db->query($sql19);

		$query33 = $this->db->query($sql33);
		$query466 = $this->db->query($sql1477);
			$query3366 = $this->db->query($sql144);
			$query83 = $this->db->query($sql83);
		$query3 = $this->db->query($sql14);
	}



	function recoilcancel($partyid){
		$sql12="UPDATE aspen_tblbillingstatus SET vBillingStatus='Ready To Bill' WHERE vIRnumber='".$partyid."'";

		$sql13="UPDATE aspen_tblrecoiling SET vStatus='Ready To Bill' WHERE vIRnumber='".$partyid."'";

		$sql15="UPDATE aspen_tblinwardentry SET vStatus='Ready To Bill' WHERE vIRnumber='".$partyid."'";

		$query = $this->db->query($sql15);
		$query1 = $this->db->query($sql12);
		$query2 = $this->db->query($sql13);
	}
	function cuttingcancel($billid,$pid,$presentwei,$actualnumberbundle){
		$sql = $this->db->query("DELETE FROM aspen_tblbilldetails WHERE nBillNo='".$billid."'");
		$sql12="UPDATE aspen_tblbillingstatus SET vBillingStatus='Ready To Bill' WHERE vIRnumber='".$pid."' AND nSno IN (".$actualnumberbundle.") ";
	//	echo $sql12;
		$sql13="UPDATE aspen_tblcuttinginstruction SET vStatus='Ready To Bill' WHERE vIRnumber='".$pid."' AND nSno  IN (".$actualnumberbundle.")  ";
	//	echo $sql13;
		$sql15="UPDATE aspen_tblinwardentry SET vStatus='Ready To Bill' WHERE vIRnumber='".$pid."'";
		$sql16="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.fpresent=
		'".$presentwei."'+(select (SUM( aspen_tblbillingstatus.fbilledWeight ) *1000)
		from aspen_tblbillingstatus
		where aspen_tblbillingstatus.vIRnumber='".$pid."'  group by aspen_tblbillingstatus.vIRnumber)
		where aspen_tblinwardentry.vIRnumber='".$pid."' ";
		$sql17="UPDATE aspen_tblbillingstatus
		SET aspen_tblbillingstatus.nActualNo= 0
		where aspen_tblbillingstatus.vIRnumber='".$pid."'  ";



		$query = $this->db->query($sql15);
		$query1 = $this->db->query($sql12);
		$query2 = $this->db->query($sql13);
		$query3= $this->db->query($sql16);
		$query4= $this->db->query($sql17);
	}

	function directbillcancel($billid,$pid,$wei){
		$sql = $this->db->query("DELETE FROM aspen_tblbilldetails WHERE nBillNo='".$billid."'");
		$sql15="UPDATE aspen_tblinwardentry SET vStatus='RECEIVED'  WHERE vIRnumber='".$pid."'";
		$sql16="UPDATE aspen_tblinwardentry SET fpresent='".$wei."'  WHERE vIRnumber='".$pid."'";
		$sql17="UPDATE aspen_hist_tblinwardentry SET vStatus='RECEIVED'  WHERE vIRnumber='".$pid."'";
		$sql18="UPDATE aspen_hist_tblinwardentry SET fpresent='".$wei."'  WHERE vIRnumber='".$pid."'";

		$sql144="UPDATE aspen_hist_tblinwardentry SET dBillDate = dReceivedDate  WHERE vIRnumber='".$pid."'";
		$sql145="UPDATE aspen_tblinwardentry SET dBillDate = dReceivedDate  WHERE vIRnumber='".$pid."'";

		$sql186="UPDATE aspen_hist_tblinwardentry SET billedweight=(fQuantity - fpresent) WHERE vIRnumber='".$pid."'";
		$sql187="UPDATE aspen_tblinwardentry SET billedweight=(fQuantity - fpresent ) WHERE vIRnumber='".$pid."'";
		$query = $this->db->query($sql15);
		$query = $this->db->query($sql16);
		$query17 = $this->db->query($sql17);
		$query18 = $this->db->query($sql18);
		$query44 = $this->db->query($sql144);
		$query45 = $this->db->query($sql145);
		$query86 = $this->db->query($sql186);
		$query87 = $this->db->query($sql187);
	}
	function semicancelbill($billid,$pid,$wei){
		$sql = $this->db->query("DELETE FROM aspen_tblbilldetails WHERE nBillNo='".$billid."'");
		$sql15="UPDATE aspen_tblinwardentry SET vStatus='Work In Progress'  WHERE vIRnumber='".$pid."'";
		$sql16="UPDATE aspen_tblinwardentry  SET
		fpresent='".$wei."' WHERE aspen_tblinwardentry.vIRnumber='".$pid."'";
		$query = $this->db->query($sql15);
		$query = $this->db->query($sql16);
	}
	function actualweight($pid){
	$sqlfb = "Select fQuantity from aspen_tblinwardentry as weight where vIRnumber='".$pid."'";
		$query = $this->db->query($sqlfb);
		$arr='';
		if ($query->num_rows() > 0) {
		 	foreach ($query->result() as $row)
			{
				$arr[] =$row;
			}
		}
		return $arr;
	}
	function slittingcancel($partyid){
		$sql12="UPDATE aspen_tblbillingstatus SET vBillingStatus='Ready To Bill' WHERE vIRnumber='".$partyid."'";

		$sql13="UPDATE aspen_tblslittinginstruction  SET vStatus='Ready To Bill' WHERE vIRnumber='".$partyid."'";

		$sql15="UPDATE aspen_tblinwardentry SET vStatus='Ready To Bill' WHERE vIRnumber='".$partyid."'";

		$query = $this->db->query($sql15);
		$query1 = $this->db->query($sql12);
		$query2 = $this->db->query($sql13);
	}
	function semifinishedcancel($partyid){
		$sql12="UPDATE aspen_tblbillingstatus SET vBillingStatus='Ready To Bill' WHERE vIRnumber='".$partyid."'";

		$sql13="UPDATE aspen_tblcuttinginstruction SET vStatus='Ready To Bill' WHERE vIRnumber='".$partyid."'";

		$sql15="UPDATE aspen_tblinwardentry SET vStatus='Ready To Bill' WHERE vIRnumber='".$partyid."'";

		$query = $this->db->query($sql15);
		$query1 = $this->db->query($sql12);
		$query2 = $this->db->query($sql13);
	}

	function functionpdfrecoilprint($billid,$partyid,$pname,$cust_add,$cust_rm,$mat_desc,$thic,$wid,$len,$wei,$inv_no,$totalweight_check,$totalrate,$totalamt,$txthandling,$txtadditional_type,$txtamount_mt,$txtoutward_num,$txtscrap,$txtservicetax,$txteductax,$txtsecedutax,$txtgrandtotal,$container,$txtnsubtotal){
		$sql = "Insert into aspen_tblbilldetails (
		   nBillNo,dBillDate, vIRnumber, fTotalWeight, fWeightAmount, fServiceTax, fEduTax, fSHEduTax, fGrantTotal, nScrapSent, vOutLorryNo, nPartyId, vBillType, BillStatus, ntotalpcs, ntotalamount, ocwtamount, ocwidthamount, oclengthamount,vAdditionalChargeType,fAmount,nsubtotal,grandtot_words)
		  VALUES('". $billid. "',now(),'". $partyid. "','0','0','". $txtservicetax. "','". $txteductax. "','". $txtsecedutax. "','". $txtgrandtotal. "','". $txtscrap. "','". $txtoutward_num. "',(SELECT aspen_tblpartydetails.nPartyId  FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),'Recoiling','Billing',0,'0','0','0','0','". $txtadditional_type. "','". $txtamount_mt. "','". $txtnsubtotal. "','". $container. "')";

		$sql12="UPDATE aspen_tblbillingstatus SET vBillingStatus='Billed' WHERE vIRnumber='".$partyid."'";

		$sql13="UPDATE aspen_tblrecoiling SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";

		$sql15="UPDATE aspen_tblinwardentry SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";

		$sql14="UPDATE aspen_tblinwardentry
		SET aspen_tblinwardentry.fpresent=
		aspen_tblinwardentry.fQuantity-(select (SUM( aspen_tblbillingstatus.fbilledWeight ) *1000)
		from aspen_tblbillingstatus
		where aspen_tblbillingstatus.vIRnumber='".$partyid."'  group by aspen_tblbillingstatus.vIRnumber)
		where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$sql33="Select fpresent from aspen_tblinwardentry where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$query = $this->db->query($sql);
		$query1 = $this->db->query($sql12);
		$query2 = $this->db->query($sql13);
		$query3 = $this->db->query($sql14);
		$query5 = $this->db->query($sql15);
		$query33 = $this->db->query($sql33);

	}

	function functionpdfslittingprint($billid,$partyid,$pname,$cust_add,$cust_rm,$mat_desc,$thic,$wid,$len,$wei,$inv_no,$totalweight_check,$totalrate,$totalamt,$txthandling,$txtadditional_type,$txtamount_mt,$txtoutward_num,$txtscrap,$txtservicetax,$txteductax,$txtsecedutax,$txtgrandtotal,$container,$txtslitsubtotal,$bundleIds,$txtadditional_type1,$txtamount_mt1,$driverContact){

		$sqlServiceTaxNAddressDetails = "select nPercentage from aspen_tbltaxdetails where vTypeOfTax = 'SERVICE TAX'
											union
										select CONCAT_WS('-',CONCAT_WS(',', 'To M/s.',nPartyName,vAddress1,vAddress2,vCity),nPinId) from aspen_tblpartydetails
										right join aspen_tblinwardentry on aspen_tblinwardentry.nPartyId = aspen_tblpartydetails.nPartyId
										where  aspen_tblinwardentry.vIRnumber='".$partyid."'";

		$resObjServiceTaxDetails = $this->db->query($sqlServiceTaxNAddressDetails);
		$serviceTaxPercent = $resObjServiceTaxDetails->result()[0]->nPercentage;
		$strBillingAddress = $resObjServiceTaxDetails->result()[1]->nPercentage;

		$sql = "Insert into aspen_tblbilldetails (
		   nBillNo,dBillDate, vIRnumber, fTotalWeight, fWeightAmount, fServiceTax, fEduTax, fSHEduTax, fGrantTotal, nScrapSent, vOutLorryNo, nPartyId, vBillType, BillStatus, ntotalpcs, ntotalamount, ocwtamount, ocwidthamount, oclengthamount,vAdditionalChargeType,fAmount,nsubtotal,grandtot_words,dFinalRate, nServiceTaxPercent,tBillingAddress,vAdditionalChargeType1,fAmount1)
		  VALUES('". $billid. "',now(),'". $partyid. "','". $totalweight_check. "','". $totalrate. "','". $txtservicetax. "','". $txteductax. "','". $txtsecedutax. "','". $txtgrandtotal. "','". $txtscrap. "','". $txtoutward_num. "',(SELECT aspen_tblpartydetails.nPartyId  FROM aspen_tblpartydetails where aspen_tblpartydetails.nPartyName = '". $pname. "'),'Slitting','Billing',0,$totalamt,'0','0','0','". $txtadditional_type. "','". $txtamount_mt. "','". $txtslitsubtotal. "','". $container. "','".$totalrate."',$serviceTaxPercent,'".$strBillingAddress."','".$txtadditional_type1."','".$txtamount_mt1."')";

		$sql12="UPDATE aspen_tblbillingstatus SET vBillingStatus='Billed' WHERE vIRnumber='".$partyid."' and nSno IN ( $bundleIds )";

		$sql22="UPDATE aspen_tblinwardentry SET vStatus='Billed' WHERE vIRnumber='".$partyid."'";

		$sql14="UPDATE aspen_tblinwardentry SET aspen_tblinwardentry.fpresent = aspen_tblinwardentry.fpresent - $totalweight_check where aspen_tblinwardentry.vIRnumber='".$partyid."'";

		$sql15="UPDATE aspen_tblinwardentry SET aspen_tblinwardentry.billedweight = aspen_tblinwardentry.billedweight + $totalweight_check where aspen_tblinwardentry.vIRnumber='".$partyid."'";

		$sqlBundleBillingAssociationInsert = "Insert into aspen_tblBillBundleAssociation(nBundleNumber,nNoOfPcs,fbilledWeight,nBillNumber) select nSno as nBundleNumber, nActualNo as nNoOfPcs, fbilledWeight as fbilledWeight, $billid from aspen_tblbillingstatus where nSno IN ( $bundleIds ) and vIRnumber='".$partyid."'";

		$sql33="Select fpresent from aspen_tblinwardentry where aspen_tblinwardentry.vIRnumber='".$partyid."' ";

		$query = $this->db->query($sql);
		$query1 = $this->db->query($sql12);
		$query2 = $this->db->query($sql22);
		$query3 = $this->db->query($sql14);
		$query4 = $this->db->query($sql15);
		$queryBundleBillingAssociationInsert = $this->db->query($sqlBundleBillingAssociationInsert);
		$query33 = $this->db->query($sql33);
		
		$strSql = "select ai.*,ap.*,am.* from aspen_tblinwardentry as ai 
		left join aspen_tblmatdescription as am on ai.nMatId = am.nMatId 
		left join aspen_tblpartydetails as ap on ap.nPartyId = ai.nPartyId
		where ai.vIRnumber = '".$partyid."'";
		$strquery = $this->db->query($strSql);

        $strBundleSql = "select nWidth,count(nWidth) as count,sum(nWeight) as sum from aspen_tblBillBundleAssociation as abb  left join aspen_tblslittinginstruction as asi on asi.nSno = abb.nBundleNumber where abb.nBillNumber = $billid and asi.vIRnumber = '".$partyid."' and asi.nSno in ($bundleIds) group by nWidth,nLength;";

        $queryBundle = $this->db->query($strBundleSql);
        if ($queryBundle->num_rows() > 0) {
            $index = 1;
            $strBundle = '';
            $strBundle1 = '';

            foreach($queryBundle->result() as $key => $row) {
                $strBundle .= '%n'.$index.') '.$row->nWidth.'mm - '.$row->count.'Nos - '.($row->sum).'kgs';
                $strBundle1 .= $index.') '.$row->nWidth.'mm - '.$row->count.'Nos - '.($row->sum).'kgs';
                $index++;
            }
        }
        if($strquery->result()[0]->nBillingUpdates) {
			sendSMS($strquery->result()[0]->nBillingUpdates,'Bill raised for coil no '.$partyid.'%n'.$strquery->result()[0]->vDescription.' '.$strquery->result()[0]->fThickness.'mm x '.$strquery->result()[0]->fWidth.'mm'.$strBundle.'%nAppx weight '.$totalweight_check.'kgs%nLoaded in '.$txtoutward_num.'%nOn:'.date('d/m/Y').'%nInvoice no:'.$billid.'%nDriver no:'.$driverContact);
		}

        if($strquery->result()[0]->vemailaddress) {
            $strEmailHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                            <title>Slitting bill for coil number '.$partyid.'</title>
                            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                            <meta name="viewport" content="width=device-width, initial-scale=1.0 " />
                            <style>
                            </style>
                            </head>';

            $strEmailHtml .= '<h4>Dear Customer,</h4>';
            $strEmailHtml .= '<h4>A Bill has been generated for coil number '.$partyid.'. The following info is for your  perusal:</h4>';
            $strEmailHtml .= '<table style="width:80%; border-collapse: collapse;" cellpadding="5">
                            <tr>
                                <td style="border: 1px solid black;">Coil Number</td>
                                <td style="border: 1px solid black;">'.$partyid.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Material Description</td>
                                <td style="border: 1px solid black;">'.$strquery->result()[0]->vDescription.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Thickness</td>
                                <td style="border: 1px solid black;">'.$strquery->result()[0]->fThickness.' mm</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Width</td>
                                <td style="border: 1px solid black;">'.$strquery->result()[0]->fWidth.' mm</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Appx weight</td>
                                <td style="border: 1px solid black;">'.$totalweight_check.' M/T</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Loaded in vehicle no</td>
                                <td style="border: 1px solid black;">'.$txtoutward_num.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">On</td>
                                <td style="border: 1px solid black;">'.date('d/m/Y').'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Invoice no</td>
                                <td style="border: 1px solid black;">'.$billid.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Driver no</td>
                                <td style="border: 1px solid black;">'.$driverContact.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Bill Details</td>
                                <td style="border: 1px solid black;word-wrap: break-word;overflow-wrap: break-word;">'.$strBundle1.'</td>
                            </tr>
                          </table>';

            $strEmailHtml .= '<p>For '.$this->companyData->company_name.'.</p>
                          <p>Please contact our unit coordinator for any clarification.</p>
                          <p>Customer Service team<br/>
                          Unit 2 (Bidadi)<br/>
                          8217766390/7008898426</p>';

            $strEmailHtml .= '<p style="color:#999999;">This is a system generated mail. Please reply to aspen.bidadi@gmail.com for more details.</p>';

            $pdfname = $this->slittingpdf($partyid, $billid, $_POST['gstType'], true);
            sendEmail($strquery->result()[0]->vemailaddress, 'Bill generated for coil number '.$partyid, $strEmailHtml, $pdfname);
        }
	}

	function savebundlemodel(){
		if(isset($txtbundleweight) && isset($pid) && isset($txtbundleids)) {
			$bundleweight =  $_POST['txtbundleweight'];
			$pid =  $_POST['pid'];
			$txtbundleids =  $_POST['txtbundleids'];
		}
	$sqlbbd= $this->db->query("Select aspen_tblbillingstatus.nActualNo into aspen_tblbilldetails    (dBillDate,vIRnumber,fTotalWeight,fWeightAmount,fServiceTax,fEduTax,fSHEduTax,fGrantTotal,nScrapSent,vOutLorryNo,nPartyId,vBillType,BillStatus) VALUES (
  now() ,'". $_POST['pid']. "','". $_POST['txtbundleweight']. "','0','0','0','0','0','0','0','0','0','0' )");
	}


function finalbillgeneratemodel($partyid='',$actualnumberbundle='',$cust_add='',$cust_rm='',$billid='',$gstType, $returnPdf = false) {
	$sqlbilling= "select aspen_tblbilldetails.nBillNo as billnumber,
	DATE_FORMAT(aspen_tblbilldetails.dBillDate, '%d/%m/%Y') as billdate,
	aspen_tblpartydetails.nPartyName as partyname,
	aspen_tblpartydetails.nTinNumber as tinnmber,
	aspen_tblpartydetails.nCgstNumber as cgstNumber,
	aspen_tblpartydetails.vAddress1 as address1,
	aspen_tblpartydetails.vAddress2 as address2,
	aspen_tblpartydetails.vCity as city,
	aspen_tblpartydetails.nPinId as pincode,
	aspen_tblbilldetails.vOutLorryNo as trucknumber,
	aspen_tblbilldetails.vBillType as billType,
	aspen_tblmatdescription.vDescription as materialdescription, aspen_tblbillingstatus.fWeight as wei, aspen_tblinwardentry.vInvoiceNo as invoiceno,DATE_FORMAT(aspen_tblinwardentry.dInvoiceDate, '%d/%m/%Y') as invoicedate ,aspen_tblinwardentry.fWidth as width,aspen_tblinwardentry.fThickness as thickness,aspen_tblbillingstatus.nSno as Sno,aspen_tblbillingstatus.nActualNo as Length,aspen_tblpricetype1.nAmount as rate,aspen_tblbillingstatus.nActualNo as noofpcs,
	aspen_tblbillingstatus.fbilledWeight as weight,aspen_tblbilldetails.ntotalpcs as totalpcs,aspen_tblbilldetails.fTotalWeight as totalweight,round(aspen_tblbilldetails.fWeightAmount+ '".$cust_add."'- '".$cust_rm."' ) as weihtamount,aspen_tblbilldetails.ntotalamount as totalamount,aspen_tblbilldetails.nScrapSent as Scrapsent,round(aspen_tblbilldetails.ocwtamount) as wtamount,round(aspen_tblbilldetails.ocwidthamount) as widthamount,aspen_tblbilldetails.oclengthamount as lengthamount,round(aspen_tblbilldetails.fServiceTax) as servicetax,round(aspen_tblbilldetails.fEduTax) as edutax,aspen_tblbilldetails.fSHEduTax as shedutax,aspen_tblbilldetails.fGrantTotal as grandtotal,aspen_tblbilldetails.vAdditionalChargeType as additionalchargetype,round(aspen_tblbilldetails.fAmount) as amount,round(aspen_tblbilldetails.nsubtotal) as subtotal,aspen_tblbilldetails.grandtot_words as container from aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
	left join aspen_tblpricetype1 on aspen_tblpricetype1.nMatId=aspen_tblmatdescription.nMatId
	left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
	LEFT JOIN aspen_tblbilldetails ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
	LEFT JOIN aspen_tbladditionalbillchargetype ON aspen_tbladditionalbillchargetype.nBillNo=aspen_tblbilldetails.nBillNo where aspen_tblinwardentry.vIRnumber ='".$partyid."' and  aspen_tblbilldetails.nBillNo='".$billid."'";

		$querymain = $this->db->query($sqlbilling);

		$serviceTaxPercent = $this->getServiceTaxPercent();

		$billnumber = $querymain->row(0)->billnumber;
		$billdate = $querymain->row(0)->billdate;
		$invoice =$partyid;
		$party_name = $querymain->row(0)->partyname;
		$billType = $querymain->row(0)->billType;
		$width = $querymain->row(0)->width;
		$thickness = $querymain->row(0)->thickness;
		$invoicedate = $querymain->row(0)->invoicedate;
		$address_one = $querymain->row(0)->address1;
		$address_two = $querymain->row(0)->address2;
		$invoiceno = $querymain->row(0)->invoiceno;
		$city = $querymain->row(0)->city;
		$pincode = $querymain->row(0)->pincode;
		$trucknumber = $querymain->row(0)->trucknumber;
		$material_descriptio = $querymain->row(0)->materialdescription;
		$additionalchargetype = $querymain->row(0)->additionalchargetype;
		$amount = $querymain->row(0)->amount;
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
		$wei = $querymain->row(0)->wei;
		$cgstNumber = $querymain->row(0)->cgstNumber;

		if($gstType == 'Within') {
			$gstSection	= '<tr>
							<td width="89%">
							<h3><b>CGST @ 9%</b></h3>
							</td> <td><h3>'.($servicetax/2).'</h3></td>
						</tr>
						<tr>
							<td width="89%">
							<h3><b>SGST @ 9%</b></h3>
							</td> <td><h3>'.($servicetax/2).'</h3></td>
						</tr>';

		} else if($gstType == 'Inter') {
			$gstSection	= '<tr>
							<td width="89%">
							<h3><b>IGST @ 18%</b></h3>
							</td> <td><h3>'.ceil($servicetax).'</h3></td>
						</tr>';
		}

		$sqlitem ="select Distinct aspen_tblbillingstatus.nSno as bundlenumber,aspen_tblcuttinginstruction.nLength as length,aspen_tblbillingstatus.nActualNo as noofpcs,aspen_tblbillingstatus.fWeight as wei,aspen_tblbillingstatus.fbilledWeight as weight  from aspen_tblcuttinginstruction
	LEFT JOIN aspen_tblbillingstatus  ON aspen_tblcuttinginstruction.vIRnumber=aspen_tblbillingstatus.vIRnumber
	WHERE aspen_tblcuttinginstruction.nSno = aspen_tblbillingstatus.nSno and  aspen_tblbillingstatus.vIRnumber='".$partyid."'  and aspen_tblbillingstatus.nSno IN( ".$actualnumberbundle.") order by aspen_tblbillingstatus.nSno asc";

		$queryitem = $this->db->query($sqlitem);
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdfname= 'bill_'.$partyid.'.pdf';
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

	$html = companyHeader().'<hr>
		<table>
			<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=3 width=100></td>
			</tr>
			<tr>
				<td width="30%" align:"left"><h3>Billnumber : '.$billnumber.'</h3></td>
				<td width="51%" align="center"><h3>Coilnumber : '.$invoice.'</h3></td>
				<td width="33.33%" align:"right"><h3>Billdate : '.$billdate.'</h3></td>
			</tr>
			<tr><td></td></tr>
			<tr>
				<td width="30%" align:"left">
					<h3>To M/s., &nbsp;'.$party_name.', '.$address_one.' &nbsp;'.$address_two.',&nbsp;'.$city.'-'.$pincode.'<br>
					Tin Number : '.$tin_number.'
					</h3>
				</td>
				<td width="40%" align="center"><h3> Desp. By Lorry No. : '.$trucknumber.'</h3> </td>
				<td width="33.33%" align:"right"><h3>Delivery: Full &nbsp; Part-1&nbsp; Part-2</h3></td>
			</tr>
			<tr><td></td></tr>
			<tr>
				<td width="30%" align:"left">
					<h3>CGST Number : '.$cgstNumber.'</h3>
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
		<tr><td colspan="3"><b>Service Accounting Code '.getServiceAccountingCode($billType).' </b></td></tr>
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

				<td style="font-weight:bold" width="13%"><h3>Total</h3></td>
				<td style="font-weight:bold" width="23%"></td>
				<td style="font-weight:bold" width="16.6%"><h3>'.$totalpcs.'</h3></td>
				<td style="font-weight:bold" width="33%"><h3>'.round($totalweight,3).'</h3></td>
				<td style="font-weight:bold" width="15.6%"><h3>'.$totalamount.'</h3></td>
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
		'.$gstSection.'
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
					<h3>Received the above goods in good condition.</h3>
				</td>
				<td width="30%"><h3>For '.$this->companyData->company_name.'.</h3></td>
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
		if($returnPdf) {
            $pdf->Output($pdfname, 'F');
            return $pdfname;
        } else
            $pdf->Output($pdfname, 'I');
	}

	function billgeneratemodel( $coilno='',$partyname='',$description='',$lorryno='',$thic='',$wid='',$totalpcs='',$totalweight='',$totamount='',$actualnumberbundle='',$partyid='') {
	$sqlrpt = "select aspen_tblbilldetails.vOutLorryNo as lorryno,
	aspen_tblbilldetails.fTotalWeight as totalweight,
	aspen_tblbilldetails.ntotalpcs as totalpcs,
	aspen_tblbilldetails.ntotalamount as totamount,
	aspen_tblpartydetails.nPartyName as partyname,
	aspen_tblmatdescription.vDescription as description,
	aspen_tblinwardentry.fWidth as wid,
	aspen_tblinwardentry.fThickness as thic,
	aspen_tblinwardentry.vIRnumber as coilno
	from aspen_tblinwardentry
		LEFT JOIN aspen_tblbilldetails  ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
		LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId
		LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
		where
    aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$coilno."'";

		$querymain = $this->db->query($sqlrpt);

	$sqlbundle ="select Distinct aspen_tblbillingstatus.nSno as bundlenumber,aspen_tblcuttinginstruction.nLength as length,aspen_tblbillingstatus.nActualNo as noofpcs,aspen_tblbillingstatus.fbilledWeight as weight  from aspen_tblcuttinginstruction
	LEFT JOIN aspen_tblbillingstatus  ON aspen_tblcuttinginstruction.vIRnumber=aspen_tblbillingstatus.vIRnumber
	WHERE aspen_tblcuttinginstruction.nSno = aspen_tblbillingstatus.nSno and  aspen_tblbillingstatus.vIRnumber='".$partyid."'  and aspen_tblbillingstatus.nSno IN( ".$actualnumberbundle.") order by aspen_tblbillingstatus.nSno asc";
	$queryitem = $this->db->query($sqlbundle);

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdfname= 'loadingslip_'.$partyname.'.pdf';
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

		$html = '

		<table align="center" width="100%" cellspacing="0" cellpadding="5"  border="0.1">
			<tr>

				<td width="100%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>LOADING SLIP</h1></td>

		</tr>
			<tr>
				<td width="30%" align="left">
				<h1><b>Party Name: </b></h1></td><td align="left" width="70%"><h1> '.$partyname.'</h1></td>
			</tr>
			<tr>
				<td align="left">
				<h1><b>Coil Number: </b></h1></td><td align="left"><h1> '.$coilno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Material Description: </b></h1></td><td align="left"><h1> '.$description.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Lorry Number: </b></h1></td><td align="left"><h1> '.$lorryno.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Width:</b> </h1></td><td align="left"><h1>'.$wid.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Thickness : </b></h1></td><td align="left"><h1> '.$thic.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Pieces :</b></h1></td><td align="left"><h1> '.$totalpcs.'</h1></td>
			</tr>
			<tr>
				<td align="left">
					<h1><b>Total Weight : </b></h1></td><td align="left"><h1> '.$totalweight.' Tonnes</h1></td>
			</tr>
			</table>';

			$html .= '
		<table cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td align="left">&nbsp;</td>
				<td align="right">&nbsp;</td>
			</tr>

		</table>';
		  $html .= '
			<table align="center" cellspacing="0" cellpadding="5" border="1px" width="100%">
		<tr>
				<th style="font-weight:bold;" width="25%"><h1>Sl. No.</h1></th>
				<th style="font-weight:bold"  width="25%"><h1>DESCRIPTION</h1></th>
				<th style="font-weight:bold"  width="25%"><h1>No. Of Pcs</h1></th>
				<th style="font-weight:bold" width="25%"><h1>Qty. In M/T</h1></th>

			</tr>';

		if ($queryitem->num_rows() > 0)
		{
			foreach($queryitem->result() as $rowitem)
			{
		$html .= '
			<tr>
				<td><h1>'.$rowitem->bundlenumber.'</h1></td>
				<td><h1>LENGTH &nbsp;&nbsp;&nbsp;'.$rowitem->length.'</h1></td>
				<td><h1>'.$rowitem->noofpcs.'</h1></td>
				<td><h1>'.$rowitem->weight.'</h1></td>

			</tr>';
			}
		}else{
		$html .= '
			<tr>
				<td align="center">&nbsp;</td>
				<td align="center">&nbsp;</td>
				<td align="center" >&nbsp;</td>
				<td align="center">&nbsp;</td>
				<td align="right">&nbsp;</td>
			</tr>';
		}
		$html .= '

		</table>';


  	$html .= '
		<table cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td align="left">&nbsp;</td>
				<td align="right">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" align="center" style="font-size:45px; font-style:italic; font-family: fantasy;"><b>**ASPEN BANGALORE**</b></td>
			</tr>
		</table>';

		$pdf->writeHTML($html, true, 0, true, true);
		$pdf->Ln();
		$pdf->lastPage();
		$pdf->Output($pdfname, 'I');
	}


		function recoilpdf($partyid='') {
	$sqlbilling= "select aspen_tblbilldetails.nBillNo as billnumber,DATE_FORMAT(aspen_tblbilldetails.dBillDate, '%d-%m-%Y') as billdate,aspen_tblpartydetails.nPartyName as partyname,aspen_tblpartydetails.nTinNumber as tinnmber,aspen_tblpartydetails.vAddress1 as address1,aspen_tblpartydetails.vAddress2 as address2,aspen_tblpartydetails.vCity as city,aspen_tblbilldetails.vOutLorryNo as trucknumber,aspen_tblmatdescription.vDescription as materialdescription,aspen_tblinwardentry.vInvoiceNo as invoiceno,DATE_FORMAT(aspen_tblinwardentry.dInvoiceDate, '%d-%m-%Y') as invoicedate ,aspen_tblinwardentry.fWidth as width,aspen_tblinwardentry.fThickness as thickness,aspen_tblbillingstatus.nSno as Sno,aspen_tblbillingstatus.nActualNo as Length,aspen_tblpricetype1.nAmount as rate,aspen_tblbillingstatus.nActualNo as noofpcs,
	aspen_tblbillingstatus.fbilledWeight as weight,aspen_tblbilldetails.ntotalpcs as totalpcs,aspen_tblbilldetails.fTotalWeight as totalweight,round(aspen_tblbilldetails.fWeightAmount) as weihtamount,aspen_tblbilldetails.ntotalamount as totalamount,aspen_tblbilldetails.nScrapSent as Scrapsent,round(aspen_tblbilldetails.ocwtamount) as wtamount,round(aspen_tblbilldetails.ocwidthamount) as widthamount,aspen_tblbilldetails.oclengthamount as lengthamount,round(aspen_tblbilldetails.fServiceTax) as servicetax,round(aspen_tblbilldetails.fEduTax) as edutax,aspen_tblbilldetails.fSHEduTax as shedutax,aspen_tblbilldetails.fGrantTotal as grandtotal,aspen_tblbilldetails.vAdditionalChargeType as additionalchargetype,round(aspen_tblbilldetails.fAmount) as amount,round(aspen_tblbilldetails.nsubtotal) as subtotal,aspen_tblbilldetails.grandtot_words as container from aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
	left join aspen_tblpricetype1 on aspen_tblpricetype1.nMatId=aspen_tblmatdescription.nMatId
	left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
	LEFT JOIN aspen_tblbilldetails ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
	LEFT JOIN aspen_tbladditionalbillchargetype ON aspen_tbladditionalbillchargetype.nBillNo=aspen_tblbilldetails.nBillNo where aspen_tblinwardentry.vIRnumber ='".$partyid."' LIMIT 1 ";

	$sqlrecoil ="select Distinct aspen_tblbillingstatus.nSno as recoilnumber,aspen_tblrecoiling.nNoOfRecoils as recoil,aspen_tblrecoiling.nNoOfRecoils as nBundleweight  from aspen_tblrecoiling
	LEFT JOIN aspen_tblbillingstatus  ON aspen_tblrecoiling.vIRnumber=aspen_tblbillingstatus.vIRnumber
	WHERE aspen_tblrecoiling.nSno = aspen_tblbillingstatus.nSno and  aspen_tblbillingstatus.vIRnumber='".$partyid."' order by aspen_tblbillingstatus.nActualNo desc";
	$queryrecoil = $this->db->query($sqlrecoil);

		$querymain = $this->db->query($sqlbilling);


		$billnumber = $querymain->row(0)->billnumber;
		$billdate = $querymain->row(0)->billdate;
		$invoice =$partyid;
		$party_name = $querymain->row(0)->partyname;
		$width = $querymain->row(0)->width;
		$thickness = $querymain->row(0)->thickness;
		$invoicedate = $querymain->row(0)->invoicedate;
		$address_one = $querymain->row(0)->address1;
		$address_two = $querymain->row(0)->address2;
		$invoiceno = $querymain->row(0)->invoiceno;
		$city = $querymain->row(0)->city;
		$trucknumber = $querymain->row(0)->trucknumber;
		$material_descriptio = $querymain->row(0)->materialdescription;
		$additionalchargetype = $querymain->row(0)->additionalchargetype;
		$amount = $querymain->row(0)->amount;
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


				$html = '
		<table cellspacing="0" cellpadding="4" border="0">
			<tr>
				<td align="left">
					 <h3>'.$billnumber.'</h3>
				</td>
				<td align="right">
					 '.$billdate.'
				</td>
			</tr>

			</table>
			<table cellspacing="0" cellpadding="1" >
			<tr>
				<td align="left">
					<h3>'.$party_name.'</h3>
				</td>
			</tr>
			</table>
			<table width="500px" cellspacing="0" cellpadding="1">
			<tr>
				<td width="350px" align="left">
					'.$address_one.'
				</td>

				<td width="150px" align="right">
					'.$trucknumber.'
				</td>
			</tr>
			</table>
			<table cellspacing="0" cellpadding="3" >
			<tr>
				<td align="left">
					'.$address_two.'
				</td>
			</tr>
			<tr>
				<td align="left">
					'.$city.'
				</td>
			</tr>
			<tr>
				<td align="left">
					<h3>'.$tin_number.'</h3>
				</td>
			</tr>
			<tr>
				<td align="right">
					'.$invoiceno.'
				</td>
			</tr>

			<tr>
				<td>
				&nbsp;&nbsp;
				</td>
			</tr>

			<tr>
				<td width="500px" align="right">
				'.$invoicedate.'
				</td>
			</tr>
			<tr>
				<td>
				&nbsp;&nbsp;
				</td>
			</tr>
			<tr>
				<td align="left">
					Coil Number:&nbsp;&nbsp; <b>'.$invoice.'</b>
				</td>
			</tr>
			<tr>
				<td align="left">
					'.$material_descriptio.'</td>
				</tr>
				</table>
				<table width="100px" cellspacing="0" cellpadding="1">
			<tr>
				<td width="40px" align="left">
					'.$width.'
				</td>
			<td width="20px" align="right">
					*
				</td>
				<td width="40px" align="right">
					'.$thickness.'
				</td>
			</tr>

			</table>
			<table width="230px" cellspacing="0" cellpadding="1">
			<tr>
				<td width="200px" align="left">
					<h4>Handling / Processing charges:</h4>
				</td>
			</tr>

				<tr>
				<td width="200px" align="left">
					&nbsp;
				</td>
				<td width="30px" align="right">
				&nbsp;
				</td>
			</tr>

			</table>';

		  $html .= '
		<table cellspacing="0" cellpadding="5" border="0px" width="800px">
			';
		if ($queryrecoil->num_rows() > 0)
		{
			foreach($queryrecoil->result() as $rowitem)
			{
		$html .= '
			<tr>
				<td width="100px">'.$rowitem->recoilnumber.'</td>
				<td width="100px">RECOIL</td>
				<td width="100px">'.$rowitem->recoil.'</td>
				<td width="100px">'.$rowitem->nBundleweight.'(in M/T)</td>

			</tr>';
			}
		}else{
		$html .= '
			<tr>
				<td align="center">&nbsp;</td>
				<td align="center">&nbsp;</td>
				<td align="center" >&nbsp;</td>
			</tr>';
		}

		$html .= '
		<table width="800px" cellspacing="0" cellpadding="5" border="0">


		<tr>
		<td width="200px"></td>
		<td><hr width=100%></td>
		<td></td>
		<td></td></tr>

		<tr>
			<td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</td>
			</tr>

			<tr>

				<td align="left">
					<b>Scrap pieces: </b>
				</td>
				<td align="right">
				'.$Scrapsent.'
				</td>
		</tr>

		<tr>
				<td width="420px" align="left">
				<b>Additional Charges: </b>
				</td>

				<td align="right">
				'.$additionalchargetype.'
				</td>
		</tr>
		<tr>
				<td width="420px" align="left">
				<b>Strapping Charge </b>
				</td>

				<td align="right">
				'.$amount.'
				</td>
		</tr>


		<tr>
				<td width="450px" border="0" align="left">
				</td>

				<td><hr width=100%></td>

		</tr>
		<tr>
				<td width="500px" border="0" align="left">
				<b>subtotal </b>
				</td>

				<td >
				'.$subtotal.'&nbsp;&nbsp;
				</td>
		</tr>
		<tr>
				<td width="500px" border="0" align="left">
				<b>Service Tax</b>
				</td>

				<td >
				'.$servicetax.'&nbsp;&nbsp;
				</td>
		</tr>
		<tr>
				<td width="500px" border="0" align="left">
				<b>Edu Tax</b>
				</td>

				<td >
				'.$edutax.'&nbsp;&nbsp;
				</td>
		</tr>
		<tr>
				<td width="500px" border="0" align="left">
				<b>SheEdu Tax</b>
				</td>

				<td >
				'.$shedutax.'&nbsp;&nbsp;
				</td>
		</tr>
		<tr>
				<td width="500px" border="0" align="left">
				<b>Grand Total</b>
				</td>

				<td >
				'.$grandtotal.'&nbsp;&nbsp;
				</td>
		</tr>
		<tr>

				<td width="200px" border="0" align="left">
				<b>Grand Total In words</b>
				</td>

				<td width="400px" border="0" align="left">
				'.$container.'&nbsp; Only&nbsp;
				</td>
		</tr>
		</table>';
		$pdf->writeHTML($html, true, 0, true, true);
		$pdf->Ln();
		$pdf->lastPage();
		$pdf->Output($pdfname, 'I');
	}

	function slittingpdf($partyid='',$billnumber='',$gstType, $returnPdf = false) {

		$sqlbilling= "select aspen_tblbilldetails.nBillNo as billnumber,
		DATE_FORMAT(aspen_tblbilldetails.dBillDate, '%d-%m-%Y') as billdate,
		aspen_tblpartydetails.nPartyName as partyname,
		aspen_tblpartydetails.nTinNumber as tinnmber,
		aspen_tblpartydetails.nCgstNumber as cgstNumber,
		aspen_tblpartydetails.vAddress1 as address1,
		aspen_tblpartydetails.vAddress2 as address2,
		aspen_tblpartydetails.vCity as city,
		aspen_tblbilldetails.vOutLorryNo as trucknumber,
		aspen_tblbilldetails.vBillType as billType,
		aspen_tblmatdescription.vDescription as materialdescription,
		aspen_tblinwardentry.vInvoiceNo as invoiceno,
		DATE_FORMAT(aspen_tblinwardentry.dInvoiceDate, '%d-%m-%Y') as invoicedate,
		aspen_tblinwardentry.fWidth as width,
		aspen_tblinwardentry.fThickness as thickness,
		aspen_tblbillingstatus.nSno as Sno,
		aspen_tblbillingstatus.nActualNo as Length,
		aspen_tblpricetype1.nAmount as rate,
		aspen_tblbillingstatus.nActualNo as noofpcs,
		DATE_FORMAT(aspen_tblinwardentry.dReceivedDate, '%d-%m-%Y') as inwardDate,
		aspen_tblbillingstatus.fbilledWeight as weight,aspen_tblbilldetails.ntotalpcs as totalpcs,aspen_tblbilldetails.fTotalWeight as totalweight,round(aspen_tblbilldetails.fWeightAmount) as weihtamount,aspen_tblbilldetails.ntotalamount as totalamount,aspen_tblbilldetails.nScrapSent as Scrapsent,round(aspen_tblbilldetails.ocwtamount) as wtamount,round(aspen_tblbilldetails.ocwidthamount) as widthamount,aspen_tblbilldetails.oclengthamount as lengthamount,round(aspen_tblbilldetails.fServiceTax) as servicetax,round(aspen_tblbilldetails.fEduTax) as edutax,aspen_tblbilldetails.fSHEduTax as shedutax,aspen_tblbilldetails.fGrantTotal as grandtotal,aspen_tblbilldetails.vAdditionalChargeType as additionalchargetype,round(aspen_tblbilldetails.fAmount) as amount,aspen_tblbilldetails.vAdditionalChargeType1 as additionalchargetype1,round(aspen_tblbilldetails.fAmount1) as amount1,round(aspen_tblbilldetails.nsubtotal) as subtotal,aspen_tblbilldetails.grandtot_words as container,aspen_tblbilldetails.nServiceTaxPercent as serviceTaxPercent
		from aspen_tblinwardentry
		LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId
		LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
		left join aspen_tblpricetype1 on aspen_tblpricetype1.nMatId=aspen_tblmatdescription.nMatId
		left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
		LEFT JOIN aspen_tblbilldetails ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
		LEFT JOIN aspen_tbladditionalbillchargetype ON aspen_tbladditionalbillchargetype.nBillNo=aspen_tblbilldetails.nBillNo where aspen_tblinwardentry.vIRnumber ='".$partyid."' and aspen_tblbilldetails.nBillNo = $billnumber LIMIT 1 ";

		$querymain = $this->db->query($sqlbilling);

		$billnumber = $querymain->row(0)->billnumber;
		$billdate = $querymain->row(0)->billdate;
		$invoice =$partyid;
		$party_name = $querymain->row(0)->partyname;
		$width = $querymain->row(0)->width;
		$billType = $querymain->row(0)->billType;
		$thickness = $querymain->row(0)->thickness;
		$invoicedate = $querymain->row(0)->invoicedate;
		$address_one = $querymain->row(0)->address1;
		$address_two = $querymain->row(0)->address2;
		$invoiceno = $querymain->row(0)->invoiceno;
		$city = $querymain->row(0)->city;
		$tinnmber = $querymain->row(0)->tinnmber;
		$cgstNumber = $querymain->row(0)->cgstNumber;
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
		$container = $querymain->row(0)->container;
		$serviceTaxPercent = $querymain->row(0)->serviceTaxPercent;

		if($gstType == 'Within') {
			$gstSection	= '
						<tr>
					<td width="555px" border="0" align="left"><b>CGST @ 9%</b></td>
					<td><b>'.($servicetax/2).'</b>&nbsp;&nbsp;</td>
					</tr>
					<tr>
					<td width="555px" border="0" align="left"><b>SGST @ 9%</b></td>
					<td><b>'.($servicetax/2).'</b>&nbsp;&nbsp;</td>
					</tr>';

		} else if($gstType == 'Inter') {
			$gstSection	= '<tr>
					<td width="555px" border="0" align="left"><b>IGST @ 18%</b></td>
					<td><b>'.($servicetax).'</b>&nbsp;&nbsp;</td>
					</tr>';
		}

		$strSqlSlittingBundleDetails = "select aspen_tblBillBundleAssociation.nBundleNumber,
						aspen_tblinwardentry.fThickness,
						aspen_tblmatdescription.vDescription as description,
						round((aspen_tblslittinginstruction.nWeight/1000),3) as weight,
						aspen_tblslittinginstruction.nWidth as width,
						$weihtamount as rate,
						round(( $weihtamount * (aspen_tblslittinginstruction.nWeight/1000) ),2) as amount
						from aspen_tblinwardentry
						left join aspen_tblslittinginstruction on  aspen_tblslittinginstruction.vIRnumber = aspen_tblinwardentry.vIRnumber
						left join aspen_tblBillBundleAssociation on aspen_tblslittinginstruction.nSno = aspen_tblBillBundleAssociation.nBundleNumber
						left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
						where aspen_tblBillBundleAssociation.nBillNumber = $billnumber and aspen_tblinwardentry.vIRnumber='".$partyid."' order by aspen_tblslittinginstruction.nSno";

		$queryBundleDetails = $this->db->query($strSqlSlittingBundleDetails);

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdfname= 'SlittingBill'.$billnumber.'.pdf';
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

		$html = companyHeader().'<table>
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
							<h3>To M/s., &nbsp; '.$party_name.' , '.$address_one.' &nbsp;'.$address_two.',&nbsp;'.$city.'
								<br>Tin Number : '.$tinnmber.'
							</h3>
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
					<td width="30%" align:"left"><h3>CGST Number : '.$cgstNumber.'</h3></td>
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
						<th style="font-weight:bold;" width="10%"><h4>Sl. No.</h4></th>
						<th style="font-weight:bold"  width="30%"><h4>Description</h4></th>
						<th style="font-weight:bold"  width="13%"><h4>Width (in mm)</h4></th>
						<th style="font-weight:bold" width="16.6%"><h4>Qty. In M/T</h4></th>
						<th style="font-weight:bold"  width="16.6%"><h4>Rate per M/T</h4></th>
						<th style="font-weight:bold"  width="16.6%"><h4>Amount</h4></th>
					</tr>
					<tr><td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td></tr>
					<tr><td colspan="3"><b>Service Accounting Code '.getServiceAccountingCode($billType).' </b></td></tr>
					<tr>
						<td width="20%" style="font-weight:bold">Coil Width : '.$width.'</td>
						<td style="font-weight:bold">Coil Thickness : '.$thickness.'</td>
					</tr>';

					if($queryBundleDetails->num_rows() > 0) {
						foreach($queryBundleDetails->result() as $rowitem) {
							$html .= '<tr>
										<td width="10%"><b>'.$rowitem->nBundleNumber.'</b></td>
										<td width="30%"><b>'.$rowitem->description.'</b></td>
										<td width="13%"><b>'.$rowitem->width.'</b></td>
										<td width="16.6%"><b>'.$rowitem->weight.'</b></td>
										<td width="16.6%"><b>'.$rowitem->rate.'</b></td>
										<td width="33%"><b>'.ceil($rowitem->amount).'</b></td>
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
				<td width="160px" align="right"><b>'.$Scrapsent.'</b></td>
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
			'.$gstSection.'
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
				<td width="25%"><b>For '.$this->companyData->company_name.'.</b></td>
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
        if($returnPdf) {
            $pdf->Output($pdfname, 'F');
            return $pdfname;
        } else
            $pdf->Output($pdfname, 'I');
	}

	function billing_direct($billid='',$partyid='',$gstType, $returnPdf = '')
    {
        $sqlrpt = "SELECT 
                aspen_tblbilldetails.vOutLorryNo AS lorryno,
                aspen_tblbilldetails.fTotalWeight AS totalweight,
                aspen_tblbilldetails.ntotalpcs AS totalpcs,
                aspen_tblbilldetails.ntotalamount AS totamount,
                aspen_tblbilldetails.vBillType AS billType,
                aspen_tblpartydetails.nPartyName AS pname,
                aspen_tblmatdescription.vDescription AS description,
                aspen_tblinwardentry.fWidth AS wid,
                aspen_tblinwardentry.fThickness AS thic,
                aspen_tblinwardentry.vIRnumber AS coilno,                
                aspen_tblinwardentry.vInvoiceNo AS inv_no,
                aspen_tblbilldetails.fTotalWeight AS totalweight_check,
                aspen_tblbilldetails.fWeightAmount as txthandling,
                aspen_tblbilldetails.nsubtotal as totalamt,
                aspen_tblbilldetails.fServiceTax as totalservicetax,
                aspen_tblbilldetails.vAdditionalChargeType as txtadditional_type,
                aspen_tblbilldetails.fAmount as txtamount_mt,
                aspen_tblbilldetails.vOutLorryNo as txtoutward_num,   
                aspen_tblbilldetails.nServiceTaxPercent as txtservicetax,
                aspen_tblbilldetails.fGrantTotal as txtgrandtotal,
                aspen_tblbilldetails.grandtot_words as container
            FROM
                aspen_tblbilldetails
                    LEFT JOIN
                aspen_tblinwardentry ON aspen_tblinwardentry.vIRnumber = aspen_tblbilldetails.vIRnumber
                    LEFT JOIN
                aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
                    LEFT JOIN
                aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId
            WHERE
                aspen_tblinwardentry.vIRnumber = '".$partyid."' and nBillNo =$billid";

        $querymain = $this->db->query($sqlrpt);

        $sql1="SELECT 
                    NOW() AS billdate,
                    dReceivedDate AS inwarddate,
                    vAddress1 AS add1,
                    vAddress1 AS add2,
                    vCity AS city,
                    nPinId AS pincode,
                    nTinNumber AS tin_number,
                    nCgstNumber AS cgstNumber
                FROM
                    aspen_tblinwardentry
                        LEFT JOIN
                    aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId
                WHERE
                    aspen_tblinwardentry.vIRnumber = '".$partyid."'";

        $querymain1 = $this->db->query($sql1);

        $serviceTaxPercent = $this->getServiceTaxPercent();

        $inwarddate = date('d-m-Y',strtotime($querymain1->row(0)->inwarddate));
        $tin_number = $querymain1->row(0)->tin_number;
        $cgstNumber = $querymain1->row(0)->cgstNumber;
        $billType = $querymain->row(0)->billType;
        $billdate = date( 'd-m-Y',strtotime($querymain1->row(0)->billdate));
        $add1= $querymain1->row(0)->add1;
        $add2= $querymain1->row(0)->add2;
        $city= $querymain1->row(0)->city;
        $pinCode = $querymain1->row(0)->pincode;
        $mat_desc = $querymain->row(0)->description;
        $thic = $querymain->row(0)->thic;
        $wid = $querymain->row(0)->wid;
        $inv_no = $querymain->row(0)->inv_no;
        $totalweight_check = $querymain->row(0)->totalweight_check;
        $txthandling = $querymain->row(0)->txthandling;
        $totalamt = $querymain->row(0)->totalamt;
        $txtadditional_type = $querymain->row(0)->txtadditional_type;
        $txtamount_mt = $querymain->row(0)->txtamount_mt;
        $txtoutward_num = $querymain->row(0)->txtoutward_num;
        $txtservicetax = $querymain->row(0)->totalservicetax;
        $txtgrandtotal = $querymain->row(0)->txtgrandtotal;
        $container = $querymain->row(0)->container;
        $pname = $querymain->row(0)->pname;

        if($gstType == 'Within') {
            $gstSection	= '<tr>
							<td width="89%">
							<h3><b>CGST @ 9%</b></h3>
							</td> <td><h3>'.($txtservicetax/2).'</h3></td>
						</tr>
						<tr>
							<td width="89%">
							<h3><b>SGST @ 9%</b></h3>
							</td> <td><h3>'.($txtservicetax/2).'</h3></td>
						</tr>';

        } else if($gstType == 'Inter') {
            $gstSection	= '<tr>
							<td width="89%">
							<h3><b>IGST @ 18%</b></h3>
							</td> <td><h3>'.ceil($txtservicetax).'</h3></td>
						</tr>';
        }

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdfname= 'DirectBill'.$partyid.'.pdf';
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

        $html = companyHeader().'
		<table width="100%" cellspacing="0" cellpadding="0" >
			<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=3 width=100></td>
			</tr>
			<tr>
				<td width="30%" align:"left"><h3>Billnumber : '.$billid.'</h3></td>
				<td width="40%" align="center"><h3>Coilnumber : '.$partyid.'</h3></td>
				<td width="33.33%" align:"right"><h3>Billdate : '.$billdate.' </h3></td>
			</tr>
			<tr><td></td></tr>
			<tr>
				<td width="30%" align:"left">
					<h3>To M/s., &nbsp; '.$pname.' , '.$add1.' &nbsp;'.$add2.',&nbsp;'.$city.'-'.$pinCode.'
						<br>Tin Number : '.$tin_number.'
					</h3>
				</td>
				<td width="40%" align="center"><h3> Desp. By Lorry No. : '.$txtoutward_num.'</h3> </td>
				<td width="33.33%" align:"right"><h3>Delivery: Full &nbsp; Part-1&nbsp; Part-2</h3></td>
			</tr>
			<tr><td></td></tr>
			<tr>
				<td width="30%" align:"left">
					<h3>CGST Number : '.$cgstNumber.'</h3>
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
		<tr><td colspan="3"><h3>Service Accounting Code '.getServiceAccountingCode($billType).' </h3></td></tr>
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
				<td style="font-weight:bold"  width="18%"><h3>'.round($totalweight_check,3).'</h3></td>
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
		'.$gstSection.'
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
				<td width="30%"><h3>For '.$this->companyData->company_name.'</h3></td>
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
        if($returnPdf) {
            $pdf->Output($pdfname, 'F');
            return $pdfname;
        } else
            $pdf->Output($pdfname, 'I');
    }

		/*function directbillingmodelpdf($partyid='') {
	$sqlbilling= "select aspen_tblbilldetails.nBillNo as billnumber,DATE_FORMAT(aspen_tblbilldetails.dBillDate, '%d-%m-%Y') as billdate,aspen_tblpartydetails.nPartyName as partyname,aspen_tblpartydetails.nTinNumber as tinnmber,aspen_tblpartydetails.vAddress1 as address1,aspen_tblpartydetails.vAddress2 as address2,aspen_tblpartydetails.vCity as city,aspen_tblbilldetails.vOutLorryNo as trucknumber,aspen_tblmatdescription.vDescription as materialdescription,aspen_tblinwardentry.vInvoiceNo as invoiceno,DATE_FORMAT(aspen_tblinwardentry.dInvoiceDate, '%d-%m-%Y') as invoicedate ,aspen_tblinwardentry.fWidth as width,aspen_tblinwardentry.fThickness as thickness,aspen_tblbillingstatus.nSno as Sno,aspen_tblbillingstatus.nActualNo as Length,aspen_tblpricetype1.nAmount as rate,aspen_tblbillingstatus.nActualNo as noofpcs,
	aspen_tblbillingstatus.fbilledWeight as weight,aspen_tblbilldetails.ntotalpcs as totalpcs,aspen_tblbilldetails.fTotalWeight as totalweight,round(aspen_tblbilldetails.fWeightAmount) as weihtamount,aspen_tblbilldetails.ntotalamount as totalamount,aspen_tblbilldetails.nScrapSent as Scrapsent,round(aspen_tblbilldetails.ocwtamount) as wtamount,round(aspen_tblbilldetails.ocwidthamount) as widthamount,aspen_tblbilldetails.oclengthamount as lengthamount,round(aspen_tblbilldetails.fServiceTax) as servicetax,round(aspen_tblbilldetails.fEduTax) as edutax,aspen_tblbilldetails.fSHEduTax as shedutax,aspen_tblbilldetails.fGrantTotal as grandtotal,aspen_tblbilldetails.vAdditionalChargeType as additionalchargetype,round(aspen_tblbilldetails.fAmount) as amount,round(aspen_tblbilldetails.nsubtotal) as subtotal,aspen_tblbilldetails.grandtot_words as container from aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
	left join aspen_tblpricetype1 on aspen_tblpricetype1.nMatId=aspen_tblmatdescription.nMatId
	left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
	LEFT JOIN aspen_tblbilldetails ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
	LEFT JOIN aspen_tbladditionalbillchargetype ON aspen_tbladditionalbillchargetype.nBillNo=aspen_tblbilldetails.nBillNo where aspen_tblinwardentry.vIRnumber ='".$partyid."' LIMIT 1 ";


		$querymain = $this->db->query($sqlbilling);


		$billnumber = $querymain->row(0)->billnumber;
		$billdate = $querymain->row(0)->billdate;
		$invoice =$partyid;
		$party_name = $querymain->row(0)->partyname;
		$width = $querymain->row(0)->width;
		$thickness = $querymain->row(0)->thickness;
		$invoicedate = $querymain->row(0)->invoicedate;
		$address_one = $querymain->row(0)->address1;
		$address_two = $querymain->row(0)->address2;
		$invoiceno = $querymain->row(0)->invoiceno;
		$city = $querymain->row(0)->city;
		$trucknumber = $querymain->row(0)->trucknumber;
		$material_descriptio = $querymain->row(0)->materialdescription;
		$additionalchargetype = $querymain->row(0)->additionalchargetype;
		$amount = $querymain->row(0)->amount;
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

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdfname= 'directbillingslip_'.$partyid.'.pdf';
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


		$html = '
		<table width="100%"  cellspacing="0" cellpadding="0" border="0">

			<tr>
				<td width="16%" align:"left"><h4>TIN:29730066589</h4></td>
				<td width="70%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>ASPEN STEEL PVT LTD</h1></td>
				<td width="25%" align:"right"><h4>Service Tax Regn. No: (BAS)/AABCA4807HST001</h4></td>
		</tr>

		<tr>
				<td align="center" width="100%"><h4>54/1, 17/18th Km. MEDAHALLI, OLD MADRAS ROAD, Bangalore - 560049, <b> Ph: 6590 4772 / 3200 3260 Email: aspensteel@yahoo.in </b></h4></td>

		</tr>

		<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>

		</tr>


		<tr>
				<td width="100%"></td>
		</tr>
		<tr>
				<td width="30%" align:"left"><h3>Billnumber : '.$billnumber.'</h3></td>
				<td width="40%" align="center"><h3>Coilnumber : '.$invoice.'</h3></td>
				<td width="33.33%" align:"right"><h3>Billdate : '.$billdate.'</h3></td>

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
				<td width="40%" align="center"><h3> Desp. By Lorry No. : '.$trucknumber.'</h3> </td>

				<td width="33.33%" align:"right"><h3>Delivery: Full &nbsp; Part-1&nbsp; Part-2</h3></td>

			</tr>
			<tr>
				<td align="left"></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td width="30%" align:"left">
					<h3>Tin Number : '.$tin_number.'</h3>
				</td>
				<td width="40%" align="center"><h3> Inward Date : 	<b> '.$invoicedate.'</b></h3> </td>
				<td width="33.33%" align:"right"><h3>Inward Challan No.:'.$invoiceno.'</h3></td>
			</tr>
			<tr>
				<td align="left"></td>
				<td></td>
				<td></td>
			</tr>
		</table>';

		$html .= '
		<table cellspacing="0" cellpadding="5" border="0px" width="100%">



		<tr>
				<td style="font-weight:bold;" width="40%"><h2>Handling / Processing charges:</h2></td>
				<td style="font-weight:bold" width="15.6%"><h2></h2></td>

			</tr>

			<tr>
				<th style="font-weight:bold;" width="25%"></th>
				<th style="font-weight:bold;" width="25%"><h2>Weight</h2></th>
				<th style="font-weight:bold" width="25%"><h2>Rate</h2></th>
				<th style="font-weight:bold" width="25%"><h2>Amount</h2></th>

			</tr>
			<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>

		</tr>
		<tr>
		<td width="100px" align="left"><h3>'.$material_descriptio.'</h3></td>
		<td width="40px" align="left"><h3>'.$thickness.'</h3></td>
		<td width="20px" align="right">*</td>
		<td width="70px" align="right"><h3>'.$width.'</h3></td>
		</tr>
			';


			$html .= '

			<tr>
				<td style="font-weight:bold;" width="25%"><h2>Total</h2></td>
				<td style="font-weight:bold" width="25%"><h2>'.$totalweight.'</h2></td>
				<td style="font-weight:bold" width="25%"><h2>'.$weihtamount.'</h2></td>

			</tr>
			<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>

		</tr>
		</table>';

		$html .= '
		<table width="100%" cellspacing="5" cellpadding="5" border="0">
		<tr>
		<td width="89%">
					<h3><b>Scrap pieces: </b></h3>
				</td> <td><h3>'.$Scrapsent.'</h3></td>
		</tr>

		<tr>
		<td width="89%">
					<h3><b>Additional Charge:&nbsp;</b></h3>
				</td> <td><h3>'.$additionalchargetype.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
					<h3><b>Strapping Charge:&nbsp;</b></h3>
				</td> <td><h3>'.$amount.'</h3></td>
		</tr>


		<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>

		</tr>
		<tr>
		<td width="89%">
					<h3><b>SUBTOTAL</b></h3>
				</td> <td><h3>'.$subtotal.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
					<h3><b>Service Tax @ 12%</b></h3>
				</td> <td><h3>'.$servicetax.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
					<h3><b>Edn. Cess @ 2% on Service Tax</b></h3>
				</td> <td><h3>'.$edutax.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
					<h3><b>S. & H. Edn. Cess @ 1% on Service Tax</b></h3>
				</td> <td><h3>'.$shedutax.'</h3></td>
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
				</td> 	<td width="75%"><h3>'.$container.'</h3></td>
		</tr>

		<tr>
			<td width="70%">
				<h3><b>Received the above goods in good condition.</b></h3>
				</td>
				<td width="30%"><h3> For ASPEN STEEL (P) LTD.</h3></td>
		</tr>
		<tr>
			<td>

				</td>
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
	*/

	function semibillingmodelpdf($partyid,$billid,$pname,$cust_add,$cust_rm,$mat_desc,$wid,$thic,$len,$wei,$inv_no,$totalweight_check,
	$totalrate,$totalamt,$txthandling, $txtadditional_type,$txtamount_mt,$txtoutward_num,$txtscrap,$txtservicetax,$txteductax,$txtsecedutax,$txtgrandtotal ,$container,$txtnsubtotal) {
	$sqlrpt = "select aspen_tblbilldetails.vOutLorryNo as lorryno,
	aspen_tblbilldetails.fTotalWeight as totalweight,
	aspen_tblbilldetails.ntotalpcs as totalpcs,
	aspen_tblbilldetails.ntotalamount as totamount,
	aspen_tblbilldetails.vBillType as billType,
	aspen_tblpartydetails.nPartyName as pname,
	aspen_tblmatdescription.vDescription as description,
	aspen_tblinwardentry.fWidth as wid,
	aspen_tblinwardentry.fThickness as thic,
	aspen_tblinwardentry.vIRnumber as coilno
	from aspen_tblinwardentry
		LEFT JOIN aspen_tblbilldetails  ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
		LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId
		LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
		where
    aspen_tblpartydetails.nPartyName='".$pname."' and aspen_tblinwardentry.vIRnumber='".$billid."'";



		$querymain = $this->db->query($sqlrpt);
		$billType = $querymain->row(0)->billType;

			$sql1="Select now() as billdate,
			dReceivedDate as inwarddate,
			vAddress1 as add1,
			vAddress1 as add2,
			vCity as city,
			nTinNumber as tin_number,
			nCgstNumber as cgstNumber
			from aspen_tblinwardentry LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId  where
    aspen_tblpartydetails.nPartyName='".$pname."' ";

		$querymain1 = $this->db->query($sql1);

		$inwarddate = date('d-m-Y',strtotime($querymain1->row(0)->inwarddate));
		$tin_number = $querymain1->row(0)->tin_number;
		$cgstNumber = $querymain1->row(0)->cgstNumber;
		$billdate = date('d-m-Y',strtotime($querymain1->row(0)->billdate));
		$add1= $querymain1->row(0)->add1;
		$add2= $querymain1->row(0)->add2;
		$city= $querymain1->row(0)->city;

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

$html = companyHeader().'<table>
		<tr><td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td></tr>
		<tr><td width="100%"></td></tr>
		<tr>
				<td width="30%" align:"left"><h3>Billnumber : '.$billid.'</h3></td>
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
					<h3>To M/s., &nbsp; '.$pname.' , '.$add1.' &nbsp;'.$add1.',&nbsp;'.$city.'
						<br>Tin Number : '.$tin_number.'
					</h3>
				</td>
				<td width="40%" align="center"><h3> Desp. By Lorry No. : '.$txtoutward_num.'</h3> </td>

				<td width="33.33%" align:"right"><h3>Delivery: Full &nbsp; Part-1&nbsp; Part-2</h3></td>

			</tr>
			<tr>
				<td align="left"></td>
				<td></td>
				<td></td>
			</tr>';

		$html .= '
			<tr>
				<td width="30%" align:"left">
					<h3>CGST Number : '.$cgstNumber.'</h3>
				</td>
				<td width="40%" align="center"><h3> Inward Date : 	'.$inwarddate.'<b> </b></h3> </td>
				<td width="33.33%" align:"right"><h3>Inward Challan No.:'.$inv_no.'</h3></td>
			</tr>';

		$html .= '
			<tr>
				<td align="center">&nbsp;</td>
				<td align="center">&nbsp;</td>
				<td align="center" >&nbsp;</td>
			</tr>';

		$html .= '
		</table>';

		$html .= '
		<table cellspacing="0" cellpadding="5" border="0px" width="100%">
		<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>

		</tr>
		<tr>
				<th style="font-weight:bold;" width="13%"><h3>Sl. No.</h3></th>
				<th style="font-weight:bold"  width="40%"><h3>DESCRIPTION</h3></th>
				<th style="font-weight:bold" width="16.6%"><h3>Qty. In M/T</h3></th>
				<th style="font-weight:bold"  width="16.6%"><h3>Rate per M/T</h3></th>
				<th style="font-weight:bold"  width="16.6%"><h3>Amount</h3></th>

			</tr>
		<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>

		</tr>
		<tr><td colspan="3"><h3>Service Accounting Code '.getServiceAccountingCode($billType).' </h3></td></tr>
		<tr>
		<td width="13%" align="left"><h3>1</h3></td>
		<td width="70px" align="left"><h3>'.$mat_desc.'</h3></td>
		<td width="50px" align="left"><h3>'.$thic.'</h3></td>
		<td width="30px" align="right">*</td>
		<td width="50px" align="right"><h3>'.$wid.'</h3></td>
		<td width="110px" align="right"><h3>'.$totalweight_check.'</h3></td>
		<td width="110px" align="right"><h3>'.$txthandling.'</h3></td>
		</tr>

		</table>';

		$html .= '
		<table width="100%" cellspacing="5" cellpadding="5" border="0">
			<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>

		</tr>
			<tr>
				<td style="font-weight:bold;" width="13%"><h3>Total</h3></td>
				<td style="font-weight:bold"  width="23%"></td>
				<td style="font-weight:bold"  width="16.6%"><h3></h3></td>
				<td style="font-weight:bold" width="18%"><h3>'.$totalweight_check.'</h3></td>
				<td style="font-weight:bold"  width="15.6%"><h3>'.$txthandling.'</h3></td>
				<td style="font-weight:bold"  width="15.6%"><h3>'.$totalamt.'</h3></td>

			</tr>

		<tr>
		<td width="90%">
					<h3><b>Strapping Charge:&nbsp;'.$txtadditional_type.'</b></h3>
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
				</td> <td><h3>'.$txtnsubtotal.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
					<h3><b>Service Tax @ 12%</b></h3>
				</td> <td><h3>'.$txtservicetax.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
					<h3><b>Edn. Cess @ 2% on Service Tax</b></h3>
				</td> <td><h3>'.$txteductax.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
					<h3><b>S. & H. Edn. Cess @ 1% on Service Tax</b></h3>
				</td> <td><h3>'.$txtsecedutax.'</h3></td>
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
		<tr>
			<td>

				</td>
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




	/*function semibillingmodelpdf($partyid='') {
	$sqlbilling= "select aspen_tblbilldetails.nBillNo as billnumber,DATE_FORMAT(aspen_tblbilldetails.dBillDate, '%d-%m-%Y') as billdate,aspen_tblpartydetails.nPartyName as partyname,aspen_tblpartydetails.nTinNumber as tinnmber,aspen_tblpartydetails.vAddress1 as address1,aspen_tblpartydetails.vAddress2 as address2,aspen_tblpartydetails.vCity as city,aspen_tblbilldetails.vOutLorryNo as trucknumber,aspen_tblmatdescription.vDescription as materialdescription,aspen_tblinwardentry.vInvoiceNo as invoiceno,DATE_FORMAT(aspen_tblinwardentry.dInvoiceDate, '%d-%m-%Y') as invoicedate ,aspen_tblinwardentry.fWidth as width,aspen_tblinwardentry.fThickness as thickness,aspen_tblbillingstatus.nSno as Sno,aspen_tblbillingstatus.nActualNo as Length,aspen_tblpricetype1.nAmount as rate,aspen_tblbillingstatus.nActualNo as noofpcs,
	aspen_tblbillingstatus.fbilledWeight as weight,aspen_tblbilldetails.ntotalpcs as totalpcs,aspen_tblbilldetails.fTotalWeight as totalweight,round(aspen_tblbilldetails.fWeightAmount) as weihtamount,aspen_tblbilldetails.ntotalamount as totalamount,aspen_tblbilldetails.nScrapSent as Scrapsent,round(aspen_tblbilldetails.ocwtamount) as wtamount,round(aspen_tblbilldetails.ocwidthamount) as widthamount,aspen_tblbilldetails.oclengthamount as lengthamount,round(aspen_tblbilldetails.fServiceTax) as servicetax,round(aspen_tblbilldetails.fEduTax) as edutax,aspen_tblbilldetails.fSHEduTax as shedutax,aspen_tblbilldetails.fGrantTotal as grandtotal,aspen_tblbilldetails.vAdditionalChargeType as additionalchargetype,round(aspen_tblbilldetails.fAmount) as amount,round(aspen_tblbilldetails.nsubtotal) as subtotal,aspen_tblbilldetails.grandtot_words as container from aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription  ON aspen_tblmatdescription.nMatId=aspen_tblinwardentry.nMatId LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails .nPartyId=aspen_tblinwardentry.nPartyId
	left join aspen_tblpricetype1 on aspen_tblpricetype1.nMatId=aspen_tblmatdescription.nMatId
	left join aspen_tblbillingstatus on aspen_tblinwardentry.vIRnumber=aspen_tblbillingstatus.vIRnumber
	LEFT JOIN aspen_tblbilldetails ON aspen_tblbilldetails.vIRnumber=aspen_tblinwardentry.vIRnumber
	LEFT JOIN aspen_tbladditionalbillchargetype ON aspen_tbladditionalbillchargetype.nBillNo=aspen_tblbilldetails.nBillNo where aspen_tblinwardentry.vIRnumber ='".$partyid."' LIMIT 1 ";
//echo $sqlbilling;

		$querymain = $this->db->query($sqlbilling);


		$billnumber = $querymain->row(0)->billnumber;
		$billdate = $querymain->row(0)->billdate;
		$invoice =$partyid;
		$party_name = $querymain->row(0)->partyname;
		$width = $querymain->row(0)->width;
		$thickness = $querymain->row(0)->thickness;
		$invoicedate = $querymain->row(0)->invoicedate;
		$address_one = $querymain->row(0)->address1;
		$address_two = $querymain->row(0)->address2;
		$invoiceno = $querymain->row(0)->invoiceno;
		$city = $querymain->row(0)->city;
		$trucknumber = $querymain->row(0)->trucknumber;
		$material_descriptio = $querymain->row(0)->materialdescription;
		$additionalchargetype = $querymain->row(0)->additionalchargetype;
		$amount = $querymain->row(0)->amount;
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



				$html = '
		<table width="100%"  cellspacing="0" cellpadding="0" border="0">

			<tr>
				<td width="16%" align:"left"><h4>TIN:29730066589</h4></td>
				<td width="70%"align="center" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>ASPEN STEEL PVT LTD</h1></td>
				<td width="25%" align:"right"><h4>Service Tax Regn. No: (BAS)/AABCA4807HST001</h4></td>
		</tr>

		<tr>
				<td align="center" width="100%"><h4>54/1, 17/18th Km. MEDAHALLI, OLD MADRAS ROAD, Bangalore - 560049, <b> Ph: 6590 4772 / 3200 3260 Email: aspensteel@yahoo.in </b></h4></td>

		</tr>

		<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>

		</tr>


		<tr>
				<td width="100%"></td>
		</tr>
		<tr>
				<td width="30%" align:"left"><h3>Billnumber : '.$billnumber.'</h3></td>
				<td width="40%" align="center"><h3>Coilnumber : '.$invoice.'</h3></td>
				<td width="33.33%" align:"right"><h3>Billdate : '.$billdate.'</h3></td>

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
				<td width="40%" align="center"><h3> Desp. By Lorry No. : '.$trucknumber.'</h3> </td>

				<td width="33.33%" align:"right"><h3>Delivery: Full &nbsp; Part-1&nbsp; Part-2</h3></td>

			</tr>
			<tr>
				<td align="left"></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td width="30%" align:"left">
					<h3>Tin Number : '.$tin_number.'</h3>
				</td>
				<td width="40%" align="center"><h3> Inward Date : 	<b> '.$invoicedate.'</b></h3> </td>
				<td width="33.33%" align:"right"><h3>Inward Challan No.:'.$invoiceno.'</h3></td>
			</tr>
			<tr>
				<td align="left"></td>
				<td></td>
				<td></td>
			</tr>
		</table>';

		$html .= '
		<table cellspacing="0" cellpadding="5" border="0px" width="100%">
		<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>

		</tr>
		<tr>
				<th style="font-weight:bold;" width="13%"><h3>Sl. No.</h3></th>
				<th style="font-weight:bold"  width="22%"><h3>DESCRIPTION</h3></th>
				<th style="font-weight:bold"  width="16.6%"><h3></h3></th>
				<th style="font-weight:bold" width="16.6%"><h3>Qty. In M/T</h3></th>
				<th style="font-weight:bold"  width="16.6%"><h3></h3></th>
				<th style="font-weight:bold"  width="15.6%"><h3>Rate per M/T</h3></th>

			</tr>
		<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>

		</tr>
		<tr>
		<td width="100px" align="left"><h3>1</h3></td>
		<td width="90px" align="left"><h3>'.$material_descriptio.'</h3></td>
		<td width="33px" align="left"><h3>'.$thickness.'</h3></td>
		<td width="20px" align="right">*</td>
		<td width="50px" align="right"><h3>'.$width.'</h3></td>
<td width="70px" align="right"><h3>'.$totalweight.'</h3></td>
		<td width="200px" align="right"><h3>'.$weihtamount.'</h3></td>
		</tr>

';

		$html .= '

		</table>';

		$html .= '
		<table width="100%" cellspacing="5" cellpadding="5" border="0">
			<tr>
				<td align="center" width="100%"><hr color=#00CC33 size=5 width=100></td>

		</tr>
			<tr>
				<td style="font-weight:bold;" width="13%"><h3></h3></td>
				<td style="font-weight:bold"  width="23%"></td>
				<td style="font-weight:bold" width="33%"><h3></h3></td>
				<td style="font-weight:bold"  width="15.6%"><h3></h3></td>


			</tr>

		<tr>
		<td width="89%">
					<h3><b>Strapping Charge:&nbsp;'.$additionalchargetype.'</b></h3>
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
					<h3><b>Service Tax @ 12%</b></h3>
				</td> <td><h3>'.$servicetax.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
					<h3><b>Edn. Cess @ 2% on Service Tax</b></h3>
				</td> <td><h3>'.$edutax.'</h3></td>
		</tr>
		<tr>
		<td width="89%">
					<h3><b>S. & H. Edn. Cess @ 1% on Service Tax</b></h3>
				</td> <td><h3>'.$shedutax.'</h3></td>
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
				</td> 	<td width="75%"><h3>'.$container.'</h3></td>
		</tr>

		<tr>
			<td width="70%">
				<h3><b>Received the above goods in good condition.</b></h3>
				</td>
				<td width="30%"><h3> For ASPEN STEEL (P) LTD.</h3></td>
		</tr>
		<tr>
			<td>

				</td>
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
	}*/

}

class Billings_model extends Base_module_record {

}
