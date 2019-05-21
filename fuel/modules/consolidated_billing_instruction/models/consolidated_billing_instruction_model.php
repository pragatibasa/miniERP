<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/base_module_model.php');
require_once(MODULES_PATH.'/consolidated_billing_instruction/config/consolidated_billing_instruction_constants.php');
require_once(APPPATH.'helpers/tcpdf/config/lang/eng.php');
require_once(APPPATH.'helpers/tcpdf/tcpdf.php');

class consolidated_billing_instruction_model extends Base_module_model {
    function __construct() {
        parent::__construct('aspen_tblinwardentry');// table name
    }

    function getParentCoilDetails($vIRnumber) {
        $strSql = "Select * from aspen_tblinwardentry 
        left join aspen_tblmatdescription on aspen_tblmatdescription.nMatId = aspen_tblinwardentry.nMatId
        left join aspen_tblpartydetails on aspen_tblpartydetails.nPartyId = aspen_tblinwardentry.nPartyId
        where vIRnumber='".$vIRnumber."'";
        $query = $this->db->query($strSql);
        return $query->result()[0];
    }

    function getChildrenCoilDetails($vIRnumber) {
        $strSql = "Select * from aspen_tblinwardentry where vParentIRNumber='".$vIRnumber."'";
        $query = $this->db->query($strSql);
        return $query->result();
    }
    
    function getBundlesFromCoil($partyid) {
        
        $strSql = "select aspen_tblbillingstatus.nSno as bundlenumber,aspen_tblcuttinginstruction.nBundleweight
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

       $query = $this->db->query($strSql);
       $arr = '';
       if($query->num_rows() > 0) {
          foreach($query->result() as $row) {
             $arr[] =$row;
          }
       }
       return $arr;
    }

    function getSlitsFromCoil($partyid) {
        $strSql = "select aspen_tblslittinginstruction.nSno as slitnumber,
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
      
      $query = $this->db->query($strSql);
      $arr = '';
      if($query->num_rows() > 0) {
         foreach($query->result() as $row) {
            $arr[] =$row;
         }
      }
      return $arr;
    }

    function getThicknessRateByCoils($arrCoils) {
        $strSql = 'select sum(ap.nAmount) as subtotal
        from 
        aspen_tblinwardentry as ai 
        left join aspen_tblmatdescription as am on ai.nMatId = am.nMatId
        left join aspen_tblpricetype1 as ap on ap.nMatId = ai.nMatId
        where ai.fThickness between nMinThickness and nMaxThickness and ai.vIRnumber in ("' . implode('", "', $arrCoils) . '")';

        $query = $this->db->query($strSql);
        $arr = '';
        if($query->num_rows() > 0) {
            foreach($query->result() as $row) {
                $arr[] =$row;
            }
        }
        return $arr[0];
    }

    function getSelectedCuttingCoils($coilNumber, $selectedBundles) {
        $strSql = "select aspen_tblbillingstatus.nSno as bundlenumber,aspen_tblcuttinginstruction.nBundleweight
        as weight,aspen_tblcuttinginstruction.nLength as length,aspen_tblcuttinginstruction.vIRnumber as coilnumber
       ,aspen_tblcuttinginstruction.nNoOfPieces as totalnumberofsheets,
        aspen_tblbillingstatus.nBilledNumber  as noofsheetsbilled
       ,aspen_tblbillingstatus.vBillingStatus as billingstatus, 
       aspen_tblbillingstatus.nbalance AS balance,
        round(nBundleweight - (nBundleweight*nBilledNumber/nNoOfPieces),2) as balanceWeight
         from aspen_tblcuttinginstruction
       LEFT JOIN aspen_tblbillingstatus  ON aspen_tblcuttinginstruction.vIRnumber=aspen_tblbillingstatus
       .vIRnumber  WHERE  aspen_tblcuttinginstruction.nSno = aspen_tblbillingstatus.nSno and aspen_tblcuttinginstruction
       .vIRnumber='".$coilNumber."' and aspen_tblcuttinginstruction.nSno in (".implode(',',array_keys($selectedBundles)).") Group by  aspen_tblbillingstatus.nSno";

       $query = $this->db->query($strSql);
       $arr = '';
       if($query->num_rows() > 0) {
          foreach($query->result() as $row) {
             $arr[] =$row;
          }
       }
       return $arr;
    }

    function getSelectedSlittingCoils($coilNumber, $selectedBundles) {
        $strSql = "select aspen_tblslittinginstruction.nSno as slitnumber,
        aspen_tblslittinginstruction.nLength as length,
        aspen_tblslittinginstruction.nWidth as width,
        aspen_tblslittinginstruction.nWeight as weight,
        aspen_tblslittinginstruction.dDate as sdate,
        aspen_tblbillingstatus.vBillingStatus as billingstatus,
        aspen_tblinwardentry.vParentBundleNumber 
    from aspen_tblslittinginstruction
      LEFT JOIN aspen_tblbillingstatus ON aspen_tblslittinginstruction.vIRnumber=aspen_tblbillingstatus.vIRnumber 
      left join aspen_tblinwardentry on aspen_tblinwardentry.vParentIRNumber = aspen_tblslittinginstruction.vIRnumber and aspen_tblinwardentry.vParentBundleNumber = aspen_tblslittinginstruction.nSno
      WHERE aspen_tblslittinginstruction.nSno = aspen_tblbillingstatus.nSno and aspen_tblslittinginstruction.vIRnumber='".$coilNumber."' and aspen_tblslittinginstruction.nSno in (".implode(',',array_keys($selectedBundles)).") and aspen_tblslittinginstruction.vStatus =  'Ready To Bill' 
      Group by aspen_tblbillingstatus.nSno";
      
      $query = $this->db->query($strSql);
      $arr = '';
      if($query->num_rows() > 0) {
         foreach($query->result() as $row) {
            $arr[] =$row;
         }
      }
      return $arr;
    }
}