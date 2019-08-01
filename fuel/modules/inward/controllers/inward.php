<?php
require 'vendor/autoload.php';
require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');
ini_set('memory_limit', '-1');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
			$arr = $this->inward_model->saveinwardentry($_POST['pid'],$_POST['pname'], $_POST['date3'],$_POST['lno'],$_POST['icno'],$_POST['date4'], $_POST['coil'],$_POST['fWidth'], $_POST['fThickness'],$_POST['fLength'],$_POST['fQuantity'],$_POST['status'],$_POST['hno'],$_POST['pna'],$_POST['ppartyid'],$_POST['parentBundleNumber'],$_POST['grade'],$_POST['cast']);
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
        $spreadsheet = new Spreadsheet();

// Set workbook properties
        $spreadsheet->getProperties()->setCreator('Pragati')
            ->setLastModifiedBy('Pragati')
            ->setTitle('Inward Register Report')
            ->setSubject('InwardRegisterReport')
            ->setDescription('Inward register report for date'.date('d/m/YYYY'))
            ->setKeywords('Microsoft office 2013 php inward register report')
            ->setCategory('Inward Register Report');

        $activeSheet = $spreadsheet->getActiveSheet();

        $spreadsheet->setActiveSheetIndex(0)->setCellValue('A1', 'Inward Register Report');

        $activeSheet->getStyle('A1')->applyFromArray(
            array(
                'font'  => array(
                    'size'  =>  '25',
                    'bold' => true,
                )
            )
        );
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('A2', 'Created On:');
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('B2', date('d/m/Y'));


        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A4', 'Party Name')
            ->setCellValue('B4', 'Coil Number')
            ->setCellValue('C4', 'Inward Date')
            ->setCellValue('D4', 'Vehicle Number')
            ->setCellValue('E4', 'Invoice Number')
            ->setCellValue('F4', 'Invoice Date')
            ->setCellValue('G4', 'Material Description')
            ->setCellValue('H4', 'Width')
            ->setCellValue('I4', 'Thickness')
            ->setCellValue('K4', 'Length')
            ->setCellValue('L4', 'Weight')
            ->setCellValue('M4', 'Status')
            ->setCellValue('N4', 'Grade')
            ->setCellValue('O4', 'Heat Number');


        //getStyle accepts a range of cells as well!
        $activeSheet->getStyle('A4:P4')->applyFromArray(
            array(
                'font'  => array(
                    'bold'  =>  true
                )
            )
        );
        $arrHeading = array('Party Name' => 'nPartyName','Coil Number' => 'vIRnumber','Inward Date' => 'dReceivedDate','Jsw Coil id' => 'jid','SST id' => 'ssid', 'Vehicle Number' => 'vLorryNo','Invoice Number' => 'vInvoiceNo', 'Invoice Date' => 'dInvoiceDate','Material Description' => 'vDescription','Width' => 'fWidth','Thickness' => 'fThickness', 'Length' => 'fLength','Weight' => 'fQuantity','Status' => 'vStatus','Grade' => 'vGrade','Heat Number' => 'vHeatnumber');

        $records = $this->inward_model->exportInwardData();
        if($records->num_rows() > 0) {
            $i = 5;
            foreach ($records->result() as $row) {
                $arr = get_object_vars($row);
                $spreadsheet->getActiveSheet()->setCellValue('A' . $i, $arr[$arrHeading['Party Name']])
                    ->setCellValue('B' . $i, $arr[$arrHeading['Coil Number']])
                    ->setCellValue('C' . $i, $arr[$arrHeading['Inward Date']])
                    ->setCellValue('D' . $i, $arr[$arrHeading['Vehicle Number']])
                    ->setCellValue('E' . $i, $arr[$arrHeading['Invoice Number']])
                    ->setCellValue('F' . $i, $arr[$arrHeading['Invoice Date']])
                    ->setCellValue('G' . $i, $arr[$arrHeading['Material Description']])
                    ->setCellValue('H' . $i, $arr[$arrHeading['Width']])
                    ->setCellValue('I' . $i, $arr[$arrHeading['Thickness']])
                    ->setCellValue('J' . $i, $arr[$arrHeading['Length']])
                    ->setCellValue('K' . $i, $arr[$arrHeading['Weight']])
                    ->setCellValue('L' . $i, $arr[$arrHeading['Status']])
                    ->setCellValue('M' . $i, $arr[$arrHeading['Grade']])
                    ->setCellValue('N' . $i, $arr[$arrHeading['Heat Number']]);;
                $i++;
            }
        }


// Set worksheet title
        $spreadsheet->getActiveSheet()->setTitle('Inward Register Report');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

// Redirect output to a client's web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Inward_Register_Report"'.date('d/m/YYYY').'".xlsx"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
/* End of file */
/* Location: ./fuel/modules/controllers*/