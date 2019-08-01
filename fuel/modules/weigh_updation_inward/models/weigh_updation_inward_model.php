<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/base_module_model.php');
require_once(APPPATH.'helpers/tcpdf/config/lang/eng.php');
require_once(APPPATH.'helpers/tcpdf/tcpdf.php');

class weigh_updation_inward_model extends Base_module_model {

    function __construct() {
        parent::__construct('aspen_tblinwardentry');// table name
    }

    function getInwardVehiclesWithDate($date) {
      $strSql = "select distinct vLorryNo as vehiclenumber from aspen_tblinwardentry where dReceivedDate = '".$date."'";

      $query = $this->db->query($strSql);
      $arr='';
      if ($query->num_rows() > 0) {
        foreach ($query->result() as $row) {
          $arr[] =$row;
        }
      }
      return $arr;
    }

    function getInwardWithVehicleNumber($date, $vehicle) {
      $strSql = "SELECT 
                    aspen_tblinwardentry.*, aspen_tblpartydetails.nPartyName
                FROM
                    aspen_tblinwardentry
                        LEFT JOIN
                    aspen_tblpartydetails ON aspen_tblinwardentry.nPartyId = aspen_tblpartydetails.nPartyId
                WHERE
                    vLorryNo = '".$vehicle."'
                        AND dReceivedDate = '".$date."'";

      $query = $this->db->query($strSql);
  		$arr='';
  		if ($query->num_rows() > 0) {
  			foreach ($query->result() as $row) {
  				$arr[] =$row;
  			}
  		}
  		return $arr;
    }

    function saveInwardWeightment($inputArr) {
      $strInsertOutwardWeightment = "insert into aspen_tblInwardWeighment(date, vehicleNumber, bridgeName, slipNo, loadedWeight, emptyWeight, netWeight, createdDate) values('".$inputArr['date']."', '".$inputArr['vehiclenumber']."', '".$inputArr['weighBridgeName']."', '".$inputArr['slipNo']."', '".$inputArr['loaded-weight']."', '".$inputArr['empty-weight']."', '".$inputArr['net-weight']."',CURDATE())";

      $resInsertOutwardWeightment = $this->db->query($strInsertOutwardWeightment);
      $outwardWeighmentId = mysql_insert_id();

      if($resInsertOutwardWeightment) {
        foreach($inputArr['coilNumbers'] as $key => $coilNumber) {
          $outwardWeighmentBills = "insert into aspen_tblInwardWeighmentCoils
          (outwardId,vIRnumber,inwardWeight,materialWeight,packagingWeight,
          totalAllocatedWeight,differenceWeight) values($outwardWeighmentId,'".$coilNumber."', '".$inputArr['coilWeight'][$key]."','".$inputArr['material_weight'][$key]."',
          '".$inputArr['packing_weight'][$key]."', '".$inputArr['totAllocatedWeight'][$key]."', '".$inputArr['differenceWeight'][$key]."')";

          $resoutwardWeighmentBills = $this->db->query($outwardWeighmentBills);
        }
      }
      echo 'success';exit;
    }
}
class reportsmodel extends Base_module_record {

}
