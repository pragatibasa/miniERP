<?php
// included in the main config/MY_fuel_modules.php


$config['modules']['reports'] = array(
		'module_name' => 'Vehicle Despatch Report',
		'module_uri' => 'vehicle_despatch',
		'model_name' => 'vehicle_despatch_model',
		'model_location' => 'vehicle_despatch',
		'permission' => 'vehicle_despatch',
		'nav_selected' => 'vehicle_despatch',
		'instructions' => lang('module_instructions', 'vehicle_despatch'),
		'item_actions' => array('save', 'view', 'publish', 'delete', 'duplicate', 'create', 'others' => array('my_module/backup' => 'Backup')),
);
