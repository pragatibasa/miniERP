<?php
//link the controller to the nav link

$route[FUEL_ROUTE. 'vehicle_despatch'] = FUEL_FOLDER.'/module';
$route[FUEL_ROUTE.'vehicle_despatch/(.*)'] = FUEL_FOLDER.'/module/$1';
$route[FUEL_ROUTE. 'vehicle_despatch'] = 'vehicle_inward';
$route[FUEL_ROUTE.'vehicle_despatch/getOutwardVehiclesWithDate'] = 'vehicle_despatch/getOutwardVehiclesWithDate';
$route[FUEL_ROUTE.'vehicle_despatch/fetchWeighmentsWithDateAndVehicleNumber'] = 'vehicle_despatch/fetchWeighmentsWithDateAndVehicleNumber';
$route[FUEL_ROUTE.'vehicle_despatch/displayWeightmentDetails'] = 'vehicle_despatch/displayWeightmentDetails';
$route[FUEL_ROUTE.'vehicle_despatch/getWeighmentDetails'] = 'vehicle_despatch/getWeighmentDetails';
