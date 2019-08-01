<?php
/*
|--------------------------------------------------------------------------
| FUEL NAVIGATION: An array of navigation items for the left menu
|--------------------------------------------------------------------------
*/

$config['modules']['reports'] = array(
	'module_name' => 'Vehicle Despatch Report',
	'module_uri' => 'vehicle_inward',
	'permission' => 'vehicle_inward',
	'nav_selected' => 'vehicle_inward'
);
$config['nav']['sharoff_steel']['vehicle_inward'] = lang('module_vehicle_despatch');
