<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-01-19 09:52:30 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-19 13:24:02 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-19 13:52:36 --> Severity: Notice --> Undefined variable: vehicleType /var/www/html/trackingnew/application/controllers/Vehicles.php 69
ERROR - 2017-01-19 16:16:02 --> Severity: Parsing Error --> syntax error, unexpected 'return' (T_RETURN), expecting function (T_FUNCTION) /var/www/html/trackingnew/application/controllers/Iterationloads.php 387
ERROR - 2017-01-19 16:24:06 --> Severity: Notice --> Undefined index: abbrevation /var/www/html/trackingnew/application/controllers/Iterationloads.php 1111
ERROR - 2017-01-19 16:38:48 --> Severity: Notice --> Undefined variable: vehicleID /var/www/html/trackingnew/application/controllers/Iterationloads.php 661
ERROR - 2017-01-19 17:16:39 --> Severity: Parsing Error --> syntax error, unexpected '$show' (T_VARIABLE) /var/www/html/trackingnew/application/controllers/Iterationloads.php 93
ERROR - 2017-01-19 17:16:59 --> Severity: Parsing Error --> syntax error, unexpected '$show' (T_VARIABLE) /var/www/html/trackingnew/application/controllers/Iterationloads.php 93
ERROR - 2017-01-19 17:17:01 --> Severity: Parsing Error --> syntax error, unexpected '$show' (T_VARIABLE) /var/www/html/trackingnew/application/controllers/Iterationloads.php 93
ERROR - 2017-01-19 17:17:17 --> Severity: Parsing Error --> syntax error, unexpected 'pr' (T_STRING) /var/www/html/trackingnew/application/controllers/Iterationloads.php 91
ERROR - 2017-01-19 17:17:27 --> Severity: Parsing Error --> syntax error, unexpected '$show' (T_VARIABLE) /var/www/html/trackingnew/application/controllers/Iterationloads.php 91
ERROR - 2017-01-19 17:30:10 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'as vehicleType)
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id`=`vehicles`' at line 1 - Invalid query: SELECT `vehicles`.`label`, `vehicles`.`vin`, `vehicles`.`model`, `vehicles`.`vehicle_type`, `vehicles`.`permitted_speed`, `vehicles`.`cargo_capacity`, `vehicles`.`id`, concat(first_name, " ", last_name) as driverName, Group_Concat(equipment_types.name as vehicleType)
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id`=`vehicles`.`driver_id`
LEFT JOIN `equipment_types` ON `FIND_IN_SET`(equipment_types.abbrevation , vehicles.vehicle_type)
ERROR - 2017-01-19 17:34:34 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '.`id`, concat(first_name, " ", last_name) as driverName, `equipment_types`.`name' at line 1 - Invalid query: SELECT `vehicles`.`label`, `vehicles`.`vin`, `vehicles`.`model`, `vehicles`.`vehicle_type`, `vehicles`.`permitted_speed`, `vehicles`.`cargo_capacity`, `DISTINCT` `vehicles`.`id`, concat(first_name, " ", last_name) as driverName, `equipment_types`.`name` as `vehicleType`
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id`=`vehicles`.`driver_id`
INNER JOIN `equipment_types` ON `FIND_IN_SET`(equipment_types.abbrevation , vehicles.vehicle_type)
ERROR - 2017-01-19 17:35:02 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '.`id`, `vehicles`.`label`, `vehicles`.`vin`, `vehicles`.`model`, `vehicles`.`veh' at line 1 - Invalid query: SELECT `DISTINCT` `vehicles`.`id`, `vehicles`.`label`, `vehicles`.`vin`, `vehicles`.`model`, `vehicles`.`vehicle_type`, `vehicles`.`permitted_speed`, `vehicles`.`cargo_capacity`, concat(first_name, " ", last_name) as driverName, `equipment_types`.`name` as `vehicleType`
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id`=`vehicles`.`driver_id`
INNER JOIN `equipment_types` ON `FIND_IN_SET`(equipment_types.abbrevation , vehicles.vehicle_type)
ERROR - 2017-01-19 18:45:53 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE) /var/www/html/trackingnew/application/models/Vehicle.php 635
ERROR - 2017-01-19 18:45:55 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE) /var/www/html/trackingnew/application/models/Vehicle.php 635
ERROR - 2017-01-19 18:45:56 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE) /var/www/html/trackingnew/application/models/Vehicle.php 635
ERROR - 2017-01-19 18:45:58 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE) /var/www/html/trackingnew/application/models/Vehicle.php 635
ERROR - 2017-01-19 18:46:00 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE) /var/www/html/trackingnew/application/models/Vehicle.php 635
ERROR - 2017-01-19 18:46:01 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE) /var/www/html/trackingnew/application/models/Vehicle.php 635
ERROR - 2017-01-19 18:46:03 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE) /var/www/html/trackingnew/application/models/Vehicle.php 635
ERROR - 2017-01-19 18:46:05 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE) /var/www/html/trackingnew/application/models/Vehicle.php 635
ERROR - 2017-01-19 18:46:07 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE) /var/www/html/trackingnew/application/models/Vehicle.php 635
ERROR - 2017-01-19 18:46:14 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE) /var/www/html/trackingnew/application/models/Vehicle.php 635
ERROR - 2017-01-19 19:33:23 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'REPLACE(vehicleType, ", ", " ")
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`' at line 1 - Invalid query: SELECT DISTINCT vehicles.id, vehicles.label, vehicles.vin, vehicles.model, vehicles.vehicle_type, vehicles.permitted_speed, vehicles.cargo_capacity, concat(first_name, " ", last_name) as driverName, GROUP_CONCAT(equipment_types.name) as REPLACE(vehicleType, ", ", " ")
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id`=`vehicles`.`driver_id`
INNER JOIN `equipment_types` ON `FIND_IN_SET`(equipment_types.abbrevation , vehicles.vehicle_type) > 0
GROUP BY `vehicles`.`id`
ERROR - 2017-01-19 19:33:39 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'REPLACE(vehicleType, ", ", " ")
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`' at line 1 - Invalid query: SELECT DISTINCT vehicles.id, vehicles.label, vehicles.vin, vehicles.model, vehicles.vehicle_type, vehicles.permitted_speed, vehicles.cargo_capacity, concat(first_name, " ", last_name) as driverName, GROUP_CONCAT(equipment_types.name) as REPLACE(vehicleType, ", ", " ")
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id`=`vehicles`.`driver_id`
INNER JOIN `equipment_types` ON `FIND_IN_SET`(equipment_types.abbrevation , vehicles.vehicle_type) > 0
GROUP BY `vehicles`.`id`
ERROR - 2017-01-19 19:33:45 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'REPLACE(vehicleType, ", ", " ")
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`' at line 1 - Invalid query: SELECT DISTINCT vehicles.id, vehicles.label, vehicles.vin, vehicles.model, vehicles.vehicle_type, vehicles.permitted_speed, vehicles.cargo_capacity, concat(first_name, " ", last_name) as driverName, GROUP_CONCAT(equipment_types.name) as REPLACE(vehicleType, ", ", " ")
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id`=`vehicles`.`driver_id`
INNER JOIN `equipment_types` ON `FIND_IN_SET`(equipment_types.abbrevation , vehicles.vehicle_type) > 0
GROUP BY `vehicles`.`id`
ERROR - 2017-01-19 19:34:12 --> Query error: Unknown column 'vehicleType' in 'field list' - Invalid query: SELECT DISTINCT vehicles.id, vehicles.label, vehicles.vin, vehicles.model, vehicles.vehicle_type, vehicles.permitted_speed, vehicles.cargo_capacity, concat(first_name, " ", last_name) as driverName, GROUP_CONCAT(equipment_types.name) as vehicleType, REPLACE(vehicleType, ", ", " ")
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id`=`vehicles`.`driver_id`
INNER JOIN `equipment_types` ON `FIND_IN_SET`(equipment_types.abbrevation , vehicles.vehicle_type) > 0
GROUP BY `vehicles`.`id`
ERROR - 2017-01-19 19:37:28 --> Query error: Unknown column 'pickday' in 'where clause' - Invalid query: SELECT `DestinationCity`, `DestinationState`, `DestinationCountry`, `DeliveryDate`, DATE_FORMAT(STR_TO_DATE(pickDate, '%m/%d/%y'), '%Y-%m-%d') as pickday
FROM `loads`
WHERE `vehicle_id` = '124'
AND `pickday` < '01/10/17'
AND `delete_status` =0
AND `JobStatus` != 'cancel'
ORDER BY `pickday` DESC
ERROR - 2017-01-19 19:42:14 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '\'%m/%d/%y\'), \'%Y-%m-%d\') > '2017-01-10'
ORDER BY `pickday` DESC' at line 6 - Invalid query: SELECT `DestinationCity`, `DestinationState`, `DestinationCountry`, `DeliveryDate`, DATE_FORMAT(STR_TO_DATE(pickDate, '%m/%d/%y'), '%Y-%m-%d') as pickday
FROM `loads`
WHERE `vehicle_id` = '124'
AND `delete_status` =0
AND `JobStatus` != 'cancel'
AND DATE_FORMAT(STR_TO_DATE(pickDate, \'%m/%d/%y\'), \'%Y-%m-%d\') > '2017-01-10'
ORDER BY `pickday` DESC
ERROR - 2017-01-19 19:43:35 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '\'%m/%d/%y\') > '01/10/17'
ORDER BY `pickday` DESC' at line 6 - Invalid query: SELECT `DestinationCity`, `DestinationState`, `DestinationCountry`, `DeliveryDate`, DATE_FORMAT(STR_TO_DATE(pickDate, '%m/%d/%y'), '%Y-%m-%d') as pickday
FROM `loads`
WHERE `vehicle_id` = '124'
AND `delete_status` =0
AND `JobStatus` != 'cancel'
AND STR_TO_DATE(pickDate, \'%m/%d/%y\') > '01/10/17'
ORDER BY `pickday` DESC
