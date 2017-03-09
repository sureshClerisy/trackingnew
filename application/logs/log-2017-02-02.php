<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-02-02 09:52:47 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-02-02 10:22:49 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-02-02 10:23:11 --> Query error: Unknown column 'vehicles.team_driver_id' in 'on clause' - Invalid query: SELECT DISTINCT CONCAT(concat( drivers.first_name, " ", `drivers`.`last_name` ), " + ", concat(team.first_name, " ", team.last_name)) AS teamDriverName, vehicles.id, vehicles.label, CONCAT(users.first_name, " ", users.last_name) AS dispatcher, vehicles.vin, vehicles.model, vehicles.vehicle_type, vehicles.vehicle_status, vehicles.permitted_speed, vehicles.cargo_capacity, concat(drivers.first_name, " ", drivers.last_name) as driverName, GROUP_CONCAT(equipment_types.name) as vehicleType
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id`=`vehicles`.`driver_id`
LEFT JOIN `equipment_types` ON `FIND_IN_SET`(equipment_types.abbrevation , vehicles.vehicle_type) > 0
LEFT JOIN `users` ON `drivers`.`user_id` = `users`.`id`
LEFT JOIN `drivers` as `team` ON `vehicles`.`team_driver_id` = `team`.`id`
GROUP BY `vehicles`.`id`
ERROR - 2017-02-02 11:35:36 --> Severity: Parsing Error --> syntax error, unexpected '<<' (T_SL), expecting ')' /var/www/html/trackingnew/application/config/database.php 78
ERROR - 2017-02-02 11:35:58 --> Severity: Warning --> mysqli::real_connect(): (28000/1045): Access denied for user 'root'@'localhost' (using password: NO) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2017-02-02 11:35:58 --> Unable to connect to the database
ERROR - 2017-02-02 11:36:49 --> Severity: Notice --> Undefined index: Mileage /var/www/html/trackingnew/application/controllers/Truckstop.php 1215
ERROR - 2017-02-02 11:44:15 --> Severity: Compile Error --> Cannot use isset() on the result of an expression (you can use "null !== expression" instead) /var/www/html/trackingnew/application/controllers/Truckstop.php 1072
ERROR - 2017-02-02 11:44:55 --> Severity: Notice --> Undefined variable: extraStopsAdded /var/www/html/trackingnew/application/controllers/Truckstop.php 1073
ERROR - 2017-02-02 11:44:55 --> Severity: Notice --> Undefined index: Mileage /var/www/html/trackingnew/application/controllers/Truckstop.php 1204
ERROR - 2017-02-02 11:51:46 --> Severity: Notice --> Undefined index: originAddress_0 /var/www/html/trackingnew/application/controllers/Truckstop.php 1146
ERROR - 2017-02-02 12:00:43 --> Severity: Notice --> Undefined variable: xtraInfo /var/www/html/trackingnew/application/controllers/Truckstop.php 1237
ERROR - 2017-02-02 12:00:43 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1379
ERROR - 2017-02-02 12:00:43 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1398
ERROR - 2017-02-02 12:00:43 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1241
ERROR - 2017-02-02 12:00:43 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1242
ERROR - 2017-02-02 12:00:43 --> Severity: Notice --> Undefined variable: extraStopTime /var/www/html/trackingnew/application/controllers/Truckstop.php 1244
ERROR - 2017-02-02 12:00:43 --> Severity: Notice --> Undefined variable: extraStopDist /var/www/html/trackingnew/application/controllers/Truckstop.php 1244
ERROR - 2017-02-02 12:00:43 --> Severity: Notice --> Undefined variable: xtraInfo /var/www/html/trackingnew/application/controllers/Truckstop.php 1244
ERROR - 2017-02-02 12:22:03 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1379
ERROR - 2017-02-02 12:22:03 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1398
ERROR - 2017-02-02 12:22:03 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1241
ERROR - 2017-02-02 12:22:03 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1242
ERROR - 2017-02-02 12:22:03 --> Severity: Notice --> Undefined variable: extraStopTime /var/www/html/trackingnew/application/controllers/Truckstop.php 1244
ERROR - 2017-02-02 12:22:03 --> Severity: Notice --> Undefined variable: extraStopDist /var/www/html/trackingnew/application/controllers/Truckstop.php 1244
ERROR - 2017-02-02 12:22:03 --> Severity: Notice --> Undefined variable: xtraInfo /var/www/html/trackingnew/application/controllers/Truckstop.php 1244
ERROR - 2017-02-02 12:23:46 --> Severity: Notice --> Undefined variable: extraStopTime /var/www/html/trackingnew/application/controllers/Truckstop.php 1244
ERROR - 2017-02-02 12:23:46 --> Severity: Notice --> Undefined variable: extraStopDist /var/www/html/trackingnew/application/controllers/Truckstop.php 1244
ERROR - 2017-02-02 12:23:46 --> Severity: Notice --> Undefined variable: xtraInfo /var/www/html/trackingnew/application/controllers/Truckstop.php 1244
ERROR - 2017-02-02 12:25:13 --> Severity: Notice --> Undefined variable: extraStopDist /var/www/html/trackingnew/application/controllers/Truckstop.php 1244
ERROR - 2017-02-02 12:31:48 --> Severity: Notice --> Undefined variable: xtraInfo /var/www/html/trackingnew/application/controllers/Truckstop.php 1229
ERROR - 2017-02-02 12:31:48 --> Severity: Notice --> Undefined variable: extraStopDist /var/www/html/trackingnew/application/controllers/Truckstop.php 1234
ERROR - 2017-02-02 12:32:26 --> Severity: Notice --> Undefined variable: extraStopDist /var/www/html/trackingnew/application/controllers/Truckstop.php 1234
ERROR - 2017-02-02 13:09:47 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 1162
ERROR - 2017-02-02 13:09:47 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/controllers/Truckstop.php 1162
ERROR - 2017-02-02 13:15:14 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 1162
ERROR - 2017-02-02 13:15:14 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/controllers/Truckstop.php 1162
ERROR - 2017-02-02 13:16:07 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 1162
ERROR - 2017-02-02 13:16:07 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/controllers/Truckstop.php 1162
ERROR - 2017-02-02 13:19:52 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 1162
ERROR - 2017-02-02 13:19:52 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/controllers/Truckstop.php 1162
ERROR - 2017-02-02 13:56:16 --> Severity: Notice --> Undefined index: vehicleId /var/www/html/trackingnew/application/controllers/Truckstop.php 1411
ERROR - 2017-02-02 13:56:16 --> Severity: Notice --> Undefined index: originalDistance /var/www/html/trackingnew/application/controllers/Truckstop.php 1426
ERROR - 2017-02-02 13:56:16 --> Severity: Notice --> Undefined variable: newDistance /var/www/html/trackingnew/application/controllers/Truckstop.php 1441
ERROR - 2017-02-02 14:01:28 --> Severity: Parsing Error --> syntax error, unexpected ';' /var/www/html/trackingnew/application/controllers/Truckstop.php 1411
ERROR - 2017-02-02 14:06:42 --> Severity: Notice --> Undefined variable: perExtraStopCharges /var/www/html/trackingnew/application/controllers/Truckstop.php 1459
ERROR - 2017-02-02 14:06:42 --> Severity: Notice --> Undefined variable: newDistanceCal /var/www/html/trackingnew/application/controllers/Truckstop.php 1464
ERROR - 2017-02-02 14:06:42 --> Severity: Notice --> Undefined variable: newDistanceCal /var/www/html/trackingnew/application/controllers/Truckstop.php 1466
ERROR - 2017-02-02 14:08:11 --> Severity: Notice --> Undefined variable: newDistanceCal /var/www/html/trackingnew/application/controllers/Truckstop.php 1471
ERROR - 2017-02-02 14:08:11 --> Severity: Notice --> Undefined variable: newDistanceCal /var/www/html/trackingnew/application/controllers/Truckstop.php 1473
ERROR - 2017-02-02 14:08:27 --> Severity: Notice --> Undefined variable: newDistanceCal /var/www/html/trackingnew/application/controllers/Truckstop.php 1471
ERROR - 2017-02-02 15:55:25 --> Severity: Parsing Error --> syntax error, unexpected '<=' (T_IS_SMALLER_OR_EQUAL) /var/www/html/trackingnew/application/controllers/Truckstop.php 2577
ERROR - 2017-02-02 15:55:25 --> Severity: Parsing Error --> syntax error, unexpected '<=' (T_IS_SMALLER_OR_EQUAL) /var/www/html/trackingnew/application/controllers/Truckstop.php 2577
ERROR - 2017-02-02 15:55:42 --> Severity: Parsing Error --> syntax error, unexpected '<=' (T_IS_SMALLER_OR_EQUAL) /var/www/html/trackingnew/application/controllers/Truckstop.php 2577
ERROR - 2017-02-02 15:57:01 --> Severity: Parsing Error --> syntax error, unexpected '<=' (T_IS_SMALLER_OR_EQUAL) /var/www/html/trackingnew/application/controllers/Truckstop.php 2577
ERROR - 2017-02-02 15:58:23 --> Severity: Parsing Error --> syntax error, unexpected '<=' (T_IS_SMALLER_OR_EQUAL) /var/www/html/trackingnew/application/controllers/Truckstop.php 2577
ERROR - 2017-02-02 15:58:45 --> Severity: Parsing Error --> syntax error, unexpected '<' /var/www/html/trackingnew/application/controllers/Truckstop.php 2577
ERROR - 2017-02-02 16:00:44 --> Severity: Notice --> Undefined index: DeliveryDate /var/www/html/trackingnew/application/controllers/Truckstop.php 2544
ERROR - 2017-02-02 16:26:15 --> Severity: Parsing Error --> syntax error, unexpected 'if' (T_IF) /var/www/html/trackingnew/application/controllers/Truckstop.php 2570
ERROR - 2017-02-02 16:26:38 --> Severity: Parsing Error --> syntax error, unexpected '' date'' (T_CONSTANT_ENCAPSED_STRING) /var/www/html/trackingnew/application/controllers/Truckstop.php 2573
ERROR - 2017-02-02 16:56:27 --> Severity: Parsing Error --> syntax error, unexpected '{' /var/www/html/trackingnew/application/controllers/Truckstop.php 2515
ERROR - 2017-02-02 16:56:50 --> Severity: Parsing Error --> syntax error, unexpected ')' /var/www/html/trackingnew/application/controllers/Truckstop.php 2517
ERROR - 2017-02-02 17:43:48 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/models/BrokersModel.php 127
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: saveDeadMile /var/www/html/trackingnew/application/controllers/Truckstop.php 770
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: PaymentAmount /var/www/html/trackingnew/application/controllers/Truckstop.php 771
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: Weight /var/www/html/trackingnew/application/controllers/Truckstop.php 775
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: OriginCity /var/www/html/trackingnew/application/controllers/Truckstop.php 776
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: OriginState /var/www/html/trackingnew/application/controllers/Truckstop.php 776
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: DestinationCity /var/www/html/trackingnew/application/controllers/Truckstop.php 777
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: DestinationState /var/www/html/trackingnew/application/controllers/Truckstop.php 777
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: EquipmentTypes /var/www/html/trackingnew/application/controllers/Truckstop.php 783
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: EquipmentTypes /var/www/html/trackingnew/application/controllers/Truckstop.php 784
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: Width /var/www/html/trackingnew/application/controllers/Truckstop.php 787
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: Length /var/www/html/trackingnew/application/controllers/Truckstop.php 788
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: PaymentAmount /var/www/html/trackingnew/application/controllers/Truckstop.php 796
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: vehicle_id /var/www/html/trackingnew/application/controllers/Truckstop.php 803
ERROR - 2017-02-02 17:47:22 --> Severity: Notice --> Undefined index: Stops /var/www/html/trackingnew/application/controllers/Truckstop.php 822
ERROR - 2017-02-02 18:38:28 --> Query error: Unknown column 'vehicles.driver_type' in 'where clause' - Invalid query: SELECT CONCAT(concat( drivers.first_name, " ", `drivers`.`last_name` ), " + ", concat(team.first_name, " ", team.last_name), " - ", vehicles.label) AS driverName, concat( drivers.first_name, " ", `drivers`.`last_name` ) as dName, `drivers`.`id`, `label`, `vehicle_type`, `cargo_capacity`, `cargo_bay_l`, `cargo_bay_w`, `drivers`.`first_name`, `drivers`.`last_name`, `fuel_consumption`, `driver_id`, `vehicles`.`vehicle_image`, `drivers`.`profile_image`, `vehicles`.`id` as `assignedVehicleId`, `trailers`.`unit_id`, `trailers`.`id` as `trailerId`
FROM `drivers`
LEFT JOIN `vehicles` ON `vehicles`.`driver_id` = `drivers`.`id`
LEFT JOIN `trailers` ON `trailers`.`truck_id` = `vehicles`.`id`
LEFT JOIN `drivers` as `team` ON `vehicles`.`team_driver_id` = `team`.`id`
WHERE `vehicles`.`driver_type` = 'team'
AND `drivers`.`id` = '108'
ERROR - 2017-02-02 18:47:52 --> Severity: Notice --> Undefined index: extraStopDate_-1 /var/www/html/trackingnew/application/controllers/Truckstop.php 2602
ERROR - 2017-02-02 18:50:09 --> Severity: Notice --> Undefined index: extraStopDate_-1 /var/www/html/trackingnew/application/controllers/Truckstop.php 2602
ERROR - 2017-02-02 18:52:09 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1382
ERROR - 2017-02-02 18:52:09 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1401
ERROR - 2017-02-02 18:52:09 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1243
ERROR - 2017-02-02 18:52:09 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1244
ERROR - 2017-02-02 18:59:41 --> Severity: Notice --> Undefined index: deliveryTT /var/www/html/trackingnew/application/controllers/Truckstop.php 2104
