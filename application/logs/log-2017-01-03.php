<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2017-01-03 09:48:53 --> Severity: Warning --> mysqli::real_connect(): (HY000/2003): Can't connect to MySQL server on '192.168.1.35' (113) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2017-01-03 09:48:53 --> Unable to connect to the database
ERROR - 2017-01-03 09:49:16 --> Severity: Warning --> mysqli::real_connect(): (HY000/2003): Can't connect to MySQL server on '192.168.1.35' (113) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2017-01-03 09:49:16 --> Unable to connect to the database
ERROR - 2017-01-03 10:04:28 --> Severity: Warning --> mysqli::real_connect(): (HY000/2003): Can't connect to MySQL server on '192.168.1.35' (113) /var/www/html/trackingnew/system/database/drivers/mysqli/mysqli_driver.php 202
ERROR - 2017-01-03 10:04:28 --> Unable to connect to the database
ERROR - 2017-01-03 10:07:22 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-03 10:30:42 --> Query error: Unknown column 'undefined' in 'where clause' - Invalid query: SELECT `vehicles`.`id`, `fuel_consumption`, `destination_address`
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id` = `vehicles`.`driver_id`
WHERE (`cargo_capacity` >=0) AND (`cargo_bay_l` >= 45) AND (`cargo_bay_w` <= 8.5) AND (`vehicles`.`id` = `undefined`)
ERROR - 2017-01-03 10:33:17 --> Query error: Unknown column 'MCNumber' in 'field list' - Invalid query: UPDATE `loads` SET `id` = '10002', `user_id` = '5', `vehicle_id` = '12', `driver_id` = '53', `truckstopID` = '0', `PickupDate` = '2017-01-02', `pickDate` = '01/02/17', `PickupTime` = '', `PickupAddress` = 'Texas, United States', `OriginCity` = '', `OriginState` = '', `OriginCountry` = '', `OriginZip` = NULL, `DeliveryDate` = '2017-01-04', `DeliveryTime` = '', `DestinationAddress` = 'Miami, FL, United States', `DestinationCity` = '', `DestinationState` = '', `DestinationCountry` = '', `DestinationZip` = NULL, `equipment` = 'Flatbed', `equipment_options` = 'F', `LoadType` = 'full', `Weight` = '45,000.00', `Length` = '45.00', `Width` = '', `Mileage` = '1533', `originalDistance` = '1533', `estimated_time` = '', `Bond` = NULL, `BondTypeID` = NULL, `Credit` = '', `PaymentAmount` = 4522, `Quantity` = '1', `FuelCost` = NULL, `Stops` = '0', `commodity` = 'bees', `broker_id` = '15', `Rate` = '1', `flag` = '0', `sent_for_payment` = '0', `SpecInfo` = 'test', `deadmiles` = '517', `deadmilesEstimatetime` = '', `JobStatus` = 'booked', `assigned_truck_id` = '0', `assigned_truck` = '0', `updated_record` = '0', `invoiceNo` = 10003, `invoicedDate` = '0000-00-00', `load_source` = 'Vika Dispatch', `HandleName` = '', `Entered` = '2017-01-02', `totalCost` = 1485, `overallTotalProfit` = 3037, `overallTotalProfitPercent` = 67.16, `delete_status` = '0', `woRefno` = '455', `loadId` = 0, `created` = '0000-00-00 00:00:00', `updated` = '0000-00-00 00:00:00', `MCNumber` = '392808', `DOTNumber` = '2226913', `TruckCompanyName` = 'WORLDWIDE INTEGRATED SUPPLY CHAIN SOLUTIONS, INC.', `PointOfContact` = 'Alex Stanbrough', `PointOfContactPhone` = '515-273-3124', `TruckCompanyEmail` = 'ASTANBROUGH@WORLDWIDE-LOGISTIC', `TruckCompanyPhone` = '(515) 223-2339', `TruckCompanyFax` = '515-645-9455', `CarrierMC` = '0', `brokerStatus` = 'Approved'
WHERE `loads`.`id` = '10002'
ERROR - 2017-01-03 10:37:28 --> 404 Page Not Found: Faviconico/index
ERROR - 2017-01-03 12:42:36 --> Severity: Notice --> Undefined index: vehicleId /var/www/html/trackingnew/application/controllers/Truckstop.php 1007
ERROR - 2017-01-03 12:44:18 --> Severity: Notice --> Undefined index: vehicleId /var/www/html/trackingnew/application/controllers/Truckstop.php 1007
ERROR - 2017-01-03 12:45:21 --> Severity: Notice --> Undefined index: vehicleId /var/www/html/trackingnew/application/controllers/Truckstop.php 1007
ERROR - 2017-01-03 12:49:06 --> Severity: Notice --> Undefined index: vehicleId /var/www/html/trackingnew/application/controllers/Truckstop.php 1007
ERROR - 2017-01-03 12:50:17 --> Severity: Notice --> Undefined index: vehicleId /var/www/html/trackingnew/application/controllers/Truckstop.php 1007
ERROR - 2017-01-03 13:41:20 --> Query error: Unknown column 'undefined' in 'where clause' - Invalid query: SELECT `vehicles`.`id`, `fuel_consumption`, `destination_address`
FROM `vehicles`
LEFT JOIN `drivers` ON `drivers`.`id` = `vehicles`.`driver_id`
WHERE (`cargo_capacity` >= 43000) AND (`cargo_bay_l` >=0) AND (`cargo_bay_w` <= 8.5) AND (`vehicles`.`id` = `undefined`)
ERROR - 2017-01-03 13:59:30 --> Severity: Notice --> Trying to get property of non-object /var/www/html/trackingnew/application/controllers/Triumph.php 84
ERROR - 2017-01-03 14:18:20 --> Severity: Notice --> Undefined index: vehicleId /var/www/html/trackingnew/application/controllers/Truckstop.php 1007
ERROR - 2017-01-03 17:03:00 --> Severity: Notice --> Undefined variable: originLocaion /var/www/html/trackingnew/application/controllers/Truckstop.php 1011
ERROR - 2017-01-03 17:22:51 --> Severity: Runtime Notice --> Only variables should be passed by reference /var/www/html/trackingnew/application/controllers/Truckstop.php 1870
ERROR - 2017-01-03 17:22:51 --> Severity: Runtime Notice --> Only variables should be passed by reference /var/www/html/trackingnew/application/controllers/Truckstop.php 1871
ERROR - 2017-01-03 17:22:51 --> Severity: Runtime Notice --> Only variables should be passed by reference /var/www/html/trackingnew/application/controllers/Truckstop.php 1872
ERROR - 2017-01-03 17:22:51 --> Severity: Runtime Notice --> Only variables should be passed by reference /var/www/html/trackingnew/application/controllers/Truckstop.php 1879
ERROR - 2017-01-03 17:22:51 --> Severity: Runtime Notice --> Only variables should be passed by reference /var/www/html/trackingnew/application/controllers/Truckstop.php 1880
ERROR - 2017-01-03 17:22:51 --> Severity: Runtime Notice --> Only variables should be passed by reference /var/www/html/trackingnew/application/controllers/Truckstop.php 1881
ERROR - 2017-01-03 17:59:41 --> Severity: Notice --> Undefined index: originAddress_1 /var/www/html/trackingnew/application/controllers/Truckstop.php 1055
ERROR - 2017-01-03 17:59:41 --> Severity: Notice --> Undefined index: originAddress_2 /var/www/html/trackingnew/application/controllers/Truckstop.php 1055
ERROR - 2017-01-03 18:00:17 --> Severity: Notice --> Undefined index: originAddress_1 /var/www/html/trackingnew/application/controllers/Truckstop.php 1055
ERROR - 2017-01-03 18:00:17 --> Severity: Notice --> Undefined index: originAddress_2 /var/www/html/trackingnew/application/controllers/Truckstop.php 1055
ERROR - 2017-01-03 18:01:15 --> Severity: Warning --> Illegal string offset 'originAddress_0' /var/www/html/trackingnew/application/controllers/Truckstop.php 1056
ERROR - 2017-01-03 18:01:15 --> Severity: Warning --> Illegal string offset 'originAddress_0' /var/www/html/trackingnew/application/controllers/Truckstop.php 1057
ERROR - 2017-01-03 18:01:15 --> Severity: Warning --> Illegal string offset 'originAddress_1' /var/www/html/trackingnew/application/controllers/Truckstop.php 1056
ERROR - 2017-01-03 18:01:15 --> Severity: Notice --> Uninitialized string offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 1056
ERROR - 2017-01-03 18:01:15 --> Severity: Warning --> Illegal string offset 'originAddress_2' /var/www/html/trackingnew/application/controllers/Truckstop.php 1056
ERROR - 2017-01-03 18:01:15 --> Severity: Warning --> Illegal string offset 'originAddress_2' /var/www/html/trackingnew/application/controllers/Truckstop.php 1057
ERROR - 2017-01-03 18:02:29 --> Severity: Warning --> Illegal string offset 'originAddress_0' /var/www/html/trackingnew/application/controllers/Truckstop.php 1057
ERROR - 2017-01-03 18:02:29 --> Severity: Warning --> Illegal string offset 'originAddress_0' /var/www/html/trackingnew/application/controllers/Truckstop.php 1058
ERROR - 2017-01-03 18:02:29 --> Severity: Warning --> Illegal string offset 'originAddress_1' /var/www/html/trackingnew/application/controllers/Truckstop.php 1057
ERROR - 2017-01-03 18:02:29 --> Severity: Notice --> Uninitialized string offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 1057
ERROR - 2017-01-03 18:02:29 --> Severity: Warning --> Illegal string offset 'originAddress_2' /var/www/html/trackingnew/application/controllers/Truckstop.php 1057
ERROR - 2017-01-03 18:02:29 --> Severity: Warning --> Illegal string offset 'originAddress_2' /var/www/html/trackingnew/application/controllers/Truckstop.php 1058
ERROR - 2017-01-03 18:03:52 --> Severity: Warning --> Illegal string offset 'originAddress_0' /var/www/html/trackingnew/application/controllers/Truckstop.php 1056
ERROR - 2017-01-03 18:03:52 --> Severity: Warning --> Illegal string offset 'originAddress_0' /var/www/html/trackingnew/application/controllers/Truckstop.php 1057
ERROR - 2017-01-03 18:03:52 --> Severity: Warning --> Illegal string offset 'originAddress_0' /var/www/html/trackingnew/application/controllers/Truckstop.php 1058
ERROR - 2017-01-03 18:03:52 --> Severity: Warning --> Illegal string offset 'originAddress_0' /var/www/html/trackingnew/application/controllers/Truckstop.php 1056
ERROR - 2017-01-03 18:03:52 --> Severity: Notice --> Uninitialized string offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 1056
ERROR - 2017-01-03 18:03:52 --> Severity: Warning --> Illegal string offset 'originAddress_1' /var/www/html/trackingnew/application/controllers/Truckstop.php 1057
ERROR - 2017-01-03 18:03:52 --> Severity: Notice --> Uninitialized string offset: 0 /var/www/html/trackingnew/application/controllers/Truckstop.php 1057
ERROR - 2017-01-03 18:03:52 --> Severity: Warning --> Illegal string offset 'originAddress_0' /var/www/html/trackingnew/application/controllers/Truckstop.php 1056
ERROR - 2017-01-03 18:03:52 --> Severity: Warning --> Illegal string offset 'originAddress_2' /var/www/html/trackingnew/application/controllers/Truckstop.php 1057
ERROR - 2017-01-03 18:03:52 --> Severity: Warning --> Illegal string offset 'originAddress_2' /var/www/html/trackingnew/application/controllers/Truckstop.php 1058
ERROR - 2017-01-03 18:04:47 --> Severity: Notice --> Undefined index: originAddress_1 /var/www/html/trackingnew/application/controllers/Truckstop.php 1055
ERROR - 2017-01-03 18:04:47 --> Severity: Notice --> Undefined index: originAddress_2 /var/www/html/trackingnew/application/controllers/Truckstop.php 1055
ERROR - 2017-01-03 18:05:11 --> Severity: Notice --> Undefined index: originAddress_1 /var/www/html/trackingnew/application/controllers/Truckstop.php 1055
ERROR - 2017-01-03 18:05:11 --> Severity: Notice --> Undefined index: originAddress_1 /var/www/html/trackingnew/application/controllers/Truckstop.php 1056
ERROR - 2017-01-03 18:05:11 --> Severity: Notice --> Undefined index: originAddress_2 /var/www/html/trackingnew/application/controllers/Truckstop.php 1055
ERROR - 2017-01-03 18:05:11 --> Severity: Notice --> Undefined index: originAddress_2 /var/www/html/trackingnew/application/controllers/Truckstop.php 1056
ERROR - 2017-01-03 18:20:54 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/models/Job.php 394
ERROR - 2017-01-03 18:23:17 --> Severity: Notice --> Undefined offset: 1 /var/www/html/trackingnew/application/models/Job.php 399
