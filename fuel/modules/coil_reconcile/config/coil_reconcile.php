<?php
/*
|--------------------------------------------------------------------------
| FUEL NAVIGATION: An array of navigation items for the left menu
|--------------------------------------------------------------------------
*/

$config['modules']['reports'] = array(
	'module_name' => 'Coil Reconcilliation Report',
	'module_uri' => 'coil_reconcile',
	'permission' => 'coil_reconcile',
	'nav_selected' => 'coil_reconcile'
);
$config['nav']['sharoff_steel']['coil_reconcile'] = lang('module_coil_reconcile');
