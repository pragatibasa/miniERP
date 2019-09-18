<?php
require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');

class Company_details extends Fuel_base_controller {
	private $data;
	private $gdata;
	public $nav_selected = 'company_details';
	public $view_location = 'company_details';

	function __construct() {
		parent::__construct();
		
		$this->load->module_model(COMPANY_DETAILS_FOLDER, 'Company_details_model');
		$this->config->load('company_details');
		$this->load->language('company_details');
		$this->Company_details = $this->config->item('Company_details');
		$this->data = $this->Company_details_model->getCompanyData();
		if(isset($this->data)) {
			if(isset($this->data[0]))  {
		    }
	    }
    }

	function savedetails() {
	    $arr = $this->Company_details_model->savecompany($_POST);
	}

	function index() {
		if(!empty($this->data) && isset($this->data)) {
			$vars['formdata']= $this->formdisplay();
			$vars['data']= $this->data;
            $this->_render('company_details', $vars);
		} else {
			redirect(fuel_url('#'));
		}
	}

	function formdisplay() {
	    return $this->Company_details_model->form_fields();
	}
}
/* End of file */
/* Location: ./fuel/modules/controllers*/