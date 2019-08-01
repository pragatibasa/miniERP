<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/base_module_model.php');

class vehicle_inward_model extends Base_module_model {
  function __construct() {
      parent::__construct('aspen_tblinwardentry');// table name
  }

  function getInwardVehiclesWithDate($date) {
    $strSql = "select distinct vehicleNumber as vehiclenumber from aspen_tblInwardWeighment where date = '".$date."'";

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
      $strSql = "select netWeight as weight from aspen_tblInwardWeighment where vehicleNumber = '".$vehicleNumber."' and date = '".$date."'";

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
      $strSql = "SELECT 
                    *
                FROM
                    aspen_tblInwardWeighment AS ow
                        LEFT JOIN
                    aspen_tblInwardWeighmentCoils AS owb ON ow.id = owb.outwardId
                        LEFT JOIN
                    aspen_tblinwardentry AS ai ON ai.vIRnumber = owb.vIRnumber
                        LEFT JOIN
                    aspen_tblpartydetails AS ap ON ap.nPartyId = ai.nPartyId
                        LEFT JOIN
                    aspen_tblmatdescription AS am ON ai.nMatId = am.nMatId
                WHERE
                    vehicleNumber = '".$vehicleNumber."'
                        AND date = '".$date."'
                        AND netWeight = '".$weight."'";

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
      $strSql = "Select * from aspen_tblInwardWeighment where vehicleNumber = '".$vehicleNumber."' and date = '".$date."' and netWeight = '".$weight."'";

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
