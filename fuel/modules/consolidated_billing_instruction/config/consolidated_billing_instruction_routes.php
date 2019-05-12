<?php 
//link the controller to the nav link

$route[FUEL_ROUTE.'consolidated_billing_instruction'] = FUEL_FOLDER.'/module';
$route[FUEL_ROUTE.'consolidated_billing_instruction/(.*)'] = FUEL_FOLDER.'/module/$1';
$route[FUEL_ROUTE.'consolidated_billing_instruction'] = 'consolidated_billing_instruction';
$route[FUEL_ROUTE.'consolidated_billing_instruction/listParentBundlesOrSlits'] = 'consolidated_billing_instruction/listParentBundlesOrSlits';
$route[FUEL_ROUTE.'consolidated_billing_instruction/previewBillPage'] = 'consolidated_billing_instruction/previewBillPage';