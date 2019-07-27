<?php
//link the controller to the nav link

$route[FUEL_ROUTE.'weigh_updation'] = FUEL_FOLDER.'/module';
$route[FUEL_ROUTE.'weigh_updation/(.*)'] = FUEL_FOLDER.'/module/$1';
$route[FUEL_ROUTE.'weigh_updation'] = 'weigh_updation';
$route[FUEL_ROUTE.'weigh_updation/allocate_weight'] = 'weigh_updation/allocate_weight';
$route[FUEL_ROUTE.'weigh_updation/getOutwardVehiclesWithDate'] = 'weigh_updation/getOutwardVehiclesWithDate';
$route[FUEL_ROUTE.'weigh_updation/saveOutwardWeightment'] = 'weigh_updation/saveOutwardWeightment';
