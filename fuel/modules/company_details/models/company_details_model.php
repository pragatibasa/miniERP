<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH . 'models/base_module_model.php');
require_once(MODULES_PATH . '/company_details/config/company_details_constants.php');

class Company_details_model extends Base_module_model {

    function __construct() {
        parent::__construct('aspen_tblmatdescription');
    }

    function getCompanyData() {
        return $this->db->query("select * from aspen_company_details where company_id = 1")->result();
    }

    function savecompany($inputArr) {

        $sql = "update aspen_company_details set
		company_name = '".$inputArr['cname']."',
		identifier_receivable = '".$inputArr['ide_receive']."',
		identifier_payable = '".$inputArr['ide_payable']."',
		head_address = '".$inputArr['headOffice']."',
		branch_address = '".$inputArr['branchOffice']."',
		contact = '".$inputArr['contact']."',
		email = '".$inputArr['email']."',
		gst_no = '".$inputArr['gstNumber']."',
		tin_no = '".$inputArr['tinNumber']."'
        where company_id = 1";

        $query = $this->db->query($sql);
    }

    function form_fields() {
        $CI =& get_instance();
        $fields['nMinLength']['type'] = 'Min length';
        $fields['nMaxLength']['type'] = 'Max length';
        $fields['nAmount']['type'] = 'Amount';
        return $fields;

    }
}

class Companydetails_model extends Base_module_model {

}
