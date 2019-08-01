<?php
//link the controller to the nav link

$route[FUEL_ROUTE.'weigh_updation_inward'] = FUEL_FOLDER.'/module';
$route[FUEL_ROUTE.'weigh_updation_inward/(.*)'] = FUEL_FOLDER.'/module/$1';
$route[FUEL_ROUTE.'weigh_updation_inward'] = 'weigh_updation_inward';
$route[FUEL_ROUTE.'weigh_updation_inward/getInwardVehiclesWithDate'] = 'weigh_updation_inward/getInwardVehiclesWithDate';
$route[FUEL_ROUTE.'weigh_updation_inward/allocate_weight'] = 'weigh_updation_inward/allocate_weight';
$route[FUEL_ROUTE.'weigh_updation_inward/saveInwardWeightment'] = 'weigh_updation_inward/saveInwardWeightment';