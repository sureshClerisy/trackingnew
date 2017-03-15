<html>
<head>

</head>
<body>

	<table cellpadding="0" cellspacing="0" style="width:1060px; margin:0px auto;padding:0px;">
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;border-bottom:1px solid #b6b6b6;padding:44px 0px 34px;font-family:arial;">
					<tr>
						<td style="width:118px;">
							<img src="/assets/img/print_logo.png" >
						</td>
						<td style="text-align:center;width:758px;">
							<h1 style="font-size:30px; color:#1f1f1f;padding:0px; margin:0px;">LOAD DETAILS</h1>
						</td>
						<td style="width:300px; ">
							<div style="width:300px; float:right;">
								<div style="float: left;width: 78px;font-size: 13px;">
									<b style="width: 100%;text-transform: uppercase;float: left;font-size: 13px;color: #868686;font-weight: bold;margin-bottom: 10px;">Load ID</b><?php echo (isset($jobDetails['id']) && $jobDetails['id'] != '' ) ? $jobDetails['id'] : 'NA';?>
									</div>
								<div style="float: left;width: 102px;font-size: 13px;">
									<b style="width: 100%;text-transform: uppercase;float: left;font-size: 13px;color: #868686;font-weight: bold;margin-bottom: 10px;">Invoice No</b><?php echo (isset($jobDetails['invoiceNo']) && $jobDetails['invoiceNo']) ? $jobDetails['invoiceNo'] : 'NA'; ?>
								</div>
								<a style="width: 111px;float: right;border: 1px solid #6c6c6c;text-align: center;border-radius: 3px;text-decoration: none;font-size: 14px;color: #6c6c6c;padding: 11px 0px;">
									<?php echo ($jobDetails['JobStatus'] != '') ? $jobDetails['JobStatus'] : 'No Status'; ?>
								</a>
							</div>
						</td>
					</tr>
				</table>
			
				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;padding:30px 0px 44px;font-family:arial;">
					<tr>
						<td style="width:749px; float:left;">
							<div style="width: 100%;float: left; margin-bottom:16px;">
								<div style="width:177px;float: left;height: 72px;text-align: center;border: 1px solid #505050;border-radius: 5px;border-top-width: 7px;padding: 10px 0;margin-right: 10px;">
									<b style="color: #6c6c6c;text-transform: uppercase;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">Amount Invoiced</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;">$<?php echo $jobDetails['PaymentAmount'];?>
									</p>
								</div>
								<div style="width:177px;float: left;height: 72px;text-align: center;border: 1px solid #505050;border-radius: 5px;border-top-width: 7px;padding: 10px 0;margin-right: 10px;">
									<b style="color: #6c6c6c;text-transform: uppercase;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">Total charges</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;">$<?php echo $jobDetails['totalCost'];?></p>
								</div>
								<div style="width:177px;float: left;height: 72px;text-align: center;border: 1px solid #505050;border-radius: 5px;border-top-width: 7px;padding: 10px 0;margin-right: 10px;">
									<b style="color: #6c6c6c;text-transform: uppercase;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">RATE/MILE</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;">$<?php echo $jobDetails['overall_total_rate_mile']; ?></p>
								</div>
								<div style="width:177px;float: left;height: 72px;text-align: center;border: 1px solid #505050;border-radius: 5px;border-top-width: 7px;padding: 10px 0;margin-right: 0px;">
									<b style="color: #6c6c6c;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">PROFIT</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;"><?php checkNegativeValue($jobDetails['overallTotalProfit']); ?></p>
									<span style="font-size: 12px;float: left;width: 100%;color: #000000;">(<?php echo $jobDetails['overallTotalProfitPercent'];?> %)</span>
								</div>
							</div>
							<table cellpadding="0" cellspacing="0" style="width:100%; float:left;border:1px solid #9f9f9f;border-radius:5px;">
								<tr>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 20px;">Origin</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo "{$jobDetails['OriginCity']}, {$jobDetails['OriginState']}"?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo $jobDetails['OriginCountry'];?></p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 20px;">Destination</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo "{$jobDetails['DestinationCity']}, {$jobDetails['DestinationState']}"?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo $jobDetails['DestinationCountry'];?></p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 20px;">Loaded Distance</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo $jobDetails['Mileage'];?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">$<?php echo $jobDetails['loadedDistanceCost']; ?></p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 20px;">DEAD MILES</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;font-size: 13px;color: #030101;"><?php echo $jobDetails['deadmiles']?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;font-size: 13px;color: #030101;">$<?php echo $jobDetails['deadMileDistCost']; ?></p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="width: 100%;font-size:13px;color: #6c6c6c;float: left;margin-bottom: 20px;">TOTAL MILES</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;font-size: 13px;color: #030101;"><?php echo ($jobDetails['Mileage'] + $jobDetails['deadmiles']); ?></p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;font-size: 13px;color: #030101;">$<?php echo ($jobDetails['loadedDistanceCost'] + $jobDetails['deadMileDistCost']); ?></p>
									</td>
									<td style="padding: 20px 0px;text-align: center;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 20px;">Estimated Fuel Cost</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo ($jobDetails['Mileage'] + $jobDetails['deadmiles']); ?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">$<?php echo $jobDetails['estimatedFuelCost']; ?></p>
									</td>
									
								</tr>
							</table>
						</td>
						<td style="width:305px; float:left;">
							<div style="padding-left:24px;">
								<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">Truck Details<span style="font-size: 13px;padding-left: 8px;position: relative;top: -2px;color: #363636;text-transform: none;">(Posted: <?php echo $jobDetails['Entered']; ?>)</span></h1>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Driver Name</span>
									<span style="float: right;font-size: 13px;color: #000000;"><?php echo $jobDetails['assignedDriverName']; ?></span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Truck Number </span>
									<span style="float: right;font-size: 13px;color: #000000;"><?php echo (isset($vehicleInfo['label']) && $vehicleInfo['label'] != '' ) ? $vehicleInfo['label'] : 'NA'; ?></span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Truck Length </span>
									<span style="float: right;font-size: 13px;color: #000000;"><?php echo (isset($vehicleInfo['cargo_bay_l']) && $vehicleInfo['cargo_bay_l'] != '' ) ? $vehicleInfo['cargo_bay_l'].' ft' : 'NA'; ?> </span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Truck Width</span>
									<span style="float: right;font-size: 13px;color: #000000;"><?php echo (isset($vehicleInfo['cargo_bay_w']) && $vehicleInfo['cargo_bay_w'] != '' ) ? $vehicleInfo['cargo_bay_w'].' ft' : 'NA'; ?> </span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Trailer Type</span>
									<span style="float: right;font-size: 13px;color: #000000;"><?php echo (isset($vehicleInfo['vehicle_type']) && $vehicleInfo['vehicle_type'] != '' ) ? $vehicleInfo['vehicle_type'] : 'NA'; ?>	</span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:15px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Trailer Number</span>
									<span style="float: right;font-size: 13px;color: #000000;"><?php echo (isset($vehicleInfo['unit_id']) && $vehicleInfo['unit_id'] ) ? $vehicleInfo['unit_id'] : 'NA'; ?></span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:15px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Max Weight</span>
									<span style="float: right;font-size: 13px;color: #000000;"><?php echo (isset($vehicleInfo['cargo_capacity']) && $vehicleInfo['cargo_capacity']) ? $vehicleInfo['cargo_capacity'].' LBS' : 'NA'; ?></span>
								</div>
							</div>
						</td>
					</tr>
					
				</table>
				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;font-family:arial;">
					<tr>
						<td style="width:100%;">
							<h2 style="font-size: 18px; text-transform: uppercase; color: rgb(54, 54, 54);">origin info</h2>
							<table cellpadding="0" cellspacing="0" style="width:100%; float:left;font-family:arial;border:1px solid #9f9f9f;border-radius:5px;">
								<tr>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Entity</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['shipper_entity']) ? $jobDetails['shipper_entity'] : ''; ?></p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Name</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;font-size: 13px;color: #030101;">
											<?php echo isset($jobDetails['shipper_name']) ? $jobDetails['shipper_name'] : ''; ?>
										</p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Telephone</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo isset($jobDetails['shipper_phone']) ? $jobDetails['shipper_phone'] : ''; ?>
										</p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup date</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo (isset($jobDetails['PickupDate']) && $jobDetails['PickupDate'] != '0000-00-00') ? $jobDetails['PickupDate'] : '';?>
										</p>
									</td>
									<td style="text-align: left; padding: 20px 22px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup Time</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo (isset($jobDetails['PickupTime']) && $jobDetails['PickupTime'] != '' ) ? $jobDetails['PickupTime'].' TO ' : '';?><?php echo $jobDetails['PickupTimeRangeEnd'];?></p>
									</td>
								</tr>
								<tr>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup Address</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['PickupAddress']) ? $jobDetails['PickupAddress'] : ''; ?></p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">City</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['OriginCity']) ? $jobDetails['OriginCity'] : ''; ?></p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">State</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['OriginState']) ? $jobDetails['OriginState'] : ''; ?></p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Zip Code</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['OriginZip']) ? $jobDetails['OriginZip'] : ''; ?></p>
									</td>
									<td style="padding: 20px 23px;text-align: left;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Counatry</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['OriginCountry']) ? $jobDetails['OriginCountry'] : '';  ?></p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table style="width:100%; float:left;padding:30px 0px 44px;font-family:arial;" cellspacing="0" cellpadding="0">
								<tbody>
									<tr>
										<?php if( isset($extraStopsData)) {
											 for( $i = 0; $i < count($extraStopsData); $i++ ) { ?>
											<td style="width:330px; float:left;padding-right: 22px;">
												<div>
													<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">extra stop (<?php echo $i + 1; ?>)</h1>
													<div style="width: 100%;float: left;padding-bottom:13px;">
														<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Entity</span>
														<span style="float: right;font-size: 13px;color: #000000;"><?php echo $extraStopsData[$i]['extraStopEntity']; ?></span>
													</div>
													<div style="width: 100%;float: left;padding-bottom:13px;">
														<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Name </span>
														<span style="float: right;font-size: 13px;color: #000000;"><?php echo $extraStopsData[$i]['extraStopName']; ?></span>
													</div>
													<div style="width: 100%;float: left;padding-bottom:13px;">
														<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Telephone </span>
														<span style="float: right;font-size: 13px;color: #000000;"><?php echo $extraStopsData[$i]['extraStopPhone']; ?></span>
													</div>
													<div style="width: 100%;float: left;padding-bottom:13px;">
														<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pickup date</span>
														<span style="float: right;font-size: 13px;color: #000000;"><?php echo (isset($extraStopsData[$i]['extraStopDate']) && $extraStopsData[$i]['extraStopDate'] != '0000-00-00' ) ? $extraStopsData[$i]['extraStopDate'] : ''; ?></span>
													</div>
													<div style="width: 100%;float: left;padding-bottom:13px;">
														<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pickup Time</span>
														<span style="float: right;font-size: 13px;color: #000000;">
															<?php echo (isset($extraStopsData[$i]['extraStopTime']) && $extraStopsData[$i]['extraStopTime'] != '' ) ? $extraStopsData[$i]['extraStopTime'].' To ' : ''; ?><?php echo $extraStopsData[$i]['extraStopTimeRange']; ?> 
														</span>
													</div>
													<div style="width: 100%;float: left;padding-bottom:15px;">
														<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Pickup Address</span>
														<span style="float: right;font-size: 13px;color: #000000;"><?php echo $extraStopsData[$i]['extraStopAddress']; ?></span>
													</div>
													<div style="width: 100%;float: left;padding-bottom:15px;">
														<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> City</span>
														<span style="float: right;font-size: 13px;color: #000000;"><?php echo $extraStopsData[$i]['extraStopCity']; ?></span>
													</div>
													<div style="width: 100%;float: left;padding-bottom:15px;">
														<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> State</span>
														<span style="float: right;font-size: 13px;color: #000000;"><?php echo $extraStopsData[$i]['extraStopState']; ?></span>
													</div>
													<div style="width: 100%;float: left;padding-bottom:15px;">
														<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Zip Code</span>
														<span style="float: right;font-size: 13px;color: #000000;"><?php echo $extraStopsData[$i]['extraStopZipCode']; ?></span>
													</div>
													<div style="width: 100%;float: left;padding-bottom:15px;">
														<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Country</span>
														<span style="float: right;font-size: 13px;color: #000000;"><?php echo $extraStopsData[$i]['extraStopCountry']; ?></span>
													</div>
												</div>
											</td>
										<?php } } ?>

									</tr>
									<tr>
										<td style="width:100%;">
											<h2 style="font-size: 18px; text-transform: uppercase; color: rgb(54, 54, 54); margin-top: 15px;">destination info</h2>
											<table style="width:100%; float:left;font-family:arial;border:1px solid #9f9f9f;border-radius:5px;" cellspacing="0" cellpadding="0">
												<tbody>
													<tr>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Entity</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['consignee_entity']) ? $jobDetails['consignee_entity'] : ''; ?></p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Name</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['consignee_name']) ? $jobDetails['consignee_name'] : ''; ?></p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Telephone</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['consignee_phone']) ? $jobDetails['consignee_phone'] : ''; ?></p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup date</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo (isset($jobDetails['DeliveryDate']) && $jobDetails['DeliveryDate'] != '0000-00-00') ? $jobDetails['DeliveryDate'] : ''; ?></p>
														</td>
														<td style="padding: 20px 23px;text-align: left;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup Time</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo (isset($jobDetails['DeliveryTime']) && $jobDetails['DeliveryTime'] != '') ? $jobDetails['DeliveryTime'].' TO ' : ''; ?><?php echo $jobDetails['DeliveryTimeRangeEnd']; ?></p>
														</td>
													</tr>
													<tr>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup Address</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['DestinationAddress']) ? $jobDetails['DestinationAddress'] : ''; ?></p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">City</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['DestinationCity']) ? $jobDetails['DestinationCity'] : ''; ?></p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">State</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['DestinationState']) ? $jobDetails['DestinationState'] : ''; ?></p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Zip Code</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['DestinationZip']) ? $jobDetails['DestinationZip'] : ''; ?></p>
														</td>
														<td style="padding: 20px 23px;text-align: left;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Counatry</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['DestinationCountry']) ? $jobDetails['DestinationCountry'] : '';  ?></p>
														</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
								
									<tr>
										<td style="width:100%;">
											<h2 style="font-size: 18px; text-transform: uppercase; color: rgb(54, 54, 54); margin-top: 33px;">load details</h2>
											<table style="width:100%; float:left;font-family:arial;border:1px solid #9f9f9f;border-radius:5px;" cellspacing="0" cellpadding="0">
												<tbody>
													<tr>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Equipment</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo $jobDetails['equipment']; ?></p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Equipment Options</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
															<?php 
															if ( isset($jobDetails['equipment_options']) && $jobDetails['equipment_options'] != '' ) {
																$equipmentOptions = $jobDetails['equipment_options'];
															} else if ( isset($jobDetails['EquipmentTypes']['Code']) && $jobDetails['EquipmentTypes']['Code'] != '' ) {
																$equipmentOptions = $jobDetails['EquipmentTypes']['Code'];
															} else {
																$equipmentOptions = '';
															}

															 echo $equipmentOptions; ?>
															 	
															 </p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Load SIZE </b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo $jobDetails['LoadType']; ?></p>
														</td>
														<td style="text-align: center; padding: 20px 22px;">
															<div style="display: inline-block; text-align: left; width: 51%;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">WEIGHT</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo (isset($jobDetails['Weight']) && $jobDetails['Weight'] != '' && $jobDetails['Weight'] != 0 ) ? $jobDetails['Weight'].' LBS' : ''; ?></p>
															</div>
															<div style="display: inline-block; text-align: left; width: 43%;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">LENGTH</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo (isset($jobDetails['Length']) && $jobDetails['Length'] != '' && $jobDetails['Length'] != 0 ) ? $jobDetails['Length'].' ft' : ''; ?></p>
															</div>
														</td>
													</tr>
													<tr>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Commodity</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['commodity']) ? $jobDetails['commodity'] : ''; ?></p>
														</td>
														<td style=" padding: 20px 23px;text-align: center;border-right: 1px solid rgb(159, 159, 159);border-top:1px solid #9f9f9f;">
															<div style=" text-align: left; width: 55%; float: left;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">pAYMENT AMOUNT</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">$<?php echo isset($jobDetails['PaymentAmount']) ? $jobDetails['PaymentAmount'] : ''; ?></p>
															</div>
															<div style=" text-align: left; width: 25%; float: right;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">rate</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['Rate']) ? $jobDetails['Rate'] : ''; ?></p>
															</div>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<div style=" text-align: left; width: 55%; float: left;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">lOAD QUANTITY</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['Quantity']) ? $jobDetails['Quantity'] : ''; ?></p>
															</div>
															<div style=" text-align: left; width: 30%; float: right;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Distance</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['Mileage']) ? $jobDetails['Mileage'] : ''; ?></p>
															</div>
														</td>
														<td style="text-align: left;  padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<div style="display: inline-block; text-align: left; width: 51%;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">W/O Ref. No. </b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['woRefno']) ? $jobDetails['woRefno'] : ''; ?></p>
															</div>
															<div style="display: inline-block; text-align: left; width: 43%;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Dead Miles</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo $jobDetails['deadmiles']; ?></p>
															</div>
														</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
									<?php if( isset($jobDetails['specInfo']) && $jobDetails['specInfo'] != '' ) { ?>
										<tr>
											<td style="width:100%;">
												<h2 style="font-size: 18px; text-transform: uppercase; color: rgb(54, 54, 54); margin-top: 33px; margin-bottom: 7px;">Special Info</h2>
												<p style="color: rgb(54, 54, 54); font-weight: normal; font-size: 14px; line-height: 22px; margin-top: 0px; display: inline-block;"><?php echo $jobDetails['specInfo']; ?></p>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</td>
						
					</tr>
				</table>

					<p style="page-break-after: always;" > </p>
				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;border-bottom:1px solid #b6b6b6;padding:44px 0px 15px;font-family:arial;">
					<tr>
						<td style="text-align:center;width:758px;">
						<h1 style="font-size:30px; color:#1f1f1f;padding:0px; margin:0px;">BROKER INFORMATION</h1></td>
					</tr>
				</table>

				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;font-family:arial;">
					<tr>
						<td>
							<h1 style="color: rgb(54, 54, 54); text-transform: uppercase; font-size: 25px; margin: 35px 0 30px;">
							<?php echo isset($brokerData['TruckCompanyName']) ? $brokerData['TruckCompanyName'] : '';?>
							<span style="font-size: 13px;padding-left: 8px;position: relative;top: -5px;color: #363636;text-transform: none;">(<?php echo isset($brokerData['brokerStatus']) ? $brokerData['brokerStatus'] : 'NA'; ?>)</span></h1>
						</td>
					</tr>
					<tr>
						<td>
							<table style="width:100%; float:left;padding:0 0 44px;font-family:arial;" cellspacing="0" cellpadding="0">
								<tbody>
									<tr>
										<td style="width:330px; float:left;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">AUTHORITY</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Broker MC
													</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($brokerData['MCNumber']) ? $brokerData['MCNumber'] : '' ?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Carrier MC </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($brokerData['CarrierMC']) ? $brokerData['CarrierMC'] : '' ;?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">US Dot  </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($brokerData['DOTNumber']) ? $brokerData['DOTNumber'] : '' ;?>
													</span>
												</div>											
											</div>
										</td>
									
										<td style="width: 330px; float: left; padding: 0px 33px;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">CONTACT INFORMATION</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Handle</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo ($jobDetails['PointOfContact']) ? $jobDetails['PointOfContact'] : 'NA'; ?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Contact </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo ($jobDetails['PointOfContactPhone']) ? $jobDetails['PointOfContactPhone'] : 'NA'; ?></span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Email </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo ($jobDetails['TruckCompanyEmail']) ? $jobDetails['TruckCompanyEmail'] : 'NA'; ?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Office</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo ($jobDetails['TruckCompanyPhone']) ? $jobDetails['TruckCompanyPhone'] : 'NA'; ?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Fax</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo ($jobDetails['TruckCompanyFax']) ? $jobDetails['TruckCompanyFax'] : 'NA'; ?>
													</span>
												</div>
											</div>
										</td>
									
										<td style="width:330px; float:left;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">ADDRESS</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Street Address</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($brokerData['postingAddress']) ? $brokerData['postingAddress'] : '';?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">City </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($brokerData['city']) ? $brokerData['city'] : '';?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">State </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($brokerData['state']) ? $brokerData['state'] : '';?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Zip Code
													</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($brokerData['zipcode']) ? $brokerData['zipcode'] : '';?> 
													</span>
												</div>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table>
			
		
				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;border-bottom:1px solid #b6b6b6;padding:44px 0px 15px;font-family:arial;">
					<tr>
						
						<td style="text-align:center;width:758px;">
							<h1 style="font-size:30px; color:#1f1f1f;padding:0px; margin:0px;">TRIP DETAILS</h1>
						</td>
						
					</tr>
				</table>
				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;font-family:arial;">
					<tr>
						<td>
							<table style="width:100%; float:left;padding:30px 0px 44px;font-family:arial;" cellspacing="0" cellpadding="0">
								<tbody>
									<tr>
										<td style="width:455px; float:left;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">DIESEL CALCULATIONS</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Vehicle/Avg</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['fuel_consumption']) ? $tripDetails[0]['fuel_consumption'] : 'NA';?>															
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Diesel Needed </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['gallon_needed']) ? $tripDetails[0]['gallon_needed'] : 'NA';?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Fuel Per Gallon  </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['diesel_rate_per_gallon']) ? '$'.$tripDetails[0]['diesel_rate_per_gallon'] : 'NA';?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Avg cost of Diesel </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<b style="font-weight: bold; font-size: 15px;">
														<?php echo isset($tripDetails[0]['comp_diesel_cost']) ? '$'.$tripDetails[0]['comp_diesel_cost'] : 'NA';	?>
														</b>
													</span>
												</div>
											
											</div>
											<div style="width: 100%; float: left; margin-top: 21px;">
												<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">Toll and taxes</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">IFTA Taxes</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['tax_ifta_tax']) ? '$'.$tripDetails[0]['tax_ifta_tax'] : 'NA'; ?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Tarps </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['tax_tarps']) ? '$'.$tripDetails[0]['tax_tarps'] : 'NA'; ?>
															
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Detention Time </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['tax_det_time']) ? '$'.$tripDetails[0]['tax_det_time'] : 'NA'; ?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Tolls</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['tax_tolls']) ? '$'.$tripDetails[0]['tax_tolls'] : 'NA'; ?> 
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #000;font-family: arial;font-weight: bold;font-size: 13px;float: left;">
														<b style="font-weight: bold; font-size: 14px;">CHARGES</b></span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<b style="font-weight: bold; font-size: 15px;">
															<?php echo isset($tripDetails[0]['tax_total_charge']) ? '$'.$tripDetails[0]['tax_total_charge'] : 'NA'; ?>
														</b>
													</span>
												</div>
											</div>											
										</td>
										<td style="width: 565px; float: left; padding-left: 40px;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">DRIVER CALCULATIONS</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Origin to Destination</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['originToDestination']) ? $tripDetails[0]['originToDestination'] : 'NA';?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Dead Miles to Destination </span>
													<span style="float: right;font-size: 13px;color: #000000;"><?php echo isset($tripDetails[0]['deadMileDist']) ? $tripDetails[0]['deadMileDist'] : 'NA'; ?></span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Total Miles </span>
													<span style="float: right;font-size: 13px;color: #000000;">
													<?php echo isset($tripDetails[0]['total_complete_distance']) ? $tripDetails[0]['total_complete_distance'] : 'NA'; ?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Dead miles not Paid</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['driver_dead_miles_not_paid']) ? '$'.$tripDetails[0]['driver_dead_miles_not_paid'] : 'NA'; ?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Dead head miles paid</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['driver_dead_miles_paid']) ? '$'.$tripDetails[0]['driver_dead_miles_paid'] : 'NA'; ?> 
														</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pay for dead head Miles</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['driver_pay_for_dead_mile']) ? '$'.$tripDetails[0]['driver_pay_for_dead_mile'] : 'NA'; ?> 
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Dead Miles Paid</span>
													<span style="float: right;font-size: 13px;color: #000000;"><?php echo isset($tripDetails[0]['driver_dead_mile_paid']) ? '$'.$tripDetails[0]['driver_dead_mile_paid'] : 'NA'; ?> </span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Extra Stop charges</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['xtraStopCharges']) ? '$'.$tripDetails[0]['xtraStopCharges'] : 'NA'; ?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pay for milesw/cargo</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo isset($tripDetails[0]['driver_pay_miles_cargo']) ? '$'.$tripDetails[0]['driver_pay_miles_cargo'] : 'NA'; ?>
													</span>
												</div>
												
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Amount Paid With Cargo</span>
													<span style="float: right;font-size: 13px;color: #000000;">
													<?php echo isset($tripDetails[0]['driver_amount_cargo']) ? '$'.$tripDetails[0]['driver_amount_cargo'] : 'NA'; ?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #000;font-family: arial;font-weight: bold;float: left;"><b style="font-weight: bold; font-size: 14px;">Due to Driver</b></span>
													<span style="float: right;color: #000000;">
														<b style="font-weight: bold; font-size: 15px;">
															<?php echo isset($tripDetails[0]['driver_due_driver']) ? '$'.$tripDetails[0]['driver_due_driver'] : 'NA'; ?>
													 	</b>
													 </span>
												</div>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table>

			</td>
		</tr>
	</table>

</body>
</html>
