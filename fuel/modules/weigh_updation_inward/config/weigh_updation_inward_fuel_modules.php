<?php
// included in the main config/MY_fuel_modules.php


$config['modules']['reports'] = array(
		'module_name' => 'Weigh Bridge Inward Updation',
		'module_uri' => 'weigh_updation_inward',
		'model_name' => 'weigh_updation_inward_model',
		'model_location' => 'weigh_updation_inward',
		'permission' => 'weigh_updation_inward',
		'nav_selected' => 'weigh_updation_inward',
		'instructions' => lang('module_instructions', 'weigh_updation_inward'),
		'item_actions' => array('save', 'view', 'publish', 'delete', 'duplicate', 'create', 'others' => array('my_module/backup' => 'Backup')),
);
