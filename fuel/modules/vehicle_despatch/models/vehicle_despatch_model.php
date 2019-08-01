<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/base_module_model.php');

class vehicle_despatch_model extends Base_module_model {
  function __construct() {
      parent::__construct('aspen_tblinwardentry');// table name
  }

  function getOutwardVehiclesWithDate($date) {
    $strSql = "select distinct vehicleNumber as vehiclenumber from outwardWeighment where date = '".$date."'";

    $query = $this->db->query($strSql);
    $arr='';
    if ($query->num_rows() > 0) {
      foreach ($query->result() as $row) {
        $arr[] =$row;
      }
    }
    return $arr;
  }

  function fetchWeighmentsWithDateAndVehicleNumber($date, $vehicleNumber) {
      $strSql = "select netWeight as weight from outwardWeighment where vehicleNumber = '".$vehicleNumber."' and date = '".$date."'";

      $query = $this->db->query($strSql);
      $arr='';
      if ($query->num_rows() > 0) {
          foreach ($query->result() as $row) {
              $arr[] =$row;
          }
      }
      return $arr;
  }

  function displayWeightmentDetails($date, $vehicleNumber, $weight) {
      $strSql = "Select * from outwardWeighment  as ow left join outwardWeighmentBills as owb on ow.id = owb.outwardId 
Left join aspen_tblbilldetails as ab on ab.nBillNo = owb.billNo
Left join aspen_tblinwardentry as ai on ai.vIRnumber = ab.vIRnumber
Left join aspen_tblpartydetails as ap on ap.nPartyId = ai.nPartyId
left join aspen_tblmatdescription as am on ai.nMatId = am.nMatId
where vehicleNumber = '".$vehicleNumber."' and date = '".$date."' and netWeight = '".$weight."'";

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

  function getWeighmentDetails($date, $vehicleNumber, $weight) {
      $strSql = "Select * from outwardWeighment where vehicleNumber = '".$vehicleNumber."' and date = '".$date."' and netWeight = '".$weight."'";

      $query = $this->db->query($strSql);
      $arr='';
      if ($query->num_rows() > 0) {
          foreach ($query->result() as $row) {
              $arr[] =$row;
          }
      }
      return $arr;
  }
}
