<?php
require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');

class weigh_updation_inward extends Fuel_base_controller {
	private $data;
	public $nav_selected = 'weigh_updation_inward';
	public $view_location = 'weigh_updation_inward';

	function __construct() {
		parent::__construct();
		$this->load->module_model(WEIGH_UPDATION_INWARD_FOLDER, 'weigh_updation_inward_model');
	}

	function index() {
		$this->_render('weigh_updation_inward');
	}

	function getInwardVehiclesWithDate() {
		$vehicles = $this->weigh_updation_inward_model->getInwardVehiclesWithDate($_POST['date']);

		if(!empty($vehicles)) {
		 $files = array();
		 foreach($vehicles as $vehicle) {
			 $obj = new stdClass();
			 $obj->vehiclenumber = $vehicle->vehiclenumber;
			 $files[] = $obj;
		 }
		 echo json_encode($files);
		} else {
		 $status = array("status"=>"No Results!");
		 echo json_encode($status);
		}
		exit;
	}

	function allocate_weight() {
		$inwards = $this->weigh_updation_inward_model->getInwardWithVehicleNumber($_POST['date'], $_POST['vehiclenumber']);

		if(!empty($inwards)) {
		 $files = array();
		 foreach($inwards as $inward) {
			 $obj = new stdClass();
			 $obj->coilNumber = $inward->vIRnumber;
			 $obj->partyname = $inward->nPartyName;
			 $obj->fQuantity = $inward->fQuantity;
			 $files[] = $obj;
		 }
		 echo json_encode($files);
		} else {
		 $status = array("status"=>"No Results!");
		 echo json_encode($status);
		}
		exit;
	}

	function saveInwardWeightment() {
		if (!empty($_POST)) {
			$arr = $this->weigh_updation_inward_model->saveInwardWeightment($_POST);
		} else {
			echo 'Error';
		}
		exit;
	}
}

/* End of file */
/* Location: ./fuel/modules/controllers*/
