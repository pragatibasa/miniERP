<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/base_module_model.php');
require_once(MODULES_PATH.'/reports/config/reports_constants.php');
require_once(APPPATH.'helpers/tcpdf/config/lang/eng.php');
require_once(APPPATH.'helpers/tcpdf/tcpdf.php');

class coil_reconcile_model extends Base_module_model {
  function __construct() {
      parent::__construct('aspen_tblinwardentry');// table name
  }

  function getParties() {
      $CI =& get_instance();
      $userdata = $CI->fuel_auth->user_data();

      $whereSql = '';
      if($userdata['super_admin'] == 'no') {
        $whereSql = 'where nPartyName="'.$userdata['user_name'].'"';
      }

      $query = $this->db->query('select * from aspen_tblpartydetails '.$whereSql);
      $arr='';
      if ($query->num_rows() > 0) {
          foreach ($query->result() as $row) {
              $arr[] =$row;
          }
      }
      return $arr;
  }

  function getCoilNumberByParty($partyId, $term) {
      $query = $this->db->query("select vIRnumber from aspen_tblinwardentry where nPartyId=$partyId and vIRnumber like '%$term%'");
      $arr='';
      if ($query->num_rows() > 0) {
          foreach ($query->result() as $row) {
              $arr[] = $row->vIRnumber;
          }
      }
      return $arr;
  }

  function getCoilReconcileDetails($coilNumber) {
      return $this->db->query("SELECT 
                                    ai.*, abs(ROUND(ai.fpresent)) as fpresent, am.*, COALESCE(COUNT(ai.vIRnumber), NULL) AS coil_upgrade
                                FROM
                                    aspen_tblinwardentry ai
                                        LEFT JOIN
                                    aspen_tblinwardentry aii ON ai.vIRnumber = aii.vParentIRNumber
                                        LEFT JOIN
                                    aspen_tblmatdescription am ON ai.nMatId = am.nMatId
                                WHERE
                                    ai.vIRnumber = '$coilNumber'
                                GROUP BY ai.vIRnumber")->result();
  }

  function getChildCoilsByParentCoilNumber($parentCoilNumber) {
      return $this->db->query("SELECT 
                                    *
                                FROM
                                    aspen_tblinwardentry ai
                                        LEFT JOIN
                                    aspen_tblmatdescription am ON ai.nMatId = am.nMatId
                                WHERE
                                   ai.vParentIRNumber = '$parentCoilNumber'");
  }

  function getCoilBillDetails($coilNumber) {
      return $this->db->query("SELECT 
                                    *
                                FROM
                                    aspen_tblbilldetails AS ab
                                        LEFT JOIN
                                    outwardWeighmentBills AS owb ON ab.nBillNo = owb.billNo
                                        LEFT JOIN
                                    outwardWeighment AS ow ON ow.id = owb.outwardId
                                WHERE
                                    ab.vIRnumber = '$coilNumber'");
  }
}
