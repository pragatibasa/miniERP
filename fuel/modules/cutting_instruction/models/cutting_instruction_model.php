<?php  
if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class Cutting_instruction_model extends Base_module_model {
	
 	public $required = array('vIRnumber','nSno','dDate','nLength','nNoOfPieces','nTotalWeight');
	protected $key_field = 'vIRnumber';
	
    function __construct()
    {
        parent::__construct('aspen_tblcuttinginstruction');
    }
	
	function form_fields($values = array())
	{
	    $fields = parent::form_fields($values);
		$fields['nPartyName']['label'] = 'Party Name';
		$fields['nMatId']['label'] = 'Material Description';
		$fields['fWidth']['label'] = 'Width';
		$fields['fThickness']['label'] = 'Thickness';
		$fields['fLength']['label'] = 'Length';
		$fields['fQuantity']['label'] = 'Weight';
		$fields['dReceivedDate']= datetime_now();
		$fields['nLength']['label'] = 'Length of a cutting instruction';
		$fields['numbers'] = array('type' => 'enum', 'label' => 'Customer Rate', 'options' => array('yes' => 'Add Discount','no' => 'Remove Discount'), 'required' => TRUE);
		$this->form_builder->set_fields($fields);
	    return $fields;
	}
	
	function formdisplay()
	{
		$fields['nPartyName']['label'] = 'Party Name';
		$fields['nMatId']['label'] = 'Material Description';
		$fields['fWidth']['label'] = 'Width';
		$fields['fThickness']['label'] = 'Thickness';
		$fields['fLength']['label'] = 'Length';
		$fields['fQuantity']['label'] = 'Weight';
		$fields['dReceivedDate']= datetime_now();
		$fields['nLength']['label'] = 'Length of a cutting instruction';
		$fields['numbers'] = array('type' => 'enum', 'label' => 'Customer Rate', 'options' => array('yes' => 'Add Discount','no' => 'Remove Discount'), 'required' => TRUE);
		return $fields;
	}
	
 function savechange(){ 
	$sql = $this->db->query ("UPDATE aspen_tblcuttinginstruction SET vStatus='WIP-Cutting' WHERE vIRnumber='".$_POST['pid']."' and nSno!=0 and ( vStatus = '' OR vStatus = 'WIP-Cutting')");	
	$sql = $this->db->query ("UPDATE aspen_tblinwardentry  SET vprocess='Cutting', vStatus='Work In Progress' WHERE vIRnumber='".$_POST['pid']."'");

	$strSql = "select ai.*,ap.*,am.* from aspen_tblinwardentry as ai 
	left join aspen_tblmatdescription as am on ai.nMatId = am.nMatId 
	left join aspen_tblpartydetails as ap on ap.nPartyId = ai.nPartyId
	where ai.vIRnumber = '".$_POST['pid']."'";
	$query = $this->db->query($strSql);

	$strBundleSql = "select * from aspen_tblcuttinginstruction where vIRnumber = '".$_POST['pid']."'";
	$queryBundle = $this->db->query($strBundleSql);
	if ($queryBundle->num_rows() > 0) {
		$strBundle = '';
		$strBundle1
		$index = 1;
		foreach($queryBundle->result() as $key => $row) {
			$strBundle .= '%n'.$index.') '.$row->nLength.'mm - '.$row->nNoOfPieces.'Nos - '.$row->nBundleweight.'kgs';
            $strBundle1 .= $index.') '.$row->nLength.'mm - '.$row->nNoOfPieces.'Nos - '.$row->nBundleweight.'kgs <br>';
            $index++;
        }
	} 

	if($query->result()[0]->nProcessUpdates) {
		sendSMS($query->result()[0]->nProcessUpdates,'Cutting instruction given for Coil no '.$_POST['pid'].'%n'.$query->result()[0]->vDescription.' '.$query->result()[0]->fThickness.'mm x '.$query->result()[0]->fWidth.'mm%nProcess:CTL'.$strBundle);
	}

     if($query->result()[0]->vemailaddress) {
         $strEmailHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                            <title>Cutting Instruction</title>
                            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                            <meta name="viewport" content="width=device-width, initial-scale=1.0 " />
                            <style>
                            </style>
                            </head>';

         $strEmailHtml .= '<h4>Dear Customer,</h4>';
         $strEmailHtml .= '<h4>Cutting instruction has been given for coil number '.$_POST['pid'].'. The following info is for your  perusal:</h4>';
         $strEmailHtml .= '<table style="width:80%; border-collapse: collapse;" cellpadding="5">
                            <tr>
                                <td style="border: 1px solid black;">Coil Number</td>
                                <td style="border: 1px solid black;">'.$_POST['pid'].'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Material Description</td>
                                <td style="border: 1px solid black;">'.$query->result()[0]->vDescription.'</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Thickness</td>
                                <td style="border: 1px solid black;">'.$query->result()[0]->fThickness.' mm</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Width</td>
                                <td style="border: 1px solid black;">'.$query->result()[0]->fWidth.' mm</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Quantity</td>
                                <td style="border: 1px solid black;">'.$query->result()[0]->fQuantity.' kgs</td>
                            </tr>                            <tr>
                                <td style="border: 1px solid black;">Received Date</td>
                                <td style="border: 1px solid black;">'.$query->result()[0]->dReceivedDate.'</td>
                            </tr>
                            </tr>       
                            <tr>
                                <td style="border: 1px solid black;">Process</td>
                                <td style="border: 1px solid black;">CTL</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid black;">Cutting Details</td>
                                <td style="border: 1px solid black;word-wrap: break-word;overflow-wrap: break-word;">'.$strBundle1.'</td>
                            </tr>
                          </table>';

         $strEmailHtml .= '<p>For Aspen Steel Pvt ltd</p>
                          <p>Please contact our unit coordinator for any clarification.</p>
                          <p>Customer Service team<br/>
                          Unit 2 (Bidadi)<br/>
                          8217766390/7008898426</p>';

         $strEmailHtml .= '<p style="color:#999999;">This is a system generated mail. Please reply to aspen.bidadi@gmail.com for more details.</p>';

         sendEmail($query->result()[0]->vemailaddress, 'Cutting Instruction given for coil number '.$_POST['pid'], $strEmailHtml);
     }

 }
 
function list_items($limit = NULL, $offset = NULL, $col = 'vIRnumber', $order = 'asc') {
	$this->db->select('*');

	$data = parent::list_items($limit, $offset, $col, $order);
	return $data;    	
}
		
function getcoildetails() {		
	$this->save($save);
}
	
	function totalweight_checkmodel($partyid){
	$sqlfb = "select 
	round(sum(nBundleweight),0)as weight from aspen_tblcuttinginstruction
	left join aspen_tblinwardentry on aspen_tblcuttinginstruction.vIRnumber=aspen_tblinwardentry.vIRnumber where aspen_tblinwardentry.vIRnumber='".$partyid."'";
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
	
	function getCuttingInstruction($pid, $pname) {
		if(isset($pid) && isset($pname)) {
			$partyname = $pname;
			$partyid = $pid;
		}
		$sql ="SELECT aspen_tblinwardentry.vIRnumber,aspen_tblinwardentry.fLength, aspen_tblinwardentry.dReceivedDate, aspen_tblmatdescription.vDescription, aspen_tblinwardentry.fThickness, aspen_tblinwardentry.fWidth, aspen_tblinwardentry.fQuantity, aspen_tblinwardentry.vStatus
		FROM aspen_tblinwardentry LEFT JOIN aspen_tblmatdescription ON aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
		LEFT JOIN aspen_tblpartydetails ON aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId ";
		if(!empty($partyid)) {
		$sql.="WHERE aspen_tblinwardentry.vIRnumber='".$partyid."' ";
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

function deleterow($deleteid)
	 {
	 $querycheck = $this->db->query("select * from aspen_tblcuttinginstruction where nSno = '".$deleteid."'");
	 $arr = $querycheck->result();
	 if(!empty($arr)) {
		$sql = $this->db->query("DELETE FROM aspen_tblcuttinginstruction WHERE nSno='".$deleteid."'");
	}
	else{
		return false;
	  }
        }
		
		
 function coillistdetails($partyid = '') 
 {
	$sqlci = "select nSno as bundlenumber, DATE_FORMAT(dDate, '%d-%m-%Y') AS processdate, nLength as length, nNoOfPieces as noofsheets, nBundleweight as weight, vStatus as status, vIRnumber as pnumber from aspen_tblcuttinginstruction WHERE aspen_tblcuttinginstruction.vIRnumber='".$partyid."' and aspen_tblcuttinginstruction.vStatus != 'Billed' and aspen_tblcuttinginstruction.vStatus != 'Ready To Bill' and aspen_tblcuttinginstruction.vStatus != ''";
	//echo $sqlci;
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
	
	function delete_bundlenumber($Bundlenumber='', $Pid='') {
		$sql ="DELETE FROM aspen_tblcuttinginstruction WHERE vIRnumber ='".$Pid."' and nSno = '".$Bundlenumber."'";
		//echo $sql; die();
		$query = $this->db->query($sql);
	}

	 
  
  function weightbundle() {
	$sqlchildweight ="SELECT sum(aspen_tblcuttinginstruction.nBundleweight) as childlength from aspen_tblcuttinginstruction where vIRnumber = '".$_POST['pid']."'"; 
 	$querychildweight = $this->db->query($sqlchildweight);
 	if ($querychildweight->num_rows() > 0) {
    foreach ($querychildweight->result() as $rowcw)
    {
    $childweight =$rowcw->childlength;
    }
    }
 $sqlparentweight ="SELECT fQuantity as parentweight from aspen_tblinwardentry where vIRnumber = '".$_POST['pid']."'"; 
 $queryparentweight = $this->db->query($sqlparentweight);
 if ($queryparentweight->num_rows() > 0)
    {
    foreach ($queryparentweight->result() as $rowpw)
    {
    $parentweight =$rowpw->parentweight;
    }
    }
 
 $leftweight = $parentweight - $childweight;
 
 return  $leftweight;
  }	
  
  
  
  
function savebundle() {
	if(isset( $_POST['pid']) && isset( $_POST['bundlenumber']) && isset( $_POST['date1']) && isset( $_POST['length']) && isset( $_POST['rate'])&& isset( $_POST['bundleweight']) ) {
		$bundlenumber = $_POST['bundlenumber'];
		$date1 = $_POST['date1'];
		$length = $_POST['length'];
		$rate = $_POST['rate'];
		$bundleweight = $_POST['bundleweight'];
		$pid = $_POST['pid'];
	}
	$sql = $this->db->query ("Insert into aspen_tblcuttinginstruction  (vIRnumber,dDate,nLength,nNoOfPieces,nBundleweight,vStatus ) VALUES(  '". $pid. "',
 '". $date1. "','". $length. "','". $rate. "','". $bundleweight. "', 'WIP-Cutting' )");
	$sql1 = $this->db->query ("Insert into aspen_hist_tblcuttinginstruction (vIRnumber,dDate,nLength,nNoOfPieces,nBundleweight ) VALUES(  '". $pid. "',
  '". $date1. "','". $length. "','". $rate. "','". $bundleweight. "' )");
}	
  
    function editbundlemodel(){
	   if(isset( $_POST['bundlenumber']) && isset( $_POST['pid']) && isset( $_POST['length']) && isset( $_POST['rate']) && isset( $_POST['bundleweight'])) {
		$pid = $_POST['pid'];
		$bno = $_POST['bundlenumber'];
		$ln = $_POST['length'];
		$rate = $_POST['rate'];
		$bw = $_POST['bundleweight'];
	 }
		$sql = ("UPDATE aspen_tblcuttinginstruction SET nNoOfPieces='". $rate. "', nLength='". $ln. "', nBundleweight='". $bw. "'");
       		$sql.=" WHERE aspen_tblcuttinginstruction.nSno='".$bno."' and  aspen_tblcuttinginstruction.vIRnumber='".$pid."'";
    		$query1=$this->db->query ($sql);
	  
	 }
	 
	function cancelcoilmodel(){
		if(isset( $_POST['date1'])&& isset( $_POST['bundlenumber'])&& isset( $_POST['pid'])){
		$date1 = $_POST['date1'];
		}
		$sql = ("UPDATE aspen_tblcuttinginstruction SET vStatus='RECEIVED' ");
		$sql.="WHERE aspen_tblcuttinginstruction.vIRnumber='".$_POST['pid']."'";
		$query1=$this->db->query ($sql);
	  
		$sql1 =("UPDATE aspen_tblinwardentry SET vStatus='RECEIVED'  ");
		$sql1.="WHERE vIRnumber='".$_POST['pid']."'";
		$query1=$this->db->query ($sql1);
	  
		$sql2 ="DELETE FROM aspen_tblcuttinginstruction WHERE vIRnumber='".$_POST['pid']."'";
		$query = $this->db->query($sql2);
  
	}	

}

class Cuttinginstructions_model extends Base_module_record {
	
 	
}
