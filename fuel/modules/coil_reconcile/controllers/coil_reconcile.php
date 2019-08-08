<?php
require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');

class coil_reconcile extends Fuel_base_controller {
	public $nav_selected = 'coil_reconcile';
	public $view_location = 'coil_reconcile';

	function __construct() {
		parent::__construct();
		$this->load->module_model(COIL_RECONCILE_FOLDER, 'coil_reconcile_model');
	}

	function index() {
        $vars['parties'] = $this->coil_reconcile_model->getParties();
		$this->_render('coil_reconcile', $vars);
	}

	function searchCoilNumber() {
        $coil_numbers = $this->coil_reconcile_model->getCoilNumberByParty($_REQUEST['partyId'], $_REQUEST['term']);
        echo json_encode($coil_numbers);exit;
    }

    function getCoilReconcileDetails() {
        $responseData['coil_details'] = (array) $this->coil_reconcile_model->getCoilReconcileDetails($_REQUEST['coilNumber'])[0];

        if($responseData['coil_details']['coil_upgrade'] > 1) {
            $child_coils = $this->coil_reconcile_model->getChildCoilsByParentCoilNumber($_REQUEST['coilNumber']);
            if ($child_coils->num_rows() > 0) {
                foreach ($child_coils->result() as $row) {
                    $responseData['child_details'][$row->vIRnumber][] = (array) $row;
                }
            }
        }

	    $bill_details = $this->coil_reconcile_model->getCoilBillDetails($_REQUEST['coilNumber']);
        if ($bill_details->num_rows() > 0) {
            foreach ($bill_details->result() as $row) {
                $responseData['bill_details'][$row->nBillNo][] = $row->nBillNo;
                $responseData['bill_details'][$row->nBillNo][] = $row->dBillDate;
                $responseData['bill_details'][$row->nBillNo][] = $row->ntotalpcs;
                $responseData['bill_details'][$row->nBillNo][] = ($row->vBillType == 'Slitting' ) ? round(($row->fTotalWeight),3) : round(($row->fTotalWeight*1000),3);
                $responseData['bill_details'][$row->nBillNo][] = ($row->materialWeight == null) ? 0 : $row->materialWeight;
                $responseData['bill_details'][$row->nBillNo][] = ($row->packagingWeight == null) ? 0 : $row->packagingWeight;
                $responseData['bill_details'][$row->nBillNo][] = ($row->totalAllocatedWeight == null) ? 0 :$row->totalAllocatedWeight;
            }
        }
	    echo json_encode($responseData);exit;
    }
}

/* End of file */
/* Location: ./fuel/modules/controllers*/
