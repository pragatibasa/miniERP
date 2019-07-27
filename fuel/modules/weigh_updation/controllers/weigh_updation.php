<?php
require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');

class weigh_updation extends Fuel_base_controller {
	private $data;
	public $nav_selected = 'weigh_updation';
	public $view_location = 'weigh_updation';

	function __construct() {
		parent::__construct();
		$this->load->module_model(WEIGH_UPDATION_FOLDER, 'weigh_updation_model');
	}

	function index() {
		$this->_render('weigh_updation');
	}

	function getOutwardVehiclesWithDate() {
		$vehicles = $this->weigh_updation_model->getOutwardVehiclesWithDate($_POST['date']);

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
		$bills = $this->weigh_updation_model->getBillWithVehicleNumber($_POST['date'], $_POST['vehiclenumber']);

		if(!empty($bills)) {
		 $files = array();
		 foreach($bills as $bill) {
			 $obj = new stdClass();
			 $obj->billnumber = $bill->nBillNo;
			 $obj->partyname = $bill->nPartyName;
			 $obj->billweight = ($bill->vBillType == 'Slitting' ) ? round(($bill->fTotalWeight/1000),3) : $bill->fTotalWeight;
			 $files[] = $obj;
		 }
		 echo json_encode($files);
		} else {
		 $status = array("status"=>"No Results!");
		 echo json_encode($status);
		}
		exit;
	}

	function saveOutwardWeightment() {
		// print_r($_POST);exit;
		if (!empty($_POST)){
			$arr = $this->weigh_updation_model->saveOutwardWeightment($_POST);
		}
		else{
			echo 'Error';
		}
		exit;
	}
}

/* End of file */
/* Location: ./fuel/modules/controllers*/
