<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2016-12-28 09:53:05 --> Severity: Warning --> mysqli::real_connect(): (HY000/2003): Can't connect to MySQL server on '192.168.1.35' (113) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2016-12-28 09:53:05 --> Unable to connect to the database
ERROR - 2016-12-28 10:07:50 --> 404 Page Not Found: Faviconico/index
ERROR - 2016-12-28 10:48:43 --> Severity: Warning --> unlink(assets/uploads/documents/rateSheet/rateSheet_1481796686.pdf): No such file or directory /var/www/html/trackingnew/application/controllers/Truckstop.php 1670
ERROR - 2016-12-28 10:48:43 --> Severity: Warning --> unlink(assets/uploads/documents/thumb_rateSheet/thumb_rateSheet_1481796686.jpg): No such file or directory /var/www/html/trackingnew/application/controllers/Truckstop.php 1673
ERROR - 2016-12-28 11:04:10 --> Severity: Warning --> Illegal string offset 'inputId' /var/www/html/trackingnew/application/controllers/Billings.php 274
ERROR - 2016-12-28 11:04:10 --> Severity: Warning --> Illegal string offset 'filename' /var/www/html/trackingnew/application/controllers/Billings.php 274
ERROR - 2016-12-28 11:04:10 --> Severity: Warning --> Illegal string offset 'fileData' /var/www/html/trackingnew/application/controllers/Billings.php 274
ERROR - 2016-12-28 17:59:39 --> Query error: Column 'status' in where clause is ambiguous - Invalid query: SELECT `drivers`.`id`, concat(drivers.first_name, " ", drivers.last_name) as driverName, `users`.`username`
FROM `drivers`
JOIN `users` ON `drivers`.`user_id` = `users`.`id`
WHERE `user_id` IS NULL
AND `status` = 1
ERROR - 2016-12-28 18:00:01 --> Query error: Column 'status' in where clause is ambiguous - Invalid query: SELECT `drivers`.`id`, concat(drivers.first_name, " ", drivers.last_name) as driverName, `users`.`username`
FROM `drivers`
JOIN `users` ON `drivers`.`user_id` = `users`.`id`
WHERE `user_id` IS NULL
AND `status` = 1
ERROR - 2016-12-28 18:00:58 --> Query error: Column 'status' in where clause is ambiguous - Invalid query: SELECT `drivers`.`id`, concat(drivers.first_name, " ", drivers.last_name) as driverName, `users`.`username`
FROM `drivers`
JOIN `users` ON `drivers`.`user_id` = `users`.`id`
WHERE `user_id` IS NULL
AND `status` = 1
ERROR - 2016-12-28 18:29:39 --> 404 Page Not Found: Faviconico/index
ERROR - 2016-12-28 20:21:20 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/models/BrokersModel.php 125
