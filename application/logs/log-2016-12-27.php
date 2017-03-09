<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2016-12-27 09:49:15 --> Severity: Warning --> mysqli::real_connect(): (HY000/2003): Can't connect to MySQL server on '192.168.1.35' (113) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2016-12-27 09:49:15 --> Unable to connect to the database
ERROR - 2016-12-27 09:54:17 --> 404 Page Not Found: Faviconico/index
ERROR - 2016-12-27 10:58:57 --> Severity: Warning --> mysqli::real_connect(): (HY000/2003): Can't connect to MySQL server on '192.168.1.35' (4) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2016-12-27 10:58:57 --> Unable to connect to the database
ERROR - 2016-12-27 11:04:14 --> Query error: Unknown column 'loads.TruckCompanyName.JobStatus' in 'field list' - Invalid query: SELECT `loads`.`id`, `loads`.`truckstopID`, `loads`.`Bond`, `loads`.`PointOfContactPhone`, `loads`.`equipment_options`, `loads`.`LoadType`, `loads`.`PickupDate`, `loads`.`OriginCity`, `loads`.`OriginState`, `loads`.`DestinationCity`, `loads`.`DestinationState`, `loads`.`PaymentAmount`, `loads`.`Mileage`, `loads`.`deadmiles`, `loads`.`Weight`, `loads`.`Length`, `loads`.`TruckCompanyName`, `loads`.`TruckCompanyName`.`JobStatus`, `loads`.`totalCost`, `loads`.`pickDate`, `broker_info`.`TruckCompanyName` as `companyName`
FROM `loads`
LEFT JOIN `broker_info` ON `broker_info`.`id` = `loads`.`broker_id`
WHERE `loads`.`vehicle_id` IN('127')
AND `loads`.`load_source` = 'Vika Dispatch'
AND `delete_status` =0
ERROR - 2016-12-27 12:49:08 --> Query error: Column 'status' in where clause is ambiguous - Invalid query: SELECT `drivers`.`id`, concat(drivers.first_name, " ", drivers.last_name) as driverName, `users`.`username`
FROM `drivers`
JOIN `users` ON `drivers`.`user_id` = `users`.`id`
WHERE `user_id` IS NULL
AND `status` = 1
ERROR - 2016-12-27 13:07:22 --> Severity: Notice --> Undefined index: vehicleIDRepeat /var/www/html/trackingnew/application/controllers/Truckstop.php 1430
ERROR - 2016-12-27 16:13:18 --> Severity: error --> Exception: looks like we got no XML document /var/www/html/trackingnew/application/core/MY_Controller.php 76
