<?php

require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');

class inward extends Fuel_base_controller {
	private $data;
	public $nav_selected = 'inward';
	public $view_location = 'inward';
	private $datam;
	private $fdata;
	
	
	function __construct()
	{
		parent::__construct();
		$this->config->load('inward');
		$this->load->language('inward');
		$this->inward = $this->config->item('inward');
		$this->load->module_model(INWARD_FOLDER, 'inward_model');
		$this->data = $this->inward_model->example();
		if(isset($this->data)) {
			
		$this->uri->init_get_params();
		$this->pname = (string) $this->input->get('pname', TRUE);
		$this->ppartyid = (string) $this->input->get('ppartyid', TRUE);
		$this->pcoildetails = '';
		if($this->pname == 'undefined' || $this->pname == '' || $this->pname == 'No Result'){
			$this->pname = '';
		} 
		$this->partyname = (string) $this->input->get('partyname', TRUE);
		$this->datam = $this->inward_model->mat();
		$this->fdata = $this->inward_model->party();
	}		
}	
	function index() {
		if(!empty($this->data) && isset($this->data)) {
		
			$bundleNumber = (string) $this->input->get('bundleNumber', TRUE);
		
			if(!empty(trim($this->ppartyid))) {
				$this->pcoildetails = $this->inward_model->getParentCoilDetails($this->ppartyid);
			} 
			if(!empty($bundleNumber)) {
				$vars['bundledetails'] = $this->inward_model->getParentBundleDetails($this->ppartyid,$bundleNumber);
			}
			$vars['bundleNumber'] = $bundleNumber;
			$vars['data']= $this->data;
			$vars['ppartyid']= $this->ppartyid;
			$vars['pname'] = $this->pname;
			$vars['datam'] = $this->datam;
			$vars['fdata'] = $this->fdata;
			$vars['pcoildetails'] = $this->pcoildetails;
            $this->_render('inward', $vars);
		} else {
			redirect(fuel_url('#'));
		}
	}
	
	function checkcoilno() {
		if (!empty($_REQUEST)) {
		$checkrecordinfo = $this->inward_model->checkcoilno($_REQUEST);
		return $checkrecordinfo;
		}else {
		echo 'ERROR';
		}
	}

	function inwardbillgenerate(){
		 $queryStr = $_SERVER['QUERY_STRING'];
        parse_str($queryStr, $args);
        $pname = $args["pname"];
		$pid = $args["pid"];
		$this->load->module_model(INWARD_FOLDER, 'inward_model');
		$inwardbillgenerateb = $this->inward_model->inwardbillgeneratemodel($pname,$pid);
	
	}
		
	function savedetails(){
		if (!empty($_POST)){
		$this->load->module_model(INWARD_FOLDER, 'inward_model');
			$arr = $this->inward_model->saveinwardentry($_POST['pid'],$_POST['pname'], $_POST['date3'],$_POST['lno'],$_POST['icno'],$_POST['date4'], $_POST['coil'],$_POST['fWidth'], $_POST['fThickness'],$_POST['fLength'],$_POST['fQuantity'],$_POST['status'],$_POST['hno'],$_POST['pna'],$_POST['ppartyid'],$_POST['parentBundleNumber']);
			if(empty($arr)) echo 'Success'; else echo 'Unable to save';
	
		}
		else{
			//redirect(fuel_uri('#'));
		}
	}
		
	function autosuggest($pname = ''){
		if(empty($pname)) { 
			$pname = $_POST['queryString'];
		}
		$pnamelists = $this->inward_model->list_pnamelists($pname);
		return $pnamelists;
	}

    function exportExcel() {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=inward_register".date('d/M/Y'));
        $heading = false;

        $records = $this->inward_entry_model->exportInwardData();
//        print_r($records);exit;
        if($records->num_rows() > 0) {
            foreach($records->result() as $row) {
                $arr = get_object_vars($row);
                if(!$heading) {
                    // display field/column names as a first row
                    echo implode("\t", array_keys($arr)) . "\n";
                    $heading = true;
                }
                echo implode("\t", array_values($arr)) . "\n";
            }
        }
        exit;
    }
}
/* End of file */
/* Location: ./fuel/modules/controllers*/