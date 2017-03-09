<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-02-20 10:01:42 --> Severity: Warning --> mysqli::real_connect(): (HY000/2003): Can't connect to MySQL server on '192.168.1.135' (113) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2017-02-20 10:01:42 --> Unable to connect to the database
ERROR - 2017-02-20 10:09:26 --> Severity: Warning --> mysqli::real_connect(): (HY000/2003): Can't connect to MySQL server on '192.168.1.135' (4) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2017-02-20 10:09:26 --> Unable to connect to the database
ERROR - 2017-02-20 10:11:38 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 10:24:25 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 10:29:44 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 10:44:00 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 11:20:13 --> Severity: Notice --> Undefined index: first_name /var/www/html/trackingnew/application/controllers/Truckstop.php 468
ERROR - 2017-02-20 11:20:13 --> Severity: Notice --> Undefined index: last_name /var/www/html/trackingnew/application/controllers/Truckstop.php 468
ERROR - 2017-02-20 11:21:19 --> Severity: Notice --> Undefined index: first_name /var/www/html/trackingnew/application/controllers/Truckstop.php 468
ERROR - 2017-02-20 11:21:19 --> Severity: Notice --> Undefined index: last_name /var/www/html/trackingnew/application/controllers/Truckstop.php 468
ERROR - 2017-02-20 11:25:45 --> Query error: Not unique table/alias: 'drivers' - Invalid query: SELECT `loads`.*, concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label) as assignedDriverName, `broker_info`.`MCNumber`, `broker_info`.`DOTNumber`, `broker_info`.`TruckCompanyName`, `broker_info`.`postingAddress`, `broker_info`.`CarrierMC`, `broker_info`.`city`, `broker_info`.`state`, `broker_info`.`zipcode`
FROM `loads`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `drivers` ON `vehicles`.`id` = `loads`.`vehicle_id`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
WHERE `loads`.`id` = '4662'
ERROR - 2017-02-20 11:29:27 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 11:30:24 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 11:31:16 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 12:31:16 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 13:26:41 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/models/Job.php 673
ERROR - 2017-02-20 13:28:07 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 14:19:35 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 14:31:51 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 2465
ERROR - 2017-02-20 14:31:52 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 2465
ERROR - 2017-02-20 14:31:52 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 2465
ERROR - 2017-02-20 14:33:22 --> Severity: Parsing Error --> syntax error, unexpected ';' /var/www/html/trackingnew/application/controllers/Truckstop.php 2465
ERROR - 2017-02-20 14:34:19 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 14:35:24 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 961
ERROR - 2017-02-20 14:36:13 --> Severity: Notice --> Undefined variable: truckAverage /var/www/html/trackingnew/application/controllers/Truckstop.php 912
ERROR - 2017-02-20 14:36:13 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 963
ERROR - 2017-02-20 15:36:09 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 15:49:47 --> Query error: Unknown column 'loads.driver_id' in 'on clause' - Invalid query: SELECT CONCAT( drivers.first_name, " + ", `team`.`first_name`, " - ", vehicles.label) AS driverName, `vehicles`.`id`, `label`, `vehicle_type`, `cargo_capacity`, `cargo_bay_l`, `cargo_bay_w`, `drivers`.`first_name`, `drivers`.`last_name`, `fuel_consumption`, `driver_id`, `vehicles`.`vehicle_image`, `drivers`.`profile_image`
FROM `vehicles`
JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `drivers` as `team` ON `teams`.`id` = `loads`.`second_driver_id`
WHERE `vehicles`.`driver_type` = 'team'
AND `vehicles`.`id` = '42'
AND `driver_id` = '160'
AND `team_driver_id` = '162'
ERROR - 2017-02-20 15:50:22 --> Query error: Unknown column 'loads.driver_id' in 'on clause' - Invalid query: SELECT CONCAT( drivers.first_name, " + ", `team`.`first_name`, " - ", vehicles.label) AS driverName, `vehicles`.`id`, `label`, `vehicle_type`, `cargo_capacity`, `cargo_bay_l`, `cargo_bay_w`, `drivers`.`first_name`, `drivers`.`last_name`, `fuel_consumption`, `driver_id`, `vehicles`.`vehicle_image`, `drivers`.`profile_image`
FROM `vehicles`
JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `drivers` as `team` ON `teams`.`id` = `loads`.`second_driver_id`
WHERE `vehicles`.`driver_type` = 'team'
AND `vehicles`.`id` = '42'
ERROR - 2017-02-20 15:51:39 --> Query error: Column 'driver_id' in field list is ambiguous - Invalid query: SELECT CONCAT( drivers.first_name, " + ", `team`.`first_name`, " - ", vehicles.label) AS driverName, `vehicles`.`id`, `label`, `vehicle_type`, `cargo_capacity`, `cargo_bay_l`, `cargo_bay_w`, `drivers`.`first_name`, `drivers`.`last_name`, `fuel_consumption`, `driver_id`, `vehicles`.`vehicle_image`, `drivers`.`profile_image`
FROM `loads`
JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `drivers` as `team` ON `teams`.`id` = `loads`.`second_driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`id` = '10801'
ERROR - 2017-02-20 15:52:28 --> Query error: Unknown column 'teams.id' in 'on clause' - Invalid query: SELECT CONCAT( drivers.first_name, " + ", `team`.`first_name`, " - ", vehicles.label) AS driverName, `vehicles`.`id`, `label`, `vehicle_type`, `cargo_capacity`, `cargo_bay_l`, `cargo_bay_w`, `drivers`.`first_name`, `drivers`.`last_name`, `fuel_consumption`, `vehicles`.`driver_id`, `vehicles`.`vehicle_image`, `drivers`.`profile_image`
FROM `loads`
JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `drivers` as `team` ON `teams`.`id` = `loads`.`second_driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`id` = '10801'
ERROR - 2017-02-20 15:54:38 --> Query error: Unknown column 'teams.id' in 'on clause' - Invalid query: SELECT CONCAT( drivers.first_name, " + ", `team`.`first_name`, " - ", vehicles.label) AS driverName, `vehicles`.`id`, `label`, `vehicle_type`, `cargo_capacity`, `cargo_bay_l`, `cargo_bay_w`, `drivers`.`first_name`, `drivers`.`last_name`, `fuel_consumption`, `vehicles`.`driver_id`, `vehicles`.`vehicle_image`, `drivers`.`profile_image`
FROM `loads`
JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `drivers` as `team` ON `loads`.`second_driver_id` = `teams`.`id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`id` = '10801'
ERROR - 2017-02-20 16:21:56 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 16:22:45 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 16:24:02 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 16:25:01 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 16:32:44 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 16:34:53 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 16:35:17 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 16:47:46 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 16:50:17 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 17:15:49 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 17:18:53 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 17:20:12 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 17:34:25 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName), `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:34:29 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName), `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:34:33 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName), `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:35:41 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) vehicles.id as vehicleID
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:35:42 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) vehicles.id as vehicleID
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:35:42 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) vehicles.id as vehicleID
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:35:42 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) vehicles.id as vehicleID
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:35:43 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) vehicles.id as vehicleID
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:36:05 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:36:05 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:36:06 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:39:18 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-',' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName) ELSE (concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:39:34 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:16 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:17 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:18 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:18 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:18 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:18 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:18 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:18 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:18 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:19 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:19 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:19 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:19 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:19 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:19 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:40:52 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`O' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', ''), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:41:05 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAd' at line 1 - Invalid query: SELECT IF (loads.driver_type = 'team', concat(drivers.first_name, ' ', `drivers`.`last_name`, '-', vehicles.label) as driverName, ' '), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:41:48 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ""), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAdd' at line 1 - Invalid query: SELECT IF (loads.driver_type = team, concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label) as driverName, ""), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:41:48 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as driverName, ""), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAdd' at line 1 - Invalid query: SELECT IF (loads.driver_type = team, concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label) as driverName, ""), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:42:03 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'drivers.last_name"-"vehicles.label) as driverName, ""), `loads`.`LoadType`, `loa' at line 1 - Invalid query: SELECT IF (loads.driver_type = team, concat(drivers.first_name" "drivers.last_name"-"vehicles.label) as driverName, ""), `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:46 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:46 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:47 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:47 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:47 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:47 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:47 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:47 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:47 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:47 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:47 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:47 --> Query error: Unknown column 'amount' in 'field list' - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -amount
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:45:59 --> Query error: Column 'driver_type' in field list is ambiguous - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -driver_type
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 17:46:01 --> Query error: Column 'driver_type' in field list is ambiguous - Invalid query: SELECT case loads.driver_type
			when "team" then concat(drivers.first_name, " ", `drivers`.`last_name`, "-", vehicles.label)
			when "N" then -driver_type
		end as  driverName, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`PickupAddress`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`DeliveryDate`, `loads`.`JobStatus`, `loads`.`truckstopID`, `loads`.`id`, `loads`.`deadmiles`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`invoiceNo`, `loads`.`load_source`, `loads`.`ready_for_invoice`, `broker_info`.`TruckCompanyName`, `vehicles`.`id` as `vehicleID`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` ON `drivers`.`id` = `loads`.`driver_id`
LEFT JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `loads`.`vehicle_id` !=0
AND `loads`.`delete_status` =0
AND `loads`.`id` NOT IN('10001', '10477', '10478', '10479', '10480', '10554', '10572', '10575', '10596', '10595', '10601', '10606', '10608', '10622', '10630', '10643', '10644', '10700', '10621', '10717', '10597', '10623', '10678', '10594', '10680', '10733', '10613', '10676', '10646', '10683', '10775', '10591', '10652', '10677', '10650', '10640', '10633', '10782', '10655', '10732', '10771', '10642', '10654', '10689', '10703', '10801', '10699', '10661', '10611', '10766', '10752', '10723', '10688', '10541', '10581', '10851')
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-20 18:44:44 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 18:46:46 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 18:50:44 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 18:51:48 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 18:52:59 --> Severity: Notice --> Undefined index: PointOfContactPhone /var/www/html/trackingnew/application/models/Job.php 876
ERROR - 2017-02-20 18:52:59 --> Severity: Notice --> Undefined index: TruckCompanyEmail /var/www/html/trackingnew/application/models/Job.php 877
ERROR - 2017-02-20 18:52:59 --> Severity: Notice --> Undefined index: TruckCompanyPhone /var/www/html/trackingnew/application/models/Job.php 878
ERROR - 2017-02-20 18:52:59 --> Severity: Notice --> Undefined index: TruckCompanyFax /var/www/html/trackingnew/application/models/Job.php 879
ERROR - 2017-02-20 18:54:41 --> Severity: Notice --> Undefined index: TruckCompanyEmail /var/www/html/trackingnew/application/models/Job.php 877
ERROR - 2017-02-20 18:54:41 --> Severity: Notice --> Undefined index: TruckCompanyPhone /var/www/html/trackingnew/application/models/Job.php 878
ERROR - 2017-02-20 18:54:41 --> Severity: Notice --> Undefined index: TruckCompanyFax /var/www/html/trackingnew/application/models/Job.php 879
ERROR - 2017-02-20 18:55:18 --> Severity: Notice --> Undefined index: TruckCompanyPhone /var/www/html/trackingnew/application/models/Job.php 878
ERROR - 2017-02-20 18:55:18 --> Severity: Notice --> Undefined index: TruckCompanyFax /var/www/html/trackingnew/application/models/Job.php 879
ERROR - 2017-02-20 19:21:04 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-20 19:34:22 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
