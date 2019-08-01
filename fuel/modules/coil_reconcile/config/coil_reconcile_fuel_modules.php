<?php
// included in the main config/MY_fuel_modules.php


$config['modules']['reports'] = array(
		'module_name' => 'Coil Reconcilliation Resport',
		'module_uri' => 'coil_reconcile',
		'model_name' => 'coil_reconcile_model',
		'model_location' => 'coil_reconcile',
		'permission' => 'coil_reconcile',
		'nav_selected' => 'coil_reconcile',
		'instructions' => lang('module_instructions', 'coil_reconcile'),
		'item_actions' => array('save', 'view', 'publish', 'delete', 'duplicate', 'create', 'others' => array('my_module/backup' => 'Backup')),
);
