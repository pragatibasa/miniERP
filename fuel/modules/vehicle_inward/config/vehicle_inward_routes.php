<?php
//link the controller to the nav link

$route[FUEL_ROUTE. 'vehicle_inward'] = FUEL_FOLDER.'/module';
$route[FUEL_ROUTE.'vehicle_inward/(.*)'] = FUEL_FOLDER.'/module/$1';
$route[FUEL_ROUTE. 'vehicle_inward'] = 'vehicle_inward';
$route[FUEL_ROUTE.'vehicle_inward/getInwardVehiclesWithDate'] = 'vehicle_inward/getInwardVehiclesWithDate';
$route[FUEL_ROUTE.'vehicle_inward/fetchWeighmentsWithDateAndVehicleNumber'] = 'vehicle_inward/fetchWeighmentsWithDateAndVehicleNumber';
$route[FUEL_ROUTE.'vehicle_inward/displayWeightmentDetails'] = 'vehicle_inward/displayWeightmentDetails';
$route[FUEL_ROUTE.'vehicle_inward/getWeighmentDetails'] = 'vehicle_inward/getWeighmentDetails';
