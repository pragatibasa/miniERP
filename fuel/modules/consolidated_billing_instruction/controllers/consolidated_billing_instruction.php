<?php
require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');

function valid_values($var) {
	return $var;
}

class consolidated_billing_instruction extends Fuel_base_controller {

	public $nav_selected =  'consolidated_billing_instruction';
	public $view_location = 'consolidated_billing_instruction';
		
	function __construct() {
		parent::__construct();
		$this->config->load('consolidated_billing_instruction');
		$this->load->language('consolidated_billing_instruction');
		$this->coil_labels = $this->config->item('consolidated_billing_instruction');
		$this->load->module_model(CONSOLIDATED_BILLING_INSTRUCTION_FOLDER, 'consolidated_billing_instruction_model');
	}
	
	function index() {
		
		$vars['parent_coil'][$_REQUEST['partyid']]['coil'] = $this->consolidated_billing_instruction_model->getParentCoilDetails($_REQUEST['partyid']);
	
		$vars['parent_coil'][$_REQUEST['partyid']]['bs'] = $this->listParentBundlesOrSlits($_REQUEST['partyid'], $vars['parent_coil'][$_REQUEST['partyid']]['coil']->vprocess, true);

		$vars['children_coils'] = $this->consolidated_billing_instruction_model->getChildrenCoilDetails($_REQUEST['partyid']);

		foreach($vars['children_coils'] as $key => $child_coil) {
			$vars['children_coils'][$child_coil->vIRnumber]['coil'] = $child_coil;
			$vars['children_coils'][$child_coil->vIRnumber]['bs'] = $this->listParentBundlesOrSlits($child_coil->vIRnumber, $child_coil->vprocess, true);
			unset($vars['children_coils'][$key]);
		}

		$vars['partyId'] = $_REQUEST['partyid'];

		$this->_render('consolidated_billing_instruction', $vars);
	}

	function listParentBundlesOrSlits($partyId = '', $process = '', $returnValues =  false) {
		$partyId = $partyId ? $partyId : $_REQUEST['partyid'];
		$process = $process ? $process : $_REQUEST['process'];

		if($process == 'Slitting') {
			$parentSlits = $this->consolidated_billing_instruction_model->getSlitsFromCoil($partyId);

			if($returnValues) {
				return $parentSlits;
			}

			if(!empty($parentSlits)){
				$files = array();
				foreach($parentSlits as $sl) {
					$obj = new stdClass();
					$obj->serialnumber = $sl->slitnumber;
					$obj->length = $sl->length;
					$obj->width = $sl->width;
					$obj->weight = $sl->weight;
					$obj->sdate = date('d-m-Y',strtotime($sl->sdate));
					$obj->billingstatus = $sl->billingstatus;
					$obj->dl = '/?slitnumber='.$sl->slitnumber;
					$obj->vParentBundleNumber = $sl->vParentBundleNumber; 
					$files[] = $obj;
				}
				echo json_encode($files);
			}else{
				$status = array("status"=>"No Results!");
				echo json_encode($status);
			}
		} else {
			$parentBundles = $this->consolidated_billing_instruction_model->getBundlesFromCoil($partyId);
			if($returnValues) {
				return $parentBundles;
			}
		}

	}

	function previewBillPage() {

		$vars['parent_coil'][$_REQUEST['partyid']]['coil'] = $this->consolidated_billing_instruction_model->getParentCoilDetails($_POST['partyid']);

		foreach($_POST['cutting-bundles'] as $coilNumber => $bundles) {
			$filtered_bundles = array_filter($bundles, "valid_values");
			$vars['selected_cutting_coil'][$coilNumber][] = $this->consolidated_billing_instruction_model->getSelectedCuttingCoils($coilNumber, $filtered_bundles);
			
			$vars['selected_cutting_coil'][$coilNumber]['number_billed'] = $filtered_bundles;
		}

		foreach($_POST['slitting-bundles'] as $coilNumber => $bundles) {
			$filtered_bundles = array_filter($bundles, "valid_values");
			$vars['selected_slitting_coil'][$coilNumber][] = $this->consolidated_billing_instruction_model->getSelectedSlittingCoils($coilNumber, $filtered_bundles);
		}

		$vars['thickness_rate'] = $this->consolidated_billing_instruction_model->getThicknessRateByCoils(array_merge(array_keys($_POST['slitting-bundles']),array_keys($_POST['cutting-bundles'])));

		$vars['partyId'] = $_REQUEST['partyid'];

		// print_r('<pre>');
		// print_r($vars);
		// print_r('</pre>');
		// exit;
		$this->_render('consolidated_billing_instruction_preview', $vars);
	}
}	
