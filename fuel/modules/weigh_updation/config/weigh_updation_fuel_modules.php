<?php
// included in the main config/MY_fuel_modules.php


$config['modules']['reports'] = array(
		'module_name' => 'Weigh Bridge Updation',
		'module_uri' => 'weigh_updation',
		'model_name' => 'weigh_updation_model',
		'model_location' => 'weigh_updation',
		'permission' => 'weigh_updation',
		'nav_selected' => 'weigh_updation',
		'instructions' => lang('module_instructions', 'weigh_updation'),
		'item_actions' => array('save', 'view', 'publish', 'delete', 'duplicate', 'create', 'others' => array('my_module/backup' => 'Backup')),
);
