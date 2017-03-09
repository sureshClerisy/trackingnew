<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-01-16 09:53:34 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-16 10:03:11 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-16 10:20:53 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-16 10:21:10 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-16 11:02:59 --> Query error: Unknown column 'setDeadMilePage' in 'field list' - Invalid query: UPDATE `loads` SET `id` = '10040', `user_id` = '5', `vehicle_id` = '35', `driver_id` = '98', `trailer_id` = '26', `truckstopID` = '0', `PickupDate` = '2017-01-19', `pickDate` = '01/19/17', `PickupTime` = '', `PickupTimeRangeEnd` = '', `PickupAddress` = 'Alaskan Way, Seattle, WA, United States', `OriginStreet` = 'Alaskan Way', `OriginCity` = 'Seattle', `OriginState` = 'WA', `OriginCountry` = 'United States', `OriginZip` = NULL, `DeliveryDate` = '', `DeliveryTime` = '', `DeliveryTimeRangeEnd` = '', `DestinationAddress` = 'Washington, DC, United States', `DestinationStreet` = '', `DestinationCity` = 'Washington', `DestinationState` = 'DC', `DestinationCountry` = 'United States', `DestinationZip` = NULL, `equipment` = 'Flatbed', `equipment_options` = 'F', `LoadType` = 'Full', `Weight` = '45,000.00', `Length` = '45.00', `Width` = '', `Mileage` = '2769', `originalDistance` = '2769', `estimated_time` = '', `Bond` = NULL, `BondTypeID` = NULL, `Credit` = '', `PaymentAmount` = 3500, `Quantity` = '1', `FuelCost` = NULL, `Stops` = '0', `commodity` = '', `broker_id` = '16', `Rate` = NULL, `flag` = '0', `sent_for_payment` = '0', `SpecInfo` = 'No special info available', `deadmiles` = '841', `deadmilesEstimatetime` = '', `JobStatus` = '', `updated_record` = '0', `invoiceNo` = NULL, `invoicedDate` = '2017-01-13', `load_source` = 'Vika Dispatch', `HandleName` = '', `Entered` = '2017-01-11', `totalCost` = 2627, `overallTotalProfit` = 873, `overallTotalProfitPercent` = 24.94, `delete_status` = '0', `woRefno` = NULL, `loadId` = 0, `created` = '2017-01-11 17:25:46', `updated` = '0000-00-00 00:00:00', `setDeadMilePage` = 0
WHERE `loads`.`id` = '10040'
ERROR - 2017-01-16 11:10:46 --> Severity: error --> Exception: Could not connect to host /var/www/html/trackingnew/application/core/MY_Controller.php 77
ERROR - 2017-01-16 11:11:16 --> Severity: error --> Exception: Could not connect to host /var/www/html/trackingnew/application/core/MY_Controller.php 77
ERROR - 2017-01-16 11:12:46 --> Severity: error --> Exception: Could not connect to host /var/www/html/trackingnew/application/core/MY_Controller.php 77
ERROR - 2017-01-16 11:13:16 --> Severity: error --> Exception: Could not connect to host /var/www/html/trackingnew/application/core/MY_Controller.php 77
ERROR - 2017-01-16 11:13:36 --> Severity: error --> Exception: Could not connect to host /var/www/html/trackingnew/application/core/MY_Controller.php 77
ERROR - 2017-01-16 11:13:56 --> Severity: error --> Exception: Could not connect to host /var/www/html/trackingnew/application/core/MY_Controller.php 77
ERROR - 2017-01-16 11:14:16 --> Severity: error --> Exception: Could not connect to host /var/www/html/trackingnew/application/core/MY_Controller.php 77
ERROR - 2017-01-16 11:14:52 --> Severity: error --> Exception: Could not connect to host /var/www/html/trackingnew/application/core/MY_Controller.php 77
ERROR - 2017-01-16 11:17:46 --> Severity: error --> Exception: Could not connect to host /var/www/html/trackingnew/application/core/MY_Controller.php 77
ERROR - 2017-01-16 11:18:16 --> Severity: error --> Exception: Could not connect to host /var/www/html/trackingnew/application/core/MY_Controller.php 77
ERROR - 2017-01-16 12:09:55 --> Severity: Notice --> Undefined index: deadMileCalculate /var/www/html/trackingnew/application/controllers/Truckstop.php 922
ERROR - 2017-01-16 13:31:58 --> Severity: Error --> Call to undefined function strotime() /var/www/html/trackingnew/application/controllers/Truckstop.php 968
ERROR - 2017-01-16 14:24:08 --> Severity: Notice --> Undefined index: setDeadMilePage /var/www/html/trackingnew/application/controllers/Truckstop.php 920
ERROR - 2017-01-16 14:42:26 --> Severity: Notice --> Undefined variable: caluclatePlanDeadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 921
ERROR - 2017-01-16 14:43:06 --> Severity: Notice --> Undefined variable: caluclatePlanDeadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 921
ERROR - 2017-01-16 15:37:54 --> Severity: Warning --> array_push() expects parameter 1 to be array, null given /var/www/html/trackingnew/application/controllers/Truckstop.php 1109
ERROR - 2017-01-16 15:54:03 --> Severity: Parsing Error --> syntax error, unexpected '$originLocation' (T_VARIABLE), expecting ',' or ';' /var/www/html/trackingnew/application/controllers/Truckstop.php 947
ERROR - 2017-01-16 15:54:20 --> Severity: Parsing Error --> syntax error, unexpected '$originLocation' (T_VARIABLE), expecting ',' or ';' /var/www/html/trackingnew/application/controllers/Truckstop.php 947
ERROR - 2017-01-16 17:11:58 --> Query error: Table 'tracking_new.vehicles' doesn't exist - Invalid query: SELECT `state`
FROM `vehicles`
GROUP BY `state`
ERROR - 2017-01-16 17:58:15 --> Severity: Notice --> Array to string conversion /var/www/html/trackingnew/application/models/Job.php 28
ERROR - 2017-01-16 18:22:32 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 552
ERROR - 2017-01-16 18:22:36 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 552
ERROR - 2017-01-16 18:22:40 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 552
ERROR - 2017-01-16 18:22:44 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 552
ERROR - 2017-01-16 18:48:05 --> Severity: Notice --> Undefined index: vehicle_id /var/www/html/trackingnew/application/controllers/Truckstop.php 1813
ERROR - 2017-01-16 18:48:05 --> Severity: Notice --> Undefined index: PaymentAmount /var/www/html/trackingnew/application/controllers/Truckstop.php 1826
ERROR - 2017-01-16 18:48:05 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1900
ERROR - 2017-01-16 18:48:05 --> Severity: Notice --> Undefined index: Mileage /var/www/html/trackingnew/application/controllers/Truckstop.php 1900
ERROR - 2017-01-16 18:48:05 --> Severity: Notice --> Undefined index: PickupDate /var/www/html/trackingnew/application/controllers/Truckstop.php 1911
ERROR - 2017-01-16 18:48:05 --> Severity: Notice --> Undefined index: equipment_options /var/www/html/trackingnew/application/models/Job.php 635
ERROR - 2017-01-16 18:48:05 --> Query error: Column 'deadmiles' cannot be null - Invalid query: INSERT INTO `loads` (`PickupAddress`, `DestinationAddress`, `totalCost`, `overallTotalProfit`, `overallTotalProfitPercent`, `PaymentAmount`, `deadmiles`, `pickDate`, `load_source`, `equipment`, `loadId`, `user_id`, `Entered`, `created`) VALUES ('', '', 50, '', '', 0, NULL, '01/01/70', 'Vika Dispatch', 0, 0, '5', '2017-01-16', '17-01-16 18:48:05')
ERROR - 2017-01-16 18:56:14 --> Severity: Notice --> Undefined index: setDeadMilePage /var/www/html/trackingnew/application/controllers/Truckstop.php 2189
ERROR - 2017-01-16 18:56:33 --> Severity: Notice --> Undefined index: setDeadMilePage /var/www/html/trackingnew/application/controllers/Truckstop.php 2189
ERROR - 2017-01-16 18:57:23 --> Severity: Notice --> Undefined index: setDeadMilePage /var/www/html/trackingnew/application/controllers/Truckstop.php 921
ERROR - 2017-01-16 19:15:11 --> Severity: Notice --> Undefined index: vehicle_id /var/www/html/trackingnew/application/controllers/Truckstop.php 1813
ERROR - 2017-01-16 19:15:11 --> Severity: Notice --> Undefined index: PaymentAmount /var/www/html/trackingnew/application/controllers/Truckstop.php 1826
ERROR - 2017-01-16 19:15:11 --> Severity: Notice --> Undefined index: deadmiles /var/www/html/trackingnew/application/controllers/Truckstop.php 1900
ERROR - 2017-01-16 19:15:11 --> Severity: Notice --> Undefined index: Mileage /var/www/html/trackingnew/application/controllers/Truckstop.php 1900
ERROR - 2017-01-16 19:15:11 --> Severity: Notice --> Undefined index: PickupDate /var/www/html/trackingnew/application/controllers/Truckstop.php 1911
ERROR - 2017-01-16 19:15:11 --> Severity: Notice --> Undefined index: equipment_options /var/www/html/trackingnew/application/models/Job.php 635
ERROR - 2017-01-16 19:15:11 --> Query error: Column 'deadmiles' cannot be null - Invalid query: INSERT INTO `loads` (`PickupAddress`, `DestinationAddress`, `totalCost`, `overallTotalProfit`, `overallTotalProfitPercent`, `PaymentAmount`, `deadmiles`, `pickDate`, `load_source`, `equipment`, `loadId`, `user_id`, `Entered`, `created`) VALUES ('', '', 50, '', '', 0, NULL, '01/01/70', 'Vika Dispatch', 0, 0, '5', '2017-01-16', '17-01-16 19:15:11')
ERROR - 2017-01-16 19:17:59 --> Severity: Warning --> Division by zero /var/www/html/trackingnew/application/controllers/Truckstop.php 1169
ERROR - 2017-01-16 20:09:38 --> Severity: Notice --> Undefined index: PickupDate /var/www/html/trackingnew/application/controllers/Truckstop.php 1911
ERROR - 2017-01-16 20:10:26 --> Severity: Notice --> Undefined index: PickupDate /var/www/html/trackingnew/application/controllers/Truckstop.php 1911
ERROR - 2017-01-16 20:12:33 --> Severity: Notice --> Undefined index: PickupDate /var/www/html/trackingnew/application/controllers/Truckstop.php 1911
