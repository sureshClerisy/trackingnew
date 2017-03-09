<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-02-16 09:52:06 --> Severity: Warning --> mysqli::real_connect(): (HY000/2003): Can't connect to MySQL server on '192.168.1.135' (113) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2017-02-16 09:52:06 --> Unable to connect to the database
ERROR - 2017-02-16 09:54:12 --> Severity: Warning --> mysqli::real_connect(): (HY000/2003): Can't connect to MySQL server on '192.168.1.135' (113) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2017-02-16 09:54:12 --> Unable to connect to the database
ERROR - 2017-02-16 11:27:26 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 11:28:15 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 11:39:07 --> Severity: Notice --> Undefined property: CI_DB_mysqli_result::$row_array /var/www/html/trackingnew/application/models/Job.php 1463
ERROR - 2017-02-16 11:39:07 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/models/Job.php 1463
ERROR - 2017-02-16 11:43:25 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:00:03 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:01:42 --> Query error: Table 'tracking_new.broker_info' doesn't exist - Invalid query: SELECT CONCAT( d.first_name, " + ", team.first_name) AS teamdriverName, CONCAT(d.first_name, " ", d.last_name) as driverName, `loads`.`driver_type`, `loads`.`id`, `loads`.`invoiceNo`, `loads`.`vehicle_id`, `loads`.`truckstopID`, `loads`.`Bond`, `broker_info`.`PointOfContactPhone`, `loads`.`equipment_options`, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`DeliveryDate`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`PickupAddress`, `loads`.`DestinationAddress`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`Weight`, `loads`.`Length`, `loads`.`JobStatus`, `loads`.`totalCost`, `loads`.`pickDate`, `loads`.`load_source`, `broker_info`.`TruckCompanyName` as `companyName`
FROM `loads`
LEFT JOIN `vehicles` as `v` ON `loads`.`vehicle_id` = `v`.`id`
LEFT JOIN `drivers` as `d` ON `v`.`driver_id` = `d`.`id`
LEFT JOIN `users` as `u` ON `d`.`user_id` = `u`.`id`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
LEFT JOIN `drivers` as `team` ON `v`.`team_driver_id` = `team`.`id`
WHERE `loads`.`vehicle_id` IN('127')
AND `delete_status` =0
ORDER BY `loads`.`PickupDate` ASC
ERROR - 2017-02-16 12:02:22 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:03:19 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:04:21 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:11:46 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:23:53 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:25:11 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:25:37 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:26:39 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:27:26 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:28:09 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:28:40 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:29:10 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:29:39 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:34:27 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:36:06 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:40:38 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:41:46 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:47:10 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:50:24 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:52:05 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 12:58:49 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:02:59 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:27:39 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:30:09 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:33:24 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:34:22 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:35:54 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:36:29 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:37:08 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:39:15 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:39:52 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:45:24 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:48:35 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 13:50:42 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 14:49:52 --> Severity: Parsing Error --> syntax error, unexpected ',' /var/www/html/trackingnew/application/controllers/Truckstop.php 2314
ERROR - 2017-02-16 15:35:05 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopEntity /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopName /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopAddress /var/www/html/trackingnew/application/views/invoice.php 231
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopPhone /var/www/html/trackingnew/application/views/invoice.php 232
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopDate /var/www/html/trackingnew/application/views/invoice.php 236
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopEntity /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopName /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopAddress /var/www/html/trackingnew/application/views/invoice.php 231
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopPhone /var/www/html/trackingnew/application/views/invoice.php 232
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopDate /var/www/html/trackingnew/application/views/invoice.php 236
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopEntity /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopName /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopAddress /var/www/html/trackingnew/application/views/invoice.php 231
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopPhone /var/www/html/trackingnew/application/views/invoice.php 232
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopDate /var/www/html/trackingnew/application/views/invoice.php 236
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopEntity /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopName /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopAddress /var/www/html/trackingnew/application/views/invoice.php 231
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopPhone /var/www/html/trackingnew/application/views/invoice.php 232
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopDate /var/www/html/trackingnew/application/views/invoice.php 236
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopEntity /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopName /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopAddress /var/www/html/trackingnew/application/views/invoice.php 231
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopPhone /var/www/html/trackingnew/application/views/invoice.php 232
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopDate /var/www/html/trackingnew/application/views/invoice.php 236
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopEntity /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopName /var/www/html/trackingnew/application/views/invoice.php 229
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopAddress /var/www/html/trackingnew/application/views/invoice.php 231
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopPhone /var/www/html/trackingnew/application/views/invoice.php 232
ERROR - 2017-02-16 16:06:07 --> Severity: Notice --> Undefined index: extraStopDate /var/www/html/trackingnew/application/views/invoice.php 236
ERROR - 2017-02-16 16:15:31 --> Severity: Parsing Error --> syntax error, unexpected '$data' (T_VARIABLE), expecting ';' /var/www/html/trackingnew/application/controllers/Assignedloads.php 335
ERROR - 2017-02-16 16:15:37 --> Severity: Parsing Error --> syntax error, unexpected '$data' (T_VARIABLE), expecting ';' /var/www/html/trackingnew/application/controllers/Assignedloads.php 335
ERROR - 2017-02-16 16:15:41 --> Severity: Parsing Error --> syntax error, unexpected '$data' (T_VARIABLE), expecting ';' /var/www/html/trackingnew/application/controllers/Assignedloads.php 335
ERROR - 2017-02-16 16:17:18 --> Severity: Notice --> Undefined index: extraStopEntity_0 /var/www/html/trackingnew/application/controllers/Assignedloads.php 337
ERROR - 2017-02-16 16:17:18 --> Severity: Notice --> Undefined index: extraStopEntity_1 /var/www/html/trackingnew/application/controllers/Assignedloads.php 337
ERROR - 2017-02-16 16:29:49 --> Severity: Parsing Error --> syntax error, unexpected ')', expecting ']' /var/www/html/trackingnew/application/controllers/Assignedloads.php 333
ERROR - 2017-02-16 16:30:56 --> Severity: Notice --> Undefined variable: Craft Equipment /var/www/html/trackingnew/application/controllers/Assignedloads.php 342
ERROR - 2017-02-16 16:30:56 --> Severity: Notice --> Undefined variable: crats villa /var/www/html/trackingnew/application/controllers/Assignedloads.php 342
ERROR - 2017-02-16 16:51:25 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 16:58:36 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 16:59:08 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 17:00:12 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 17:02:37 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 17:04:09 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 17:04:22 --> Severity: Notice --> Undefined index: driver_id /var/www/html/trackingnew/application/controllers/Truckstop.php 2405
ERROR - 2017-02-16 17:05:08 --> Severity: Notice --> Undefined index: driver_id /var/www/html/trackingnew/application/controllers/Truckstop.php 2405
ERROR - 2017-02-16 17:06:40 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 17:38:32 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 18:37:06 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 18:37:22 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 18:38:49 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 18:44:11 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 18:45:45 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 18:47:18 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 18:51:41 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 18:52:19 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 18:52:32 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 18:58:22 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 19:00:32 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 19:03:48 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
ERROR - 2017-02-16 20:05:42 --> 404 Page Not Found: %7B%7BdocContent%7D%7D/index
