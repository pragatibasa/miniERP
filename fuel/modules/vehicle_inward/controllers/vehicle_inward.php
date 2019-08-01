<?php
require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');

class vehicle_inward extends Fuel_base_controller {
	private $data;
	public $nav_selected = 'vehicle_inward';
	public $view_location = 'vehicle_inward';

	function __construct() {
		parent::__construct();
		$this->load->module_model(VEHICLE_INWARD_FOLDER, 'vehicle_inward_model');
	}

	function index() {
		$this->_render('vehicle_inward');
	}

	function getInwardVehiclesWithDate() {
		$vehicles = $this->vehicle_inward_model->getInwardVehiclesWithDate($_POST['date']);

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

	function fetchWeighmentsWithDateAndVehicleNumber() {
        $weighments = $this->vehicle_inward_model->fetchWeighmentsWithDateAndVehicleNumber($_POST['date'], $_POST['vehicleNumber']);

        if(!empty($weighments)) {
            $files = array();
            foreach($weighments as $weight) {
                $obj = new stdClass();
                $obj->weight = $weight->weight;
                $files[] = $obj;
            }
            echo json_encode($files);
        } else {
            $status = array("status"=>"No Results!");
            echo json_encode($status);
        }
        exit;
    }

    function displayWeightmentDetails() {
        $weighmentDetails = $this->vehicle_inward_model->displayWeightmentDetails($_POST['date'], $_POST['vehiclenumber'], $_POST['weight']);
        if(!empty($weighmentDetails)) {
            $files = array();
            foreach($weighmentDetails as $weighment) {
                $obj = new stdClass();
                $obj->vIRnumber = $weighment->vIRnumber;
                $obj->partyname = $weighment->nPartyName;
                $obj->dReceivedDate = $weighment->dReceivedDate;
                $obj->vDescription = $weighment->vDescription;
                $obj->fThickness = $weighment->fThickness;
                $obj->fWidth = $weighment->fWidth;
                $obj->fLength = $weighment->fLength;
                $obj->fQuantity = $weighment->fQuantity;
                $obj->materialWeight = $weighment->materialWeight;
                $obj->packagingWeight = $weighment->packagingWeight;
                $obj->totalAllocatedWeight = $weighment->totalAllocatedWeight;
                $files[] = $obj;
            }
            echo json_encode($files);
        } else {
            $status = array("status"=>"No Results!");
            echo json_encode($status);
        }
        exit;
    }

    function getWeighmentDetails() {
        $weighmentDetails = $this->vehicle_inward_model->getWeighmentDetails($_POST['date'], $_POST['vehiclenumber'], $_POST['weight']);

        if(!empty($weighmentDetails)) {
            $files = array();
            foreach($weighmentDetails as $weighment) {
                $obj = new stdClass();
                $obj->bridgeName = $weighment->bridgeName;
                $obj->slipNo = $weighment->slipNo;
                $obj->createdDate = $weighment->createdDate;
                $obj->netWeight = $weighment->netWeight;
                $files[] = $obj;
            }
            echo json_encode($files);
        } else {
            $status = array("status"=>"No Results!");
            echo json_encode($status);
        }
        exit;
    }
}

/* End of file */
/* Location: ./fuel/modules/controllers*/
