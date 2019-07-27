<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/base_module_model.php');
require_once(MODULES_PATH.'/reports/config/reports_constants.php');
require_once(APPPATH.'helpers/tcpdf/config/lang/eng.php');
require_once(APPPATH.'helpers/tcpdf/tcpdf.php');

class weigh_updation_model extends Base_module_model {

    function __construct() {
        parent::__construct('aspen_tblinwardentry');// table name
    }

    function getOutwardVehiclesWithDate($date) {
      $strSql = "select distinct vOutLorryNo as vehiclenumber from aspen_tblbilldetails where dBillDate = '".$date."'";

      $query = $this->db->query($strSql);
      $arr='';
      if ($query->num_rows() > 0) {
        foreach ($query->result() as $row) {
          $arr[] =$row;
        }
      }
      return $arr;
    }

    function getBillWithVehicleNumber($date, $vehicle) {
      $strSql = "select aspen_tblbilldetails.*, aspen_tblpartydetails.nPartyName from aspen_tblbilldetails left join aspen_tblpartydetails on aspen_tblbilldetails.nPartyId = aspen_tblpartydetails.nPartyId where vOutLorryNo = '".$vehicle."' and dBillDate = '".$date."'";

      $query = $this->db->query($strSql);
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

    function saveOutwardWeightment($inputArr) {
      $strInsertOutwardWeightment = "insert into outwardWeighment(date, vehicleNumber, bridgeName, slipNo, loadedWeight, emptyWeight, netWeight, createdDate) values('".$inputArr['date']."', '".$inputArr['vehiclenumber']."', '".$inputArr['weighBridgeName']."', '".$inputArr['slipNo']."', '".$inputArr['loaded-weight']."', '".$inputArr['empty-weight']."', '".$inputArr['net-weight']."',CURDATE())";

      $resInsertOutwardWeightment = $this->db->query($strInsertOutwardWeightment);
      $outwardWeighmentId = mysql_insert_id();

      if($resInsertOutwardWeightment) {
        foreach($inputArr['billnumbers'] as $key => $billNumber) {
          $outwardWeighmentBills = "insert into outwardWeighmentBills
          (outwardId,billNo,billWeight,materialWeight,packagingWeight,
          totalAllocatedWeight,differenceWeight) values($outwardWeighmentId,'".$billNumber."', '".$inputArr['billWeight'][$key]."','".$inputArr['material_weight'][$key]."',
          '".$inputArr['packing_weight'][$key]."', '".$inputArr['totAllocatedWeight'][$key]."', '".$inputArr['differenceWeight'][$key]."')";

          $resoutwardWeighmentBills = $this->db->query($outwardWeighmentBills);
        }
      }
      echo 'success';exit;
    }
}
class reportsmodel extends Base_module_record {

}
