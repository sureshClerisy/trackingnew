<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-01-12 09:55:31 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-12 11:02:50 --> Severity: Parsing Error --> syntax error, unexpected 'echo' (T_ECHO) /var/www/html/trackingnew/application/controllers/Truckstop.php 2065
ERROR - 2017-01-12 11:11:09 --> Query error: Unknown column 'order_date' in 'where clause' - Invalid query: SELECT `PickupDate`, `DeliveryDate`, `id`
FROM `loads`
WHERE `PickupDate` = '2017-01-12'
OR `PickupDate` >= '2017-01-12'
OR `order_date` <= '2017-01-12'
ORDER BY `id` DESC
ERROR - 2017-01-12 11:13:59 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '`NULL`
OR `DeliveryDate` > `IS` `NULL`
ORDER BY `id` DESC' at line 4 - Invalid query: SELECT `PickupDate`, `DeliveryDate`, `id`
FROM `loads`
WHERE `PickupDate` IS NULL
OR `PickupDate` < `IS` `NULL`
OR `DeliveryDate` > `IS` `NULL`
ORDER BY `id` DESC
ERROR - 2017-01-12 11:27:10 --> Query error: Unknown column '2017-01-12' in 'where clause' - Invalid query: SELECT `PickupDate`, `DeliveryDate`, `id`
FROM `loads`
WHERE (`PickupDate` = `2017-01-12`) OR (`PickupDate` <= `2017-01-12` AND `DeliveryDate` >= `2017-01-12`)
ORDER BY `id` DESC
ERROR - 2017-01-12 11:28:44 --> Query error: Unknown column '2017-01-12' in 'where clause' - Invalid query: SELECT `PickupDate`, `DeliveryDate`, `id`
FROM `loads`
WHERE (`PickupDate` = `2017-01-12`) OR (`PickupDate` <= `2017-01-12` AND `DeliveryDate` >= `2017-01-12`)
ORDER BY `id` DESC
ERROR - 2017-01-12 11:53:44 --> Severity: Notice --> Undefined variable: searchType /var/www/html/trackingnew/application/controllers/Truckstop.php 2101
ERROR - 2017-01-12 12:16:06 --> Severity: Notice --> Undefined variable: newDistance /var/www/html/trackingnew/application/controllers/Truckstop.php 2100
ERROR - 2017-01-12 12:16:06 --> Severity: Notice --> Undefined variable: newDistance /var/www/html/trackingnew/application/controllers/Truckstop.php 2100
ERROR - 2017-01-12 12:16:22 --> Severity: Notice --> Undefined variable: newDistance /var/www/html/trackingnew/application/controllers/Truckstop.php 2100
ERROR - 2017-01-12 12:16:22 --> Severity: Notice --> Undefined variable: newDistance /var/www/html/trackingnew/application/controllers/Truckstop.php 2100
ERROR - 2017-01-12 12:16:32 --> Severity: Notice --> Undefined variable: newDistance /var/www/html/trackingnew/application/controllers/Truckstop.php 2100
ERROR - 2017-01-12 12:16:32 --> Severity: Notice --> Undefined variable: newDistance /var/www/html/trackingnew/application/controllers/Truckstop.php 2100
ERROR - 2017-01-12 13:25:15 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-12 13:30:31 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE), expecting function (T_FUNCTION) /var/www/html/trackingnew/application/controllers/Truckstop.php 2129
ERROR - 2017-01-12 13:30:39 --> Severity: Parsing Error --> syntax error, unexpected 'else' (T_ELSE), expecting function (T_FUNCTION) /var/www/html/trackingnew/application/controllers/Truckstop.php 2129
ERROR - 2017-01-12 13:42:12 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-12 13:50:44 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-12 13:50:53 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-12 13:59:43 --> Severity: Parsing Error --> syntax error, unexpected 'if' (T_IF) /var/www/html/trackingnew/application/controllers/Truckstop.php 2127
ERROR - 2017-01-12 14:12:57 --> Severity: Parsing Error --> syntax error, unexpected ';', expecting ')' /var/www/html/trackingnew/application/controllers/Truckstop.php 597
ERROR - 2017-01-12 14:21:43 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ')
AND  (`PickupDate` = '2017-01-17') OR (`PickupDate` <= '2017-01-17' AND `Deliv' at line 4 - Invalid query: SELECT `PickupDate`, `DeliveryDate`, `id`
FROM `loads`
WHERE   (
 )
AND  (`PickupDate` = '2017-01-17') OR (`PickupDate` <= '2017-01-17' AND `DeliveryDate` >= '2017-01-17')
AND `vehicle_id` = '11'
ORDER BY `id` DESC
ERROR - 2017-01-12 16:35:32 --> Severity: Error --> Call to undefined method CI_DB_mysqli_driver::ordr_by() /var/www/html/trackingnew/application/models/Vehicle.php 650
ERROR - 2017-01-12 16:47:13 --> Query error: FUNCTION tracking_new.str_to_time does not exist - Invalid query: SELECT `DestinationCity`, `DestinationState`, `DestinationCountry`, str_to_time(pickDate) day
FROM `loads`
WHERE `vehicle_id` = '11'
ORDER BY `day` DESC
ERROR - 2017-01-12 16:47:32 --> Query error: FUNCTION tracking_new.strtotime does not exist - Invalid query: SELECT `DestinationCity`, `DestinationState`, `DestinationCountry`, strtotime(pickDate) day
FROM `loads`
WHERE `vehicle_id` = '11'
ORDER BY `day` DESC
ERROR - 2017-01-12 16:54:17 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ' strtotime(pickDate)) as day
FROM `loads`
WHERE `vehicle_id` = '11'
ORDER BY `da' at line 1 - Invalid query: SELECT `DestinationCity`, `DestinationState`, `DestinationCountry`, date('%Y-%m-%d', strtotime(pickDate)) as day
FROM `loads`
WHERE `vehicle_id` = '11'
ORDER BY `day` DESC
ERROR - 2017-01-12 17:13:47 --> Severity: Notice --> Undefined variable: vehicleId /var/www/html/trackingnew/application/controllers/Iterationloads.php 975
ERROR - 2017-01-12 17:23:45 --> Query error: Unknown column 'abbreviation' in 'field list' - Invalid query: SELECT `DestinationCity`, `DestinationState`, `DestinationCountry`, `abbreviation`
FROM `loads`
JOIN `vehicles` ON `vehicles`.`id` = `loads`.`vehicle_id`
WHERE `vehicle_id` = '35'
ORDER BY `PickupDate` DESC
ERROR - 2017-01-12 17:27:25 --> Query error: Unknown column 'vehicles.vehicle_type' in 'on clause' - Invalid query: SELECT `DestinationCity`, `DestinationState`, `DestinationCountry`, `equipment_types`.`abbrevation`
FROM `loads`
LEFT JOIN `equipment_types` ON `equipment_types`.`name` = `vehicles`.`vehicle_type`
WHERE `vehicle_id` = '35'
ORDER BY `PickupDate` DESC
ERROR - 2017-01-12 17:28:44 --> Severity: Warning --> array_push() expects parameter 1 to be array, null given /var/www/html/trackingnew/application/controllers/Iterationloads.php 978
ERROR - 2017-01-12 17:35:08 --> Severity: Notice --> Undefined offset: 4 /var/www/html/trackingnew/application/controllers/Iterationloads.php 1019
ERROR - 2017-01-12 17:35:42 --> Severity: Notice --> Undefined offset: 4 /var/www/html/trackingnew/application/controllers/Iterationloads.php 1019
ERROR - 2017-01-12 17:49:23 --> Severity: Parsing Error --> syntax error, unexpected '}' /var/www/html/trackingnew/application/controllers/Iterationloads.php 983
ERROR - 2017-01-12 18:05:01 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '\%d-%m-%Y\), \%Y%m%d\) as day
FROM `loads`
WHERE `vehicle_id` = '11'
ORDER BY `d' at line 1 - Invalid query: SELECT `DestinationCity`, `DestinationState`, `DestinationCountry`, DATE_FORMAT(STR_TO_DATE(date, \%d-%m-%Y\), \%Y%m%d\) as day
FROM `loads`
WHERE `vehicle_id` = '11'
ORDER BY `day` DESC
ERROR - 2017-01-12 18:05:27 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '\%d-%m-%Y\), \%Y%m%d\) as day
FROM `loads`
WHERE `vehicle_id` = '11'
ORDER BY `d' at line 1 - Invalid query: SELECT `DestinationCity`, `DestinationState`, `DestinationCountry`, DATE_FORMAT(STR_TO_DATE(pickDate, \%d-%m-%Y\), \%Y%m%d\) as day
FROM `loads`
WHERE `vehicle_id` = '11'
ORDER BY `day` DESC
ERROR - 2017-01-12 18:06:43 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '%m/%d/%y), %Y-%m-%d) as day
FROM `loads`
WHERE `vehicle_id` = '11'
ORDER BY `day' at line 1 - Invalid query: SELECT `DestinationCity`, `DestinationState`, `DestinationCountry`, DATE_FORMAT(STR_TO_DATE(pickDate, %m/%d/%y), %Y-%m-%d) as day
FROM `loads`
WHERE `vehicle_id` = '11'
ORDER BY `day` DESC
ERROR - 2017-01-12 19:12:51 --> Severity: Parsing Error --> syntax error, unexpected '$i' (T_VARIABLE) /var/www/html/trackingnew/application/controllers/Billings.php 371
