<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/base_module_model.php');
require_once(MODULES_PATH.'/billing_instruction/config/billing_instruction_constants.php');

class Billing_instruction_model extends Base_module_model {
    function __construct()
    {
        parent::__construct('aspen_tblbilldetails');// table name
    }
	
	function example(){
		return true;
	}
	
	function delete_bundlenumber($bundle='',$coilnumber='') {
		$sql ="DELETE FROM aspen_tblbillingstatus WHERE nSno = '".$bundle."' and vIRnumber='".$coilnumber."'";
		$query = $this->db->query($sql);
	}	
	
	 function billintable_model($pid){
		 if(isset( $POST['pid'])) {
			$pid = $POST['pid'];
		  }
		  $sql = "select * from aspen_tblcuttinginstruction "; 
		  if(isset($pid)) {
			$sql.="WHERE aspen_tblcuttinginstruction.vIRnumber='".$pid."'";
		  }
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
	  
	function processchk($pid){
	$sqllv = "SELECT aspen_tblinwardentry.vprocess as process FROM aspen_tblinwardentry WHERE vIRnumber='".$pid."'";
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
	
	function billistdetails($partyid = '') {
	$sqlci = "select aspen_tblbillingstatus.nSno as bundlenumber,aspen_tblcuttinginstruction.nBundleweight
				 as weight,aspen_tblcuttinginstruction.nLength as length,aspen_tblcuttinginstruction.vIRnumber as coilnumber
				,aspen_tblcuttinginstruction.nNoOfPieces as totalnumberofsheets,
				 aspen_tblbillingstatus.nBilledNumber  as noofsheetsbilled
				,aspen_tblbillingstatus.vBillingStatus as billingstatus, 
				aspen_tblbillingstatus.nbalance AS balance,
				 round(nBundleweight - (nBundleweight*nBilledNumber/nNoOfPieces),2) as balanceWeight
				  from aspen_tblcuttinginstruction
				LEFT JOIN aspen_tblbillingstatus  ON aspen_tblcuttinginstruction.vIRnumber=aspen_tblbillingstatus
				.vIRnumber  WHERE  aspen_tblcuttinginstruction.nSno = aspen_tblbillingstatus.nSno and aspen_tblcuttinginstruction
				.vIRnumber='".$partyid."' Group by  aspen_tblbillingstatus.nSno";

	$query = $this->db->query($sqlci);
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
	
	function loadfolderlistslit($partyid = '') {
		$sqlsi = "select aspen_tblslittinginstruction.nSno as slitnumber,
					aspen_tblslittinginstruction.nLength as length,
					aspen_tblslittinginstruction.nWidth as width,
					aspen_tblslittinginstruction.nWeight as weight,
					aspen_tblslittinginstruction.dDate as sdate,
					aspen_tblbillingstatus.vBillingStatus as billingstatus,
					aspen_tblinwardentry.vParentBundleNumber 
				from aspen_tblslittinginstruction
				  LEFT JOIN aspen_tblbillingstatus ON aspen_tblslittinginstruction.vIRnumber=aspen_tblbillingstatus.vIRnumber 
				  left join aspen_tblinwardentry on aspen_tblinwardentry.vParentIRNumber = aspen_tblslittinginstruction.vIRnumber and aspen_tblinwardentry.vParentBundleNumber = aspen_tblslittinginstruction.nSno
			  	WHERE aspen_tblslittinginstruction.nSno = aspen_tblbillingstatus.nSno and aspen_tblslittinginstruction.vIRnumber='".$partyid."' and  aspen_tblslittinginstruction.vStatus =  'Ready To Bill' 
			  	Group by aspen_tblbillingstatus.nSno";

		$query = $this->db->query($sqlsi);
		$arr = '';
		if($query->num_rows() > 0) {
		   foreach($query->result() as $row) {
		      $arr[] =$row;
		   }
		}
		return $arr;
	}
	
	function loadfolderlistrecoil($partyid = '') {
	$sqlsi = "select Distinct aspen_tblrecoiling.nSno as recoilnumber,aspen_tblrecoiling.nNoOfRecoils as noofrecoil,aspen_tblrecoiling.dStartDate as sdate,aspen_tblrecoiling.dEndDate as edate,aspen_tblbillingstatus.nActualNo as noofsheetsbilled ,aspen_tblbillingstatus.vBillingStatus as billingstatus from aspen_tblrecoiling
		  LEFT JOIN aspen_tblbillingstatus  ON aspen_tblrecoiling.vIRnumber=aspen_tblbillingstatus.vIRnumber  WHERE  aspen_tblrecoiling.nSno = aspen_tblbillingstatus.nSno and aspen_tblrecoiling.vIRnumber='".$partyid."' and  aspen_tblrecoiling.vStatus =  'Ready To Bill' Group by  aspen_tblbillingstatus.nSno";
		
	$query = $this->db->query($sqlsi);
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

	
	function billingviewmodel($pid, $pname, $process){
		if(isset($pid) && isset($pname)) {
			$partyname = $pname;
			$partyid = $pid;
		}

		$sqlCheckInwardEntryStatus = 'SELECT vStatus from aspen_tblinwardentry WHERE vIRnumber="'.$partyid.'"';
		$checkInwardsStatusQuery = $this->db->query($sqlCheckInwardEntryStatus );
		$checkInwardsStatusRow = $checkInwardsStatusQuery->result();
		
		if( $checkInwardsStatusRow[0]->vStatus == 'RECEIVED' || $process != '') {
			$sql ="SELECT aspen_tblinwardentry.vIRnumber,aspen_tblmatdescription.vDescription,aspen_tblinwardentry.fThickness,aspen_tblinwardentry.fWidth,aspen_tblinwardentry.fQuantity,aspen_tblinwardentry.vInvoiceNo,aspen_tblinwardentry.vStatus,aspen_tblinwardentry.fpresent
			FROM aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
			LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId ";
			if(!empty($partyname) && !empty($partyid)) {
				$sql.="WHERE aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$partyid."' ";
			}	
		} else {
			$sql = "select inw.vIRnumber,
					aspen_tblmatdescription.vDescription,
					inw.fThickness,
					inw.fWidth,
					abs(round((inw.fpresent - round((t.fQuantity+p.nWeight),2)))) as fQuantity,
					inw.vInvoiceNo,
					inw.vStatus 
					from aspen_tblinwardentry inw 
					LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = inw.nMatId
					LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = inw.nPartyId
					left join (select 
					coalesce(aspen_tblcuttinginstruction.vIRnumber, '$partyid') as vIRnumber,coalesce(round(sum(nBundleweight-(nBundleweight*nBilledNumber/nNoOfPieces)),2),0) as fQuantity from aspen_tblcuttinginstruction left join aspen_tblbillingstatus on aspen_tblcuttinginstruction.vIRnumber = aspen_tblbillingstatus.vIRnumber and aspen_tblcuttinginstruction.nSno = aspen_tblbillingstatus.nSno where aspen_tblcuttinginstruction.vIRnumber ='$partyid') t on t.vIRnumber = inw.vIRnumber
					left join (select coalesce(aspen_tblslittinginstruction.vIRnumber, '$partyid') as vIRnumber,coalesce(sum(nWeight),0) as nWeight from aspen_tblslittinginstruction LEFT JOIN aspen_tblbillingstatus ON aspen_tblslittinginstruction.vIRnumber=aspen_tblbillingstatus.vIRnumber WHERE aspen_tblslittinginstruction.nSno = aspen_tblbillingstatus.nSno and aspen_tblslittinginstruction.vIRnumber='$partyid' and aspen_tblbillingstatus.vBillingStatus != 'Billed') p on p.vIRnumber = inw.vIRnumber 
					where inw.vIRnumber='$partyid'";
		}
		$query = $this->db->query($sql);
		$arr='';
		if ($query->num_rows() > 0) {
		   foreach ($query->result() as $row) {
		      $arr[] =$row;
		   }
		}
		return json_encode($arr[0]);
	}

	function billingsemifinished($pid, $pname){
		if(isset($pid) && isset($pname)) {
			$partyname = $pname;
			$partyid = $pid;
		}
		$sql ="SELECT aspen_tblinwardentry.vIRnumber,  aspen_tblmatdescription.vDescription, aspen_tblinwardentry.fThickness, aspen_tblinwardentry.fWidth, aspen_tblinwardentry.fQuantity,aspen_tblinwardentry.vInvoiceNo, aspen_tblinwardentry.vStatus
		FROM aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
		LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId ";
		if(!empty($partyname) && !empty($partyid)) {
		$sql.="WHERE aspen_tblpartydetails.nPartyName='".$partyname."' and aspen_tblinwardentry.vIRnumber='".$partyid."' ";
		}	
		$query = $this->db->query($sql);
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

	function getParentPartyDetails($partyid) {
		$checkdata = "select count(*) as count from aspen_tblinwardentry where vParentIRNumber = '".$partyid."'";
		$checkquery = $this->db->query($checkdata);
		return $checkquery->result()[0]->count;
	}
} 
class Billinginstruction_model extends Base_module_record {
 
}
