<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-02-08 10:00:37 --> Severity: Warning --> mysqli::real_connect(): (HY000/2003): Can't connect to MySQL server on '192.168.1.135' (113) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2017-02-08 10:00:37 --> Unable to connect to the database
ERROR - 2017-02-08 10:04:51 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: driver_dead_miles_paid /var/www/html/trackingnew/application/controllers/Truckstop.php 849
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: driver_dead_miles_not_paid /var/www/html/trackingnew/application/controllers/Truckstop.php 850
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: driver_pay_for_dead_mile /var/www/html/trackingnew/application/controllers/Truckstop.php 851
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: driver_pay_miles_cargo /var/www/html/trackingnew/application/controllers/Truckstop.php 856
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: tax_ifta_tax /var/www/html/trackingnew/application/controllers/Truckstop.php 860
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: tax_det_time /var/www/html/trackingnew/application/controllers/Truckstop.php 862
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: tax_tolls /var/www/html/trackingnew/application/controllers/Truckstop.php 863
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: driver_dead_mile /var/www/html/trackingnew/application/controllers/Truckstop.php 880
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: fuel_consumption /var/www/html/trackingnew/application/models/Job.php 771
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: gallon_needed /var/www/html/trackingnew/application/models/Job.php 772
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: diesel_rate_per_gallon /var/www/html/trackingnew/application/models/Job.php 773
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: originToDestDistDriver /var/www/html/trackingnew/application/models/Job.php 774
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: driver_dead_miles_not_paid /var/www/html/trackingnew/application/models/Job.php 776
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: driver_dead_miles_paid /var/www/html/trackingnew/application/models/Job.php 777
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: driver_pay_for_dead_mile /var/www/html/trackingnew/application/models/Job.php 778
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: driver_pay_miles_cargo /var/www/html/trackingnew/application/models/Job.php 779
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: tax_ifta_tax /var/www/html/trackingnew/application/models/Job.php 780
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: tax_det_time /var/www/html/trackingnew/application/models/Job.php 782
ERROR - 2017-02-08 11:39:18 --> Severity: Notice --> Undefined index: tax_tolls /var/www/html/trackingnew/application/models/Job.php 783
ERROR - 2017-02-08 11:39:48 --> Query error: Unknown column 'vehicles.id' in 'where clause' - Invalid query: SELECT `vehicles`.`id`, `fuel_consumption`, `destination_address`
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id` = `vehicles`.`driver_id`
WHERE (`vehicles.id` = 124)
ERROR - 2017-02-08 11:40:46 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '(`vehicles.id` = 124)' at line 4 - Invalid query: SELECT `vehicles`.`id`, `fuel_consumption`, `destination_address`
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id` = `vehicles`.`driver_id`
WHERE (`cargo_bay_l` >= 60) AND (`cargo_bay_w` <= 8.5) (`vehicles.id` = 124)
ERROR - 2017-02-08 11:41:24 --> Query error: Unknown column 'vehicles.id' in 'where clause' - Invalid query: SELECT `vehicles`.`id`, `fuel_consumption`, `destination_address`
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id` = `vehicles`.`driver_id`
WHERE (`vehicles.id` = 124)
ERROR - 2017-02-08 11:41:33 --> Query error: Unknown column 'vehicles.id' in 'where clause' - Invalid query: SELECT `vehicles`.`id`, `fuel_consumption`, `destination_address`
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id` = `vehicles`.`driver_id`
WHERE (`vehicles.id` = 124)
ERROR - 2017-02-08 11:45:29 --> Query error: Unknown column 'vehicles.id' in 'where clause' - Invalid query: SELECT `vehicles`.`id`, `vehicles`.`fuel_consumption`, `vehicles`.`destination_address`
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id` = `vehicles`.`driver_id`
WHERE (`vehicles.id` = 124)
ERROR - 2017-02-08 13:29:15 --> Severity: Error --> Call to undefined method Billing::checkRequiredFeildsForInvoice() /var/www/html/trackingnew/application/models/Billing.php 338
ERROR - 2017-02-08 13:29:55 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/models/Job.php 1672
ERROR - 2017-02-08 13:30:23 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/models/Job.php 1673
ERROR - 2017-02-08 13:34:48 --> Severity: Notice --> Undefined index: PaymentAmount /var/www/html/trackingnew/application/controllers/Truckstop.php 2106
ERROR - 2017-02-08 13:34:48 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 2198
ERROR - 2017-02-08 13:34:48 --> Severity: Notice --> Undefined index: Mileage /var/www/html/trackingnew/application/controllers/Truckstop.php 2198
ERROR - 2017-02-08 13:34:48 --> Severity: Notice --> Undefined index: PickupDate /var/www/html/trackingnew/application/controllers/Truckstop.php 2209
ERROR - 2017-02-08 13:34:48 --> Severity: Notice --> Undefined index: equipment_options /var/www/html/trackingnew/application/models/Job.php 613
ERROR - 2017-02-08 13:34:48 --> Query error: Column 'deadmiles' cannot be null - Invalid query: INSERT INTO `loads` (`PickupAddress`, `DestinationAddress`, `shipper_entity`, `consignee_entity`, `totalCost`, `overallTotalProfit`, `overallTotalProfitPercent`, `PaymentAmount`, `deadmiles`, `pickDate`, `driver_type`, `load_source`, `equipment`, `loadId`, `user_id`, `Entered`, `created`) VALUES ('', '', 'shipper', 'consignee', 50, '', '', 0, NULL, '01/01/70', 'driver', 'Vika Dispatch', 0, 0, '6', '2017-02-08', '17-02-08 13:34:48')
ERROR - 2017-02-08 13:35:59 --> Severity: Notice --> Undefined index: PaymentAmount /var/www/html/trackingnew/application/controllers/Truckstop.php 2120
ERROR - 2017-02-08 13:35:59 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 2199
ERROR - 2017-02-08 13:35:59 --> Severity: Notice --> Undefined index: Mileage /var/www/html/trackingnew/application/controllers/Truckstop.php 2199
ERROR - 2017-02-08 13:35:59 --> Severity: Notice --> Undefined index: PaymentAmount /var/www/html/trackingnew/application/controllers/Truckstop.php 2199
ERROR - 2017-02-08 13:35:59 --> Severity: Notice --> Undefined index: PickupDate /var/www/html/trackingnew/application/controllers/Truckstop.php 2210
ERROR - 2017-02-08 13:35:59 --> Severity: Notice --> Undefined index: equipment_options /var/www/html/trackingnew/application/models/Job.php 613
ERROR - 2017-02-08 13:35:59 --> Query error: Column 'deadmiles' cannot be null - Invalid query: INSERT INTO `loads` (`PickupAddress`, `DestinationAddress`, `shipper_entity`, `consignee_entity`, `totalCost`, `overallTotalProfit`, `overallTotalProfitPercent`, `deadmiles`, `pickDate`, `driver_type`, `load_source`, `equipment`, `loadId`, `user_id`, `Entered`, `created`) VALUES ('', '', 'shipper', 'consignee', 50, '', '', NULL, '01/01/70', 'driver', 'Vika Dispatch', 0, 0, '6', '2017-02-08', '17-02-08 13:35:59')
ERROR - 2017-02-08 13:44:25 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 2200
ERROR - 2017-02-08 13:44:25 --> Severity: Notice --> Undefined index: Mileage /var/www/html/trackingnew/application/controllers/Truckstop.php 2200
ERROR - 2017-02-08 13:44:25 --> Severity: Notice --> Undefined index: PaymentAmount /var/www/html/trackingnew/application/controllers/Truckstop.php 2200
ERROR - 2017-02-08 13:44:25 --> Severity: Notice --> Undefined index: PickupDate /var/www/html/trackingnew/application/controllers/Truckstop.php 2211
ERROR - 2017-02-08 13:44:25 --> Severity: Notice --> Undefined index: equipment_options /var/www/html/trackingnew/application/models/Job.php 613
ERROR - 2017-02-08 13:44:25 --> Query error: Column 'deadmiles' cannot be null - Invalid query: INSERT INTO `loads` (`PickupAddress`, `DestinationAddress`, `shipper_entity`, `consignee_entity`, `totalCost`, `overallTotalProfit`, `overallTotalProfitPercent`, `deadmiles`, `pickDate`, `driver_type`, `load_source`, `equipment`, `loadId`, `user_id`, `Entered`, `created`) VALUES ('', '', 'shipper', 'consignee', 50, '', '', NULL, '01/01/70', 'driver', 'Vika Dispatch', 0, 0, '5', '2017-02-08', '17-02-08 13:44:25')
ERROR - 2017-02-08 13:45:12 --> Severity: Notice --> Undefined index: Mileage /var/www/html/trackingnew/application/controllers/Truckstop.php 2201
ERROR - 2017-02-08 13:45:12 --> Severity: Notice --> Undefined index: PaymentAmount /var/www/html/trackingnew/application/controllers/Truckstop.php 2201
ERROR - 2017-02-08 13:45:12 --> Severity: Notice --> Undefined index: PickupDate /var/www/html/trackingnew/application/controllers/Truckstop.php 2212
ERROR - 2017-02-08 13:45:12 --> Severity: Notice --> Undefined index: equipment_options /var/www/html/trackingnew/application/models/Job.php 613
ERROR - 2017-02-08 13:45:12 --> Severity: Notice --> Undefined index: driverName /var/www/html/trackingnew/application/controllers/Truckstop.php 2274
ERROR - 2017-02-08 13:45:12 --> Severity: Notice --> Undefined index: label /var/www/html/trackingnew/application/controllers/Truckstop.php 2274
ERROR - 2017-02-08 13:45:12 --> Severity: Notice --> Undefined index: label /var/www/html/trackingnew/application/controllers/Truckstop.php 2275
ERROR - 2017-02-08 13:45:12 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/controllers/Truckstop.php 2275
ERROR - 2017-02-08 13:45:12 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 2275
ERROR - 2017-02-08 13:45:39 --> Severity: Notice --> Undefined index: PaymentAmount /var/www/html/trackingnew/application/controllers/Truckstop.php 2202
ERROR - 2017-02-08 13:45:39 --> Severity: Notice --> Undefined index: PickupDate /var/www/html/trackingnew/application/controllers/Truckstop.php 2213
ERROR - 2017-02-08 13:45:39 --> Severity: Notice --> Undefined index: equipment_options /var/www/html/trackingnew/application/models/Job.php 613
ERROR - 2017-02-08 13:45:39 --> Severity: Notice --> Undefined index: driverName /var/www/html/trackingnew/application/controllers/Truckstop.php 2275
ERROR - 2017-02-08 13:45:39 --> Severity: Notice --> Undefined index: label /var/www/html/trackingnew/application/controllers/Truckstop.php 2275
ERROR - 2017-02-08 13:45:39 --> Severity: Notice --> Undefined index: label /var/www/html/trackingnew/application/controllers/Truckstop.php 2276
ERROR - 2017-02-08 13:45:39 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/controllers/Truckstop.php 2276
ERROR - 2017-02-08 13:45:39 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 2276
ERROR - 2017-02-08 13:47:57 --> Severity: Notice --> Undefined index: PickupDate /var/www/html/trackingnew/application/controllers/Truckstop.php 2215
ERROR - 2017-02-08 13:47:57 --> Severity: Notice --> Undefined index: equipment_options /var/www/html/trackingnew/application/models/Job.php 613
ERROR - 2017-02-08 13:47:57 --> Severity: Notice --> Undefined index: driverName /var/www/html/trackingnew/application/controllers/Truckstop.php 2277
ERROR - 2017-02-08 13:47:57 --> Severity: Notice --> Undefined index: label /var/www/html/trackingnew/application/controllers/Truckstop.php 2277
ERROR - 2017-02-08 13:47:57 --> Severity: Notice --> Undefined index: label /var/www/html/trackingnew/application/controllers/Truckstop.php 2278
ERROR - 2017-02-08 13:47:57 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/controllers/Truckstop.php 2278
ERROR - 2017-02-08 13:47:57 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 2278
ERROR - 2017-02-08 13:49:00 --> Severity: Notice --> Undefined index: equipment_options /var/www/html/trackingnew/application/models/Job.php 613
ERROR - 2017-02-08 13:49:01 --> Severity: Notice --> Undefined index: driverName /var/www/html/trackingnew/application/controllers/Truckstop.php 2278
ERROR - 2017-02-08 13:49:01 --> Severity: Notice --> Undefined index: label /var/www/html/trackingnew/application/controllers/Truckstop.php 2278
ERROR - 2017-02-08 13:49:01 --> Severity: Notice --> Undefined index: label /var/www/html/trackingnew/application/controllers/Truckstop.php 2279
ERROR - 2017-02-08 13:49:01 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/controllers/Truckstop.php 2279
ERROR - 2017-02-08 13:49:01 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 2279
ERROR - 2017-02-08 13:49:45 --> Severity: Notice --> Undefined index: driverName /var/www/html/trackingnew/application/controllers/Truckstop.php 2279
ERROR - 2017-02-08 13:49:45 --> Severity: Notice --> Undefined index: label /var/www/html/trackingnew/application/controllers/Truckstop.php 2279
ERROR - 2017-02-08 13:49:45 --> Severity: Notice --> Undefined index: label /var/www/html/trackingnew/application/controllers/Truckstop.php 2280
ERROR - 2017-02-08 13:49:45 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/controllers/Truckstop.php 2280
ERROR - 2017-02-08 13:49:45 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 2280
ERROR - 2017-02-08 13:50:39 --> Severity: Notice --> Undefined index: label /var/www/html/trackingnew/application/controllers/Truckstop.php 2280
ERROR - 2017-02-08 13:50:39 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/controllers/Truckstop.php 2280
ERROR - 2017-02-08 13:50:39 --> Severity: Notice --> Undefined offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 2280
ERROR - 2017-02-08 16:12:41 --> Severity: error --> Exception: This document (/home/csolution/Downloads/pod_1485959777.pdf) probably uses a compression technique which is not supported by the free parser shipped with FPDI. (See https://www.setasign.com/fpdi-pdf-parser for more details) /var/www/html/trackingnew/application/third_party/fpdi/pdf_parser.php 322
ERROR - 2017-02-08 16:13:00 --> Severity: Notice --> Undefined variable: data /var/www/html/trackingnew/application/controllers/Assignedloads.php 543
ERROR - 2017-02-08 16:13:00 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Assignedloads.php 543
ERROR - 2017-02-08 16:13:00 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Assignedloads.php 543
ERROR - 2017-02-08 16:13:00 --> Severity: Notice --> Undefined variable: data /var/www/html/trackingnew/application/controllers/Assignedloads.php 546
ERROR - 2017-02-08 16:13:00 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Assignedloads.php 546
ERROR - 2017-02-08 16:13:00 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Assignedloads.php 546
ERROR - 2017-02-08 16:13:16 --> Severity: error --> Exception: This document (/home/csolution/Downloads/podNew.pdf) probably uses a compression technique which is not supported by the free parser shipped with FPDI. (See https://www.setasign.com/fpdi-pdf-parser for more details) /var/www/html/trackingnew/application/third_party/fpdi/pdf_parser.php 322
ERROR - 2017-02-08 16:57:24 --> Severity: Notice --> Undefined variable: response /var/www/html/trackingnew/application/controllers/Truckstop.php 1919
ERROR - 2017-02-08 17:16:19 --> Severity: Notice --> Undefined index: file_name /var/www/html/trackingnew/application/controllers/Truckstop.php 1922
ERROR - 2017-02-08 12:51:29 --> Severity: Warning --> Illegal string offset 'doc_name' /var/www/html/trackingnew/application/controllers/Truckstop.php 1942
ERROR - 2017-02-08 12:51:29 --> Severity: Warning --> Illegal string offset 'doc_name' /var/www/html/trackingnew/application/controllers/Truckstop.php 1943
ERROR - 2017-02-08 12:51:29 --> Severity: Warning --> unlink(assets/uploads/documents/pod/c): No such file or directory /var/www/html/trackingnew/application/controllers/Truckstop.php 1943
ERROR - 2017-02-08 12:51:29 --> Severity: Warning --> Illegal string offset 'doc_name' /var/www/html/trackingnew/application/controllers/Truckstop.php 1944
ERROR - 2017-02-08 12:51:29 --> Severity: Warning --> unlink(assets/uploads/documents/thumb_pod/thumb_c.jpg): No such file or directory /var/www/html/trackingnew/application/controllers/Truckstop.php 1946
ERROR - 2017-02-08 12:51:29 --> Severity: Warning --> Illegal string offset 'id' /var/www/html/trackingnew/application/controllers/Truckstop.php 1948
ERROR - 2017-02-08 12:52:55 --> Severity: Warning --> unlink(assets/uploads/documents/pod/pod_1486550527.pdf): No such file or directory /var/www/html/trackingnew/application/controllers/Truckstop.php 1943
ERROR - 2017-02-08 12:52:55 --> Severity: Warning --> unlink(assets/uploads/documents/thumb_pod/thumb_pod_1486550527.jpg): No such file or directory /var/www/html/trackingnew/application/controllers/Truckstop.php 1946
ERROR - 2017-02-08 19:22:39 --> Severity: Notice --> Undefined index: calPayments /var/www/html/trackingnew/application/controllers/Truckstop.php 607
ERROR - 2017-02-08 19:22:39 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 610
ERROR - 2017-02-08 19:23:23 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 610
ERROR - 2017-02-08 19:23:29 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 610
ERROR - 2017-02-08 19:24:17 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 610
