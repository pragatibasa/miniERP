<?php
// included in the main config/MY_fuel_modules.php


$config['modules']['reports'] = array(
		'module_name' => 'Vehicle Despatch Report',
		'module_uri' => 'vehicle_inward',
		'model_name' => 'vehicle_inward_model',
		'model_location' => 'vehicle_inward',
		'permission' => 'vehicle_inward',
		'nav_selected' => 'vehicle_inward',
		'instructions' => lang('module_instructions', 'vehicle_inward'),
		'item_actions' => array('save', 'view', 'publish', 'delete', 'duplicate', 'create', 'others' => array('my_module/backup' => 'Backup')),
);
