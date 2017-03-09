<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-01-17 09:50:13 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-17 09:58:31 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-17 11:27:59 --> Severity: Warning --> file_get_contents(http://maps.googleapis.com/maps/api/geocode/json?address=New Stine Road, Bakersfield, CA, United States&amp;sensor=true_or_false&amp;key=AIzaSyDiojAPmOWusjvasUhm5wKswtCRtkEKyi8): failed to open stream: HTTP request failed! HTTP/1.0 400 Bad Request
 /var/www/html/trackingnew/application/controllers/Billings.php 682
ERROR - 2017-01-17 11:28:15 --> Severity: Warning --> file_get_contents(https://maps.googleapis.com/maps/api/geocode/json?address=New Stine Road, Bakersfield, CA, United States&amp;sensor=true_or_false&amp;key=AIzaSyDiojAPmOWusjvasUhm5wKswtCRtkEKyi8): failed to open stream: HTTP request failed! HTTP/1.0 400 Bad Request
 /var/www/html/trackingnew/application/controllers/Billings.php 682
ERROR - 2017-01-17 11:28:59 --> Severity: Notice --> Undefined variable: address /var/www/html/trackingnew/application/controllers/Billings.php 682
ERROR - 2017-01-17 11:29:00 --> Severity: Warning --> file_get_contents(http://maps.googleapis.com/maps/api/geocode/json?address=&amp;sensor=true_or_false&amp;key=AIzaSyDiojAPmOWusjvasUhm5wKswtCRtkEKyi8): failed to open stream: HTTP request failed! HTTP/1.0 400 Bad Request
 /var/www/html/trackingnew/application/controllers/Billings.php 683
ERROR - 2017-01-17 12:30:45 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '(`JobStaus` != 'cancelled' )
ORDER BY `id` DESC' at line 3 - Invalid query: SELECT `PickupDate`, `DeliveryDate`, `id`
FROM `loads`
WHERE ((`PickupDate` = '2017-01-17') OR (`PickupDate` < '2017-01-17' AND `DeliveryDate` > '2017-01-17')) AND (`vehicle_id` = 124) AND (`id` != 10046) (`JobStaus` != 'cancelled' )
ORDER BY `id` DESC
ERROR - 2017-01-17 12:34:26 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '(`JobStatus` != 'cancel' )
ORDER BY `id` DESC' at line 3 - Invalid query: SELECT `PickupDate`, `DeliveryDate`, `id`
FROM `loads`
WHERE ((`PickupDate` = '2017-01-17') OR (`PickupDate` < '2017-01-17' AND `DeliveryDate` > '2017-01-17')) AND (`vehicle_id` = 124) AND (`id` != 10046) (`JobStatus` != 'cancel' )
ORDER BY `id` DESC
ERROR - 2017-01-17 12:36:12 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '(`JobStatus` != 'cancel' )
ORDER BY `id` DESC' at line 3 - Invalid query: SELECT `PickupDate`, `DeliveryDate`, `id`
FROM `loads`
WHERE ((`PickupDate` = '2017-01-17') OR (`PickupDate` < '2017-01-17' AND `DeliveryDate` > '2017-01-17')) AND (`vehicle_id` = 124) AND (`id` != 10046) (`JobStatus` != 'cancel' )
ORDER BY `id` DESC
ERROR - 2017-01-17 16:10:17 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 1169
ERROR - 2017-01-17 17:04:19 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 2253
ERROR - 2017-01-17 17:04:19 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Truckstop.php 2253
ERROR - 2017-01-17 17:04:19 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Truckstop.php 2253
ERROR - 2017-01-17 17:04:19 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Truckstop.php 2253
ERROR - 2017-01-17 17:04:19 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 2254
ERROR - 2017-01-17 17:04:19 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Truckstop.php 2254
ERROR - 2017-01-17 17:04:19 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Truckstop.php 2254
ERROR - 2017-01-17 17:04:19 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Truckstop.php 2254
ERROR - 2017-01-17 17:04:20 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 2261
ERROR - 2017-01-17 17:04:20 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Truckstop.php 2261
ERROR - 2017-01-17 17:04:20 --> Severity: Warning --> Invalid argument supplied for foreach() /var/www/html/trackingnew/application/controllers/Truckstop.php 2262
ERROR - 2017-01-17 17:46:20 --> Severity: Notice --> Undefined property: Billings::$User /var/www/html/trackingnew/application/controllers/Billings.php 710
ERROR - 2017-01-17 17:46:20 --> Severity: Error --> Call to a member function saveZipCode() on a non-object /var/www/html/trackingnew/application/controllers/Billings.php 710
ERROR - 2017-01-17 17:46:52 --> Severity: Error --> Call to undefined method User::saveZipCode() /var/www/html/trackingnew/application/controllers/Billings.php 710
ERROR - 2017-01-17 17:59:26 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Billings.php 726
ERROR - 2017-01-17 17:59:26 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 726
ERROR - 2017-01-17 17:59:26 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 726
ERROR - 2017-01-17 17:59:26 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 726
ERROR - 2017-01-17 17:59:26 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Billings.php 727
ERROR - 2017-01-17 17:59:26 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 727
ERROR - 2017-01-17 17:59:26 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 727
ERROR - 2017-01-17 17:59:26 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 727
ERROR - 2017-01-17 17:59:26 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Billings.php 736
ERROR - 2017-01-17 17:59:26 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 736
ERROR - 2017-01-17 17:59:26 --> Severity: Warning --> Invalid argument supplied for foreach() /var/www/html/trackingnew/application/controllers/Billings.php 738
ERROR - 2017-01-17 18:15:40 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Billings.php 691
ERROR - 2017-01-17 18:15:40 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 691
ERROR - 2017-01-17 18:15:40 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 691
ERROR - 2017-01-17 18:15:40 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 691
ERROR - 2017-01-17 18:15:40 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Billings.php 692
ERROR - 2017-01-17 18:15:40 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 692
ERROR - 2017-01-17 18:15:40 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 692
ERROR - 2017-01-17 18:15:40 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 692
ERROR - 2017-01-17 18:15:40 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Billings.php 701
ERROR - 2017-01-17 18:15:40 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 701
ERROR - 2017-01-17 18:15:40 --> Severity: Warning --> Invalid argument supplied for foreach() /var/www/html/trackingnew/application/controllers/Billings.php 703
ERROR - 2017-01-17 18:17:27 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Billings.php 726
ERROR - 2017-01-17 18:17:27 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 726
ERROR - 2017-01-17 18:17:27 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 726
ERROR - 2017-01-17 18:17:27 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 726
ERROR - 2017-01-17 18:17:27 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Billings.php 727
ERROR - 2017-01-17 18:17:27 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 727
ERROR - 2017-01-17 18:17:27 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 727
ERROR - 2017-01-17 18:17:27 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 727
ERROR - 2017-01-17 18:17:27 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Billings.php 736
ERROR - 2017-01-17 18:17:27 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Billings.php 736
ERROR - 2017-01-17 18:17:27 --> Severity: Warning --> Invalid argument supplied for foreach() /var/www/html/trackingnew/application/controllers/Billings.php 738
