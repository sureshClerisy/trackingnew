<html>
<head>

</head>
<body>
	<table cellpadding="0" cellspacing="0" style="width:1170px; margin:0px auto;padding:0px;">
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;border-bottom:1px solid #b6b6b6;padding:44px 0px 34px;font-family:arial;">
					<tr>
						<td style="width:118px;">
							<img src="<?php echo base_url();?>/assets/img/print_logo.png">
						</td>
						<td style="text-align:center;width:758px;">
							<h1 style="font-size:30px; color:#1f1f1f;padding:0px; margin:0px;">LOAD DETAILS</h1>
						</td>
						<td style="width:300px; ">
							<div style="width:300px; float:right;">
								<div style="float: left;width: 78px;font-size: 13px;">
									<b style="width: 100%;text-transform: uppercase;float: left;font-size: 13px;color: #868686;font-weight: bold;margin-bottom: 10px;">Load ID</b><?php echo $encodedJobRecord['id'];?>
									</div>
								<div style="float: left;width: 102px;font-size: 13px;">
									<b style="width: 100%;text-transform: uppercase;float: left;font-size: 13px;color: #868686;font-weight: bold;margin-bottom: 10px;">Invoice No</b><?php echo ($encodedJobRecord['invoiceNo'])?$encodedJobRecord['invoiceNo']:'NA';?>
								</div>
								<a href="#" style="width: 111px;float: right;border: 1px solid #6c6c6c;text-align: center;border-radius: 3px;text-decoration: none;font-size: 14px;color: #6c6c6c;padding: 11px 0px;">
									<?php echo ($encodedJobRecord['JobStatus'])?$encodedJobRecord['JobStatus']:'NA';?>
								</a>
							</div>
						</td>
					</tr>
				</table>
				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;padding:30px 0px 44px;font-family:arial;">
					<tr>
						<td style="width:830px; float:left;">
							<div style="width: 100%;float: left; margin-bottom:16px;">
								<div style="width:198px;float: left;height: 72px;text-align: center;border: 1px solid #505050;border-radius: 5px;border-top-width: 7px;padding: 10px 0;margin-right: 10px;">
									<b style="color: #6c6c6c;text-transform: uppercase;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">Amount Invoiced</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;">$<?php echo $encodedJobRecord['PaymentAmount'];?>
									</p>
								</div>
								<div style="width:198px;float: left;height: 72px;text-align: center;border: 1px solid #505050;border-radius: 5px;border-top-width: 7px;padding: 10px 0;margin-right: 10px;">
									<b style="color: #6c6c6c;text-transform: uppercase;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">Total charges</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;">$<?php echo $encodedJobRecord['totalCost'];?></p>
								</div>
								<div style="width:198px;float: left;height: 72px;text-align: center;border: 1px solid #505050;border-radius: 5px;border-top-width: 7px;padding: 10px 0;margin-right: 10px;">
									<b style="color: #6c6c6c;text-transform: uppercase;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">RATE/MILE ****</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;">$1.54</p>
								</div>
								<div style="width:198px;float: left;height: 72px;text-align: center;border: 1px solid #505050;border-radius: 5px;border-top-width: 7px;padding: 10px 0;margin-right: 0px;">
									<b style="color: #6c6c6c;font-size: 13px;font-weight: bold;margin-bottom: 10px;width: 100%;float: left;">PROFIT</b>
									<p style="width: 100%;float: left;margin: 0px;font-size: 24px;">$<?php echo $encodedJobRecord['overallTotalProfit'];?></p>
									<span style="font-size: 12px;float: left;width: 100%;color: #000000;">(<?php echo $encodedJobRecord['overallTotalProfitPercent'];?> %)</span>
								</div>
							</div>
							<table cellpadding="0" cellspacing="0" style="width:100%; float:left;border:1px solid #9f9f9f;border-radius:5px;">
								<tr>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 20px;">Origin</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo "{$encodedJobRecord['OriginCity']}, {$encodedJobRecord['OriginState']}"?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo $encodedJobRecord['OriginCountry'];?></p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 20px;">Destination</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo "{$encodedJobRecord['DestinationCity']}, {$encodedJobRecord['DestinationState']}"?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo $encodedJobRecord['DestinationCountry'];?></p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 20px;">Loaded Distance****</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"><?php echo $encodedJobRecord['originalDistance'];?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">$2,364.27-------</p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;float: left;margin-bottom: 20px;">DEAD MILES</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;font-size: 13px;color: #030101;"><?php echo $encodedJobRecord['deadmiles']?> </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;font-size: 13px;color: #030101;">$2,364.27----------</p>
									</td>
									<td style="padding: 20px 0px;text-align: center;border-right: 1px solid #9f9f9f;">
										<b style="width: 100%;font-size:13px;color: #6c6c6c;float: left;margin-bottom: 20px;">TOTAL MILES</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;font-size: 13px;color: #030101;">2728 -----</p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;font-size: 13px;color: #030101;">$2,364.27----</p>
									</td>
									<td style="padding: 20px 0px;text-align: center;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 20px;">Estimated Fuel Cost</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 10px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">2728 </p>
										<p style="padding: 0px;width: 100%;margin: 0px 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">$2,364.27</p>
									</td>
									
								</tr>
							</table>
						</td>
						<td style="width:340px; float:left;">
							<div style="padding-left:24px;">
								<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">Truck Details<span style="font-size: 13px;padding-left: 8px;position: relative;top: -2px;color: #363636;text-transform: none;">(Posted: 2017-03-02)</span></h1>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Driver Name</span>
									<span style="float: right;font-size: 13px;color: #000000;">Sergio Penenori-104</span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Truck Number </span>
									<span style="float: right;font-size: 13px;color: #000000;">104</span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Truck Length </span>
									<span style="float: right;font-size: 13px;color: #000000;">48 ft </span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Truck Width</span>
									<span style="float: right;font-size: 13px;color: #000000;">8.5 ft </span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:13px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Trailer Type</span>
									<span style="float: right;font-size: 13px;color: #000000;">SD  </span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:15px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Trailer Number</span>
									<span style="float: right;font-size: 13px;color: #000000;"></span>
								</div>
								<div style="width: 100%;float: left;padding-bottom:15px;">
									<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Max Weight</span>
									<span style="float: right;font-size: 13px;color: #000000;">47000LBS</span>
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
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">Shipper</p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Name</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;font-size: 13px;color: #030101;">
											<?php echo $encodedJobRecord['shipper_name'];?>
										</p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Telephone</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo $encodedJobRecord['shipper_phone'];?>
										</p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup date</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo $encodedJobRecord['PickupDate'];?>
										</p>
									</td>
									<td style="text-align: left; padding: 20px 22px;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup Time</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">
											<?php echo $encodedJobRecord['PickupTime'];?> to <?php echo $encodedJobRecord['PickupTimeRangeEnd'];?></p>
									</td>
								</tr>
								<tr>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup Address</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">3131 N. COLUMBIA BLVD</p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">City</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">City</p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">State</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">State</p>
									</td>
									<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Zip Code</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">00000</p>
									</td>
									<td style="padding: 20px 23px;text-align: left;border-top:1px solid #9f9f9f;">
										<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Counatry</b>
										<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">USA</p>
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
										<td style="width:368px; float:left;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">extra stop (1)</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Entity</span>
													<span style="float: right;font-size: 13px;color: #000000;">Shipper</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Name </span>
													<span style="float: right;font-size: 13px;color: #000000;">MALARKEY ROOFING</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Telephone </span>
													<span style="float: right;font-size: 13px;color: #000000;">(000) 000-0000</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pickup date</span>
													<span style="float: right;font-size: 13px;color: #000000;">2017-03-06 </span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pickup Time</span>
													<span style="float: right;font-size: 13px;color: #000000;">12:00 PM to 12:00 AM  </span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Pickup Address</span>
													<span style="float: right;font-size: 13px;color: #000000;">3131 N. COLUMBIA BLVD</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> City</span>
													<span style="float: right;font-size: 13px;color: #000000;">City</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> State</span>
													<span style="float: right;font-size: 13px;color: #000000;">State</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Zip Code</span>
													<span style="float: right;font-size: 13px;color: #000000;">00000</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Country</span>
													<span style="float: right;font-size: 13px;color: #000000;">USA</span>
												</div>
											</div>
										</td>
									
										<td style="width: 368px; float: left; padding: 0px 33px;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">extra stop (2)</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Entity</span>
													<span style="float: right;font-size: 13px;color: #000000;">Shipper</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Name </span>
													<span style="float: right;font-size: 13px;color: #000000;">MALARKEY ROOFING</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Telephone </span>
													<span style="float: right;font-size: 13px;color: #000000;">(000) 000-0000</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pickup date</span>
													<span style="float: right;font-size: 13px;color: #000000;">2017-03-06 </span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pickup Time</span>
													<span style="float: right;font-size: 13px;color: #000000;">12:00 PM to 12:00 AM  </span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Pickup Address</span>
													<span style="float: right;font-size: 13px;color: #000000;">3131 N. COLUMBIA BLVD</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> City</span>
													<span style="float: right;font-size: 13px;color: #000000;">City</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> State</span>
													<span style="float: right;font-size: 13px;color: #000000;">State</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Zip Code</span>
													<span style="float: right;font-size: 13px;color: #000000;">00000</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Country</span>
													<span style="float: right;font-size: 13px;color: #000000;">USA</span>
												</div>
											</div>
										</td>
									
										<td style="width:368px; float:left;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">extra stop (3)</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Entity</span>
													<span style="float: right;font-size: 13px;color: #000000;">Shipper</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Name </span>
													<span style="float: right;font-size: 13px;color: #000000;">MALARKEY ROOFING</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Telephone </span>
													<span style="float: right;font-size: 13px;color: #000000;">(000) 000-0000</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pickup date</span>
													<span style="float: right;font-size: 13px;color: #000000;">2017-03-06 </span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pickup Time</span>
													<span style="float: right;font-size: 13px;color: #000000;">12:00 PM to 12:00 AM  </span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Pickup Address</span>
													<span style="float: right;font-size: 13px;color: #000000;">3131 N. COLUMBIA BLVD</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> City</span>
													<span style="float: right;font-size: 13px;color: #000000;">City</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> State</span>
													<span style="float: right;font-size: 13px;color: #000000;">State</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Zip Code</span>
													<span style="float: right;font-size: 13px;color: #000000;">00000</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:15px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"> Country</span>
													<span style="float: right;font-size: 13px;color: #000000;">USA</span>
												</div>
											</div>
										</td>
									
									</tr>
									<tr>
										<td style="width:100%;">
											<h2 style="font-size: 18px; text-transform: uppercase; color: rgb(54, 54, 54); margin-top: 15px;">destination info</h2>
											<table style="width:100%; float:left;font-family:arial;border:1px solid #9f9f9f;border-radius:5px;" cellspacing="0" cellpadding="0">
												<tbody>
													<tr>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Entity</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">Shipper</p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Name</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">MALARKEY ROOFING</p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Telephone</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">(000) 000-0000</p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup date</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">2017-03-06</p>
														</td>
														<td style="padding: 20px 23px;text-align: left;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup Time</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">12:00 PM to 12:00 AM</p>
														</td>
													</tr>
													<tr>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Pickup Address</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">3131 N. COLUMBIA BLVD</p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">City</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">City</p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">State</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">State</p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Zip Code</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">00000</p>
														</td>
														<td style="padding: 20px 23px;text-align: left;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Counatry</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">USA</p>
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
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">Flatbed or Step Deck</p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Equipment Options</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">FSD</p>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Load SIZE </b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">Full</p>
														</td>
														<td style="text-align: center; padding: 20px 22px;">
															<div style="display: inline-block; text-align: left; width: 51%;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">WEIGHT</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">46000</p>
															</div>
															<div style="display: inline-block; text-align: left; width: 43%;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">LENGTH</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">46000</p>
															</div>
														</td>
													</tr>
													<tr>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Commodity</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">Shingles</p>
														</td>
														<td style=" padding: 20px 23px;text-align: center;border-right: 1px solid rgb(159, 159, 159);border-top:1px solid #9f9f9f;">
															<div style=" text-align: left; width: 55%; float: left;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">pAYMENT AMOUNT</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">$3,400.00</p>
															</div>
															<div style=" text-align: left; width: 25%; float: right;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">rate</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"></p>
															</div>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<div style=" text-align: left; width: 55%; float: left;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">lOAD QUANTITY</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">1</p>
															</div>
															<div style=" text-align: left; width: 30%; float: right;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Distance</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">2273</p>
															</div>
														</td>
														<td style="text-align: left;  padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<div style="display: inline-block; text-align: left; width: 51%;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">W/O Ref. No. </b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">J2478404</p>
															</div>
															<div style="display: inline-block; text-align: left; width: 43%;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Dead Miles</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">2276</p>
															</div>
														</td>
													</tr>
													<tr>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">Commodity</b>
															<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">Shingles</p>
														</td>
														<td style=" padding: 20px 23px;text-align: center;border-right: 1px solid rgb(159, 159, 159);border-top:1px solid #9f9f9f;">
															<div style=" text-align: left; width: 55%; float: left;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">pAYMENT AMOUNT</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;">$3,400.00</p>
															</div>
															<div style=" text-align: left; width: 25%; float: right;">
																<b style="font-size:13px;width: 100%;color: #6c6c6c;text-transform: uppercase;float: left;margin-bottom: 13px;">rate</b>
																<p style="padding: 0px;width: 100%;margin: 0px 0px;float: left;text-transform: uppercase;font-size: 13px;color: #030101;"></p>
															</div>
														</td>
														<td style="text-align: left; border-right: 1px solid rgb(159, 159, 159); padding: 20px 26px;border-top:1px solid #9f9f9f;">
															
														</td>
														<td style="text-align: left;  padding: 20px 26px;border-top:1px solid #9f9f9f;">
															
														</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
									<tr>
										<td style="width:100%;">
											<h2 style="font-size: 18px; text-transform: uppercase; color: rgb(54, 54, 54); margin-top: 33px; margin-bottom: 7px;">load details</h2>
											<p style="color: rgb(54, 54, 54); font-weight: normal; font-size: 14px; line-height: 22px; margin-top: 0px; display: inline-block;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages</p>
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
