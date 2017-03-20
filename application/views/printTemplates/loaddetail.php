<html>
<head>

<style type="text/css">
    table { page-break-inside:auto }
    table.extra-stops { page-break-inside:auto }
    tr { page-break-inside:avoid; page-break-after:auto }
    thead { display:table-header-group }
</style>

</head>
<body>

<table cellpadding="0" cellspacing="0" style="width:1060px; margin:0px auto;padding:0px;">
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;border-bottom:1px solid #b6b6b6;padding:44px 0px 34px;font-family:arial;">
					<tr>
						<td style="width:118px;">

							<img src="<?php echo base_url(); ?>assets/img/print_logo.png" >
						</td>
						<td style="text-align:center;width:758px;">
							<h1 style="font-size:30px; color:#1f1f1f;padding:0px; margin:0px;">LOAD DETAILS</h1>
						</td>
						<td style="width:300px; ">
							<div style="width:300px; float:right;">
								<div style="float: left;width: 78px;font-size: 13px;">
									<b style="width: 100%;float: left;font-size: 13px;color: #868686;font-weight: bold;margin-bottom: 10px;">LOAD ID</b><?php echo (isset($jobDetails['id']) && $jobDetails['id'] != '' ) ? $jobDetails['id'] : 'NA';?>
									</div>
								<div style="float: left;width: 102px;font-size: 13px;">
									<b style="width: 100%;float: left;font-size: 13px;color: #868686;font-weight: bold;margin-bottom: 10px;">INVOICE NO</b><?php echo (isset($jobDetails['invoiceNo']) && $jobDetails['invoiceNo']) ? $jobDetails['invoiceNo'] : 'NA'; ?>
								</div>
								<a style="width: 111px;float: right;border: 1px solid #6c6c6c;text-align: center;border-radius: 3px;text-decoration: none;font-size: 14px;color: #6c6c6c;padding: 11px 0px;">
									<?php echo ($jobDetails['JobStatus'] != '') ? ucfirst($jobDetails['JobStatus']) : 'No Status'; ?>
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
									<b style="color: #6c6c6c;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">AMOUNT INVOICED</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;">
									<?php echo (!empty($jobDetails['PaymentAmount']))?'$'.$jobDetails['PaymentAmount']:'NA';?>
									</p>
								</div>
								<div style="width:177px;float: left;height: 72px;text-align: center;border: 1px solid #505050;border-radius: 5px;border-top-width: 7px;padding: 10px 0;margin-right: 10px;">
									<b style="color: #6c6c6c;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">TOTAL CHARGES</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;">
										<?php echo (!empty($jobDetails['totalCost']))?'$'.$jobDetails['totalCost']:'NA';?>
									</p>
								</div>
								<div style="width:177px;float: left;height: 72px;text-align: center;border: 1px solid #505050;border-radius: 5px;border-top-width: 7px;padding: 10px 0;margin-right: 10px;">
									<b style="color: #6c6c6c;text-transform: uppercase;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">RATE/MILE</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;"><?php echo (!empty($jobDetails['overall_total_rate_mile']))? '$'.$jobDetails['overall_total_rate_mile']:'NA'; ?></p>
								</div>
								<div style="width:177px;float: left;height: 72px;text-align: center;border: 1px solid #505050;border-radius: 5px;border-top-width: 7px;padding: 10px 0;margin-right: 0px;">
									<b style="color: #6c6c6c;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">PROFIT</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;">
										<?php checkNegativeValue($jobDetails['overallTotalProfit']); ?>
											
									</p>
									<span style="font-size: 12px;float: left;width: 100%;color: #000000;">(<?php echo $jobDetails['overallTotalProfitPercent'];?> %)</span>
								</div>
							</div>
							<table cellpadding="0" cellspacing="0" style="width:100%; float:left;border:1px solid #9f9f9f;border-radius:5px;">
								<tr>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 20px;">Origin</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo "{$jobDetails['OriginCity']}, {$jobDetails['OriginState']}"?> 
										</p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo $jobDetails['OriginCountry'];?>
												
										</p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 20px;">DESTINATION</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo "{$jobDetails['DestinationCity']}, {$jobDetails['DestinationState']}"?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
												<?php echo ($jobDetails['DestinationCountry'])?$jobDetails['DestinationCountry']:'NA';?>
										</p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 20px;">Loaded Distance</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
										<?php echo (!empty($jobDetails['Mileage']))?$jobDetails['Mileage']:'NA';?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
										$<?php echo (!empty($jobDetails['loadedDistanceCost']))?$jobDetails['loadedDistanceCost']:'NA'; ?></p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 20px;">DEAD MILES</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;font-size: 13px;color: #030101;"><?php echo (!empty($jobDetails['deadmiles']))?$jobDetails['deadmiles']:'NA';?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;font-size: 13px;color: #030101;">$<?php echo (!empty($jobDetails['deadMileDistCost']))?$jobDetails['deadMileDistCost']:''; ?></p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="width: 100%;font-size:13px;color: #6c6c6c;float: left;margin-bottom: 20px;">TOTAL MILES</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;font-size: 13px;color: #030101;"><?php echo ($jobDetails['Mileage'] + $jobDetails['deadmiles']); ?></p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;font-size: 13px;color: #030101;">$<?php echo ($jobDetails['loadedDistanceCost'] + $jobDetails['deadMileDistCost']); ?></p>
									</td>
									<td style="padding: 20px 0px;text-align: center;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 20px;">ESTIMATED FUEL COST</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo ($jobDetails['Mileage'] + $jobDetails['deadmiles']); ?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo (!empty($jobDetails['estimatedFuelCost']))?'$'.$jobDetails['estimatedFuelCost']:'NA'; ?>
											</p>
									</td>
									
								</tr>
							</table>
						</td>
						<td style="width:305px; float:left;">
							<div style="padding-left:24px;">
								<h1 style="font-size: 18px;margin: 0px;color: #363636;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">TRUCK DETAILS<span style="font-size: 13px;padding-left: 8px;position: relative;top: -2px;color: #363636;text-transform: none;">(Posted: <?php echo $jobDetails['Entered']; ?>)</span></h1>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">DRIVER NAME</span>
									<span style="float: right;font-size: 13px;color: #000000;">
										<?php echo (!empty($jobDetails['assignedDriverName']))?$jobDetails['assignedDriverName']:'NA';?></span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">TRUCK NUMBER </span>
									<span style="float: right;font-size: 13px;color: #000000;">
										<?php echo (isset($vehicleInfo['label']) && $vehicleInfo['label'] != '' ) ? $vehicleInfo['label'] : 'NA'; ?></span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">TRUCK LENGTH </span>
									<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo (isset($vehicleInfo['cargo_bay_l']) && $vehicleInfo['cargo_bay_l'] != '' ) ? $vehicleInfo['cargo_bay_l'].' ft' : 'NA'; ?> </span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">TRUCK WIDTH</span>
									<span style="float: right;font-size: 13px;color: #000000;">
										<?php echo (isset($vehicleInfo['cargo_bay_w']) && $vehicleInfo['cargo_bay_w'] != '' ) ? $vehicleInfo['cargo_bay_w'].' ft' : 'NA'; ?> 
									</span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">TRAILER TYPE</span>
									<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo (isset($vehicleInfo['vehicle_type']) && $vehicleInfo['vehicle_type'] != '' ) ? $vehicleInfo['vehicle_type'] : 'NA'; ?>	</span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:15px;">
									<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> TRAILER NUMBER</span>
									<span style="float: right;font-size: 13px;color: #000000;">
										<?php echo (isset($vehicleInfo['unit_id']) && $vehicleInfo['unit_id'] ) ? $vehicleInfo['unit_id'] : 'NA'; ?>
									</span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:15px;">
									<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> MAX WEIGHT</span>
									<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo (isset($vehicleInfo['cargo_capacity']) && $vehicleInfo['cargo_capacity']) ? $vehicleInfo['cargo_capacity'].' LBS' : 'NA'; ?></span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td style="width:100%;padding-top:30px;">
							<h2 style="font-size: 18px; color: rgb(54, 54, 54);">ORIGIN INFO</h2>
							<table cellpadding="0" cellspacing="0" style="width:100%; float:left;font-family:arial;border:1px solid #9f9f9f;border-radius:5px;">
								<tr>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">ENTITY</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo isset($jobDetails['shipper_entity']) ? $jobDetails['shipper_entity'] : ''; ?>
										</p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">NAME</b>
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
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">PICKUP DATE</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo (isset($jobDetails['PickupDate']) && $jobDetails['PickupDate'] != '0000-00-00') ? $jobDetails['PickupDate'] : '';?>
										</p>
									</td>
									<td style="text-align: left; padding: 20px 22px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">PICKUP TIME</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo (isset($jobDetails['PickupTime']) && $jobDetails['PickupTime'] != '' ) ? $jobDetails['PickupTime'].' TO ' : '';?><?php echo $jobDetails['PickupTimeRangeEnd'];?></p>
									</td>
								</tr>
								<tr>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">PICKUP ADDRESS</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['PickupAddress']) ? $jobDetails['PickupAddress'] : ''; ?></p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">CITY</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['OriginCity']) ? $jobDetails['OriginCity'] : ''; ?></p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">STATE</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['OriginState']) ? $jobDetails['OriginState'] : ''; ?></p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">ZIP CODE</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['OriginZip']) ? $jobDetails['OriginZip'] : ''; ?></p>
									</td>
									<td style="padding: 20px 23px;text-align: left;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">COUNTRY</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['OriginCountry']) ? $jobDetails['OriginCountry'] : '';  ?></p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					
				</table>


			</td>
		</tr>
	</table>
	<table cellpadding="0" cellspacing="0" style="width:1060px; margin:0px auto;padding:0px; font-family:arial;">
		<tr>
			<?php if( isset($extraStopsData)) {
				 for( $i = 0; $i < count($extraStopsData); $i++ ) { ?>
				<td style="width:330px; float:left;padding-right: 22px;">
					<div>
						<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">extra stop (<?php echo $i + 1; ?>)</h1>
						<div style="width: 100%;float: left;padding-bottom:13px;">
							<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">ENTITY</span>
							<span style="float: right;font-size: 13px;color: #000000;"><?php echo $extraStopsData[$i]['extraStopEntity']; ?></span>
						</div>
						<div style="width: 100%;float: left;padding-bottom:13px;">
							<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">NAME </span>
							<span style="float: right;font-size: 13px;color: #000000;"><?php echo $extraStopsData[$i]['extraStopName']; ?></span>
						</div>
						<div style="width: 100%;float: left;padding-bottom:13px;">
							<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">TELEPHONE </span>
							<span style="float: right;font-size: 13px;color: #000000;"><?php echo $extraStopsData[$i]['extraStopPhone']; ?></span>
						</div>
						<div style="width: 100%;float: left;padding-bottom:13px;">
							<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">PICKUP DATE </span>
							<span style="float: right;font-size: 13px;color: #000000;"><?php echo (isset($extraStopsData[$i]['extraStopDate']) && $extraStopsData[$i]['extraStopDate'] != '0000-00-00' ) ? $extraStopsData[$i]['extraStopDate'] : ''; ?></span>
						</div>
						<div style="width: 100%;float: left;padding-bottom:13px;">
							<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">PICKUP TIME</span>
							<span style="float: right;font-size: 13px;color: #000000;">
								<?php echo (isset($extraStopsData[$i]['extraStopTime']) && $extraStopsData[$i]['extraStopTime'] != '' ) ? $extraStopsData[$i]['extraStopTime'].' To ' : ''; ?><?php echo $extraStopsData[$i]['extraStopTimeRange']; ?> 
							</span>
						</div>
						<div style="width: 100%;float: left;padding-bottom:15px;">
							<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> PICKUP ADDRESS</span>
							<span style="float: right;font-size: 13px;color: #000000;">
								<?php echo ($extraStopsData[$i]['extraStopAddress'])?$extraStopsData[$i]['extraStopAddress']:'NA'; ?>
							</span>
						</div>
						<div style="width: 100%;float: left;padding-bottom:15px;">
							<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> CITY</span>
							<span style="float: right;font-size: 13px;color: #000000;">
								<?php echo ($extraStopsData[$i]['extraStopCity'])?$extraStopsData[$i]['extraStopCity']:'NA'; ?>
							</span>
						</div>
						<div style="width: 100%;float: left;padding-bottom:15px;">
							<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> STATE</span>
							<span style="float: right;font-size: 13px;color: #000000;">
								<?php echo ($extraStopsData[$i]['extraStopState'])?$extraStopsData[$i]['extraStopState']:'NA';?>
							</span>
						</div>
						<div style="width: 100%;float: left;padding-bottom:15px;">
							<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> ZIP CODE</span>
							<span style="float: right;font-size: 13px;color: #000000;">
							<?php echo ($extraStopsData[$i]['extraStopZipCode'])?$extraStopsData[$i]['extraStopZipCode']:'NA'; ?>
								
							</span>
						</div>
						<div style="width: 100%;float: left;padding-bottom:15px;">
							<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> COUNTRY</span>
							<span style="float: right;font-size: 13px;color: #000000;">
								<?php echo ($extraStopsData[$i]['extraStopCountry'])?$extraStopsData[$i]['extraStopCountry']:'NA'; ?>
								</span>
						</div>
					</div>
				</td>
			<?php } } ?>

		</tr>
	</table>
	<table cellpadding="0" cellspacing="0" style="width:1060px; margin:0px auto;padding:0px;font-family:arial;">
		<tr>
			<td style="width:100%;">
				<h2 style="font-size: 18px; color: rgb(54, 54, 54); margin-top: 15px;">DESTINATION INFO</h2>
				<table style="width:100%; float:left;font-family:arial;border:1px solid #9f9f9f;border-radius:5px;" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">ENTITY</b>
								<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['consignee_entity']) ? $jobDetails['consignee_entity'] : ''; ?></p>
							</td>
							<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">NAME</b>
								<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['consignee_name']) ? $jobDetails['consignee_name'] : ''; ?></p>
							</td>
							<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">TELEPHONE</b>
								<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['consignee_phone']) ? $jobDetails['consignee_phone'] : ''; ?></p>
							</td>
							<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">DROP OFF DATE</b>
								<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo (isset($jobDetails['DeliveryDate']) && $jobDetails['DeliveryDate'] != '0000-00-00') ? $jobDetails['DeliveryDate'] : ''; ?></p>
							</td>
							<td style="padding: 20px 23px;text-align: left;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">DROP OFF TIME</b>
								<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo (isset($jobDetails['DeliveryTime']) && $jobDetails['DeliveryTime'] != '') ? $jobDetails['DeliveryTime'].' TO ' : ''; ?><?php echo $jobDetails['DeliveryTimeRangeEnd']; ?></p>
							</td>
						</tr>
						<tr>
							<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">DELIVERY ADDRESS</b>
								<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['DestinationAddress']) ? $jobDetails['DestinationAddress'] : ''; ?></p>
							</td>
							<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">CITY</b>
								<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['DestinationCity']) ? $jobDetails['DestinationCity'] : ''; ?></p>
							</td>
							<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">STATE</b>
								<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['DestinationState']) ? $jobDetails['DestinationState'] : ''; ?></p>
							</td>
							<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">ZIP CODE</b>
								<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['DestinationZip']) ? $jobDetails['DestinationZip'] : ''; ?></p>
							</td>
							<td style="padding: 20px 23px;text-align: left;border-top:1px solid #9f9f9f;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">COUNTRY</b>
								<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo isset($jobDetails['DestinationCountry']) ? $jobDetails['DestinationCountry'] : '';  ?></p>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	
		<tr>
			<td style="width:100%;">
				<h2 style="font-size: 18px;color: rgb(54, 54, 54); margin-top: 33px;">LOAD DETAILS</h2>
				<table style="width:100%; float:left;font-family:arial;border:1px solid #9f9f9f;border-radius:5px;" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">EQUIPMENT</b>
								<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo $jobDetails['equipment']; ?></p>
							</td>
							<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
								<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 13px;">EQUIPMENT OPIONS</b>
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
	</table>
	<table cellpadding="0" cellspacing="0" style="width:1060px; margin:0px auto;padding:0px;font-family:arial;">
		<tr>
			<td>
				<div style="text-align:center;width:100%; border-bottom:1px solid #b6b6b6;padding:44px 0px 15px;">
					<h1 style="font-size:30px; color:#1f1f1f;padding:0px; margin:0px;">BROKER INFORMATION</h1>
				</div>
				<h1 style="color: rgb(54, 54, 54); text-transform: uppercase; font-size: 25px; margin: 35px 0 30px;">
				<?php echo isset($brokerData['TruckCompanyName']) ? $brokerData['TruckCompanyName'] : '';?>
				<span style="font-size: 13px;padding-left: 8px;position: relative;top: -5px;color: #363636;text-transform: none;">(<?php echo isset($brokerData['brokerStatus']) ? $brokerData['brokerStatus'] : 'NA'; ?>)</span></h1>
				<div style="width:100%; float:left;padding:0 0 44px;font-family:arial;">
					<div style="width:330px; float:left;">
						<div>
							<h1 style="font-size: 18px;margin: 0px;color: #363636;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">AUTHORITY</h1>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">BROCKER MC
								</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($brokerData['MCNumber']) ? $brokerData['MCNumber'] : '' ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">CARRIER MC </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($brokerData['CarrierMC']) ? $brokerData['CarrierMC'] : '' ;?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">US DOT  </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($brokerData['DOTNumber']) ? $brokerData['DOTNumber'] : '' ;?>
								</span>
							</div>											
						</div>
					</div>
				
					<div style="width: 330px; float: left; padding: 0px 33px;">
						<div>
							<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">CONTACT INFORMATION</h1>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">HANDLE</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo ($jobDetails['PointOfContact']) ? $jobDetails['PointOfContact'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">CONTACT </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo ($jobDetails['PointOfContactPhone']) ? $jobDetails['PointOfContactPhone'] : 'NA'; ?></span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">EMAIL </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo ($jobDetails['TruckCompanyEmail']) ? $jobDetails['TruckCompanyEmail'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">OFFICE</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo ($jobDetails['TruckCompanyPhone']) ? $jobDetails['TruckCompanyPhone'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">FAX</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo ($jobDetails['TruckCompanyFax']) ? $jobDetails['TruckCompanyFax'] : 'NA'; ?>
								</span>
							</div>
						</div>
					</div>
				
					<div style="width:330px; float:left;">
						<div>
							<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">ADDRESS</h1>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">STREET ADDRESS</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($brokerData['postingAddress']) ? $brokerData['postingAddress'] : '';?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">CITY </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($brokerData['city']) ? $brokerData['city'] : '';?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">STATE </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($brokerData['state']) ? $brokerData['state'] : '';?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">ZIP CODE
								</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($brokerData['zipcode']) ? $brokerData['zipcode'] : '';?> 
								</span>
							</div>
						</div>
					</div>
				</div>
			</td>
		</tr>
		
		<tr>
			<td>
				<div style="text-align:center;width:100%;border-bottom:1px solid #b6b6b6;padding:44px 0px 15px;">
					<h1 style="font-size:30px; color:#1f1f1f;padding:0px; margin:0px;">TRIP DETAILS</h1>
				</div>
				<div style="padding:44px 0 0;float:left;">
					<div style="width:455px; float:left;">
						<div>
							<h1 style="font-size: 18px;margin: 0px;color: #363636;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">DIESEL CALCULATIONS</h1>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">VEHICLE/AVG</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['fuel_consumption']) ? $tripDetails[0]['fuel_consumption'] : 'NA';?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">DIESEL NEEDED </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['gallon_needed']) ? $tripDetails[0]['gallon_needed'] : 'NA';?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">FUEL PER GALLON </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['diesel_rate_per_gallon']) ? '$'.$tripDetails[0]['diesel_rate_per_gallon'] : 'NA';?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">AVG COST OF DIESEL </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<b style="font-weight: bold; font-size: 15px;">
									<?php echo isset($tripDetails[0]['comp_diesel_cost']) ? '$'.$tripDetails[0]['comp_diesel_cost'] : 'NA';	?>
									</b>
								</span>
							</div>
						
						</div>
						<div style="width: 100%; float: left; margin-top: 21px;">
							<h1 style="font-size: 18px;margin: 0px;color: #363636;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">TOLL AND TAXEX</h1>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">IFTA TAXEX</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['tax_ifta_tax']) ? '$'.$tripDetails[0]['tax_ifta_tax'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">TARPS </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['tax_tarps']) ? '$'.$tripDetails[0]['tax_tarps'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">DETENTION TIME </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['tax_det_time']) ? '$'.$tripDetails[0]['tax_det_time'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">TOLLS</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['tax_tolls']) ? '$'.$tripDetails[0]['tax_tolls'] : 'NA'; ?> 
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #000;font-family: arial;font-weight: bold;font-size: 13px;float: left;">
									<b style="font-weight: bold; font-size: 14px;">CHARGES</b>
								</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<b style="font-weight: bold; font-size: 15px;">
										<?php echo isset($tripDetails[0]['tax_total_charge']) ? '$'.$tripDetails[0]['tax_total_charge'] : 'NA'; ?>
									</b>
								</span>
							</div>
						</div>											
					</div>
					<div style="width: 565px; float: left; padding-left: 40px;">
						<div>
							<h1 style="font-size: 18px;margin: 0px;color: #363636;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">DRIVER CALCULATIONS</h1>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">ORIGIN TO DESTINATION</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['originToDestination']) ? $tripDetails[0]['originToDestination'] : 'NA';?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">DEAD MILES TO DESTINATION </span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['deadMileDist']) ? $tripDetails[0]['deadMileDist'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">TOTAL MILES </span>
								<span style="float: right;font-size: 13px;color: #000000;">
								<?php echo isset($tripDetails[0]['total_complete_distance']) ? $tripDetails[0]['total_complete_distance'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">DEAD MILES NOT PAID</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['driver_dead_miles_not_paid']) ? '$'.$tripDetails[0]['driver_dead_miles_not_paid'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">DEAD HEAD MILES PAID</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['driver_dead_miles_paid']) ? '$'.$tripDetails[0]['driver_dead_miles_paid'] : 'NA'; ?> 
									</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">PAY FOR DEAD HEAD MILES</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['driver_pay_for_dead_mile']) ? '$'.$tripDetails[0]['driver_pay_for_dead_mile'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">DEAD MILES PAID</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['driver_dead_mile_paid']) ? '$'.$tripDetails[0]['driver_dead_mile_paid'] : 'NA'; ?> 
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">EXTRA STOP CHARGES</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['xtraStopCharges']) ? '$'.$tripDetails[0]['xtraStopCharges'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">PAY FOR MILESW/CARGO</span>
								<span style="float: right;font-size: 13px;color: #000000;">
									<?php echo isset($tripDetails[0]['driver_pay_miles_cargo']) ? '$'.$tripDetails[0]['driver_pay_miles_cargo'] : 'NA'; ?>
								</span>
							</div>
							
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">AMOUNT PAID WITH CARGO</span>
								<span style="float: right;font-size: 13px;color: #000000;">
								<?php echo isset($tripDetails[0]['driver_amount_cargo']) ? '$'.$tripDetails[0]['driver_amount_cargo'] : 'NA'; ?>
								</span>
							</div>
							<div style="width: 100%;float: left;padding-bottom:13px;">
								<span style="color: #000;font-family: arial;font-weight: bold;float: left;">
									<b style="font-weight: bold; font-size: 14px;">DUE TO DRIVER</b>
								</span>
								<span style="float: right;color: #000000;">
									<b style="font-weight: bold; font-size: 15px;">
										<?php echo isset($tripDetails[0]['driver_due_driver']) ? '$'.$tripDetails[0]['driver_due_driver'] : 'NA'; ?>
								 	</b>
								 </span>
							</div>
						</div>
					</div>
				</div>
			</td>
		</tr>
	</table>
</body>
</html>