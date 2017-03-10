<html>
<head>
<title> </title>
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
							<h1 style="font-size:30px; color:#1f1f1f;padding:0px; margin:0px;">TRIP DETAILS</h1>
						</td>
						<td style="width:300px; ">
							<div style="width:300px; float:right;">
								<div style="float: left;width: 78px;font-size: 13px;">
									<b style="width: 100%;text-transform: uppercase;float: left;font-size: 13px;color: #868686;font-weight: bold;margin-bottom: 10px;">Load ID</b><?php echo $loadID;?>
								</div>
								<div style="float: left;width: 102px;font-size: 13px;">
									<b style="width: 100%;text-transform: uppercase;float: left;font-size: 13px;color: #868686;font-weight: bold;margin-bottom: 10px;">Invoice No</b><?php echo ($invoceNo)?$invoceNo:'NA';?>
								</div>
								<a href="#" style="width: 111px;float: right;border: 1px solid #6c6c6c;text-align: center;border-radius: 3px;text-decoration: none;font-size: 14px;color: #6c6c6c;padding: 11px 0px;"><?php echo $JobStatus;?></a>
							</div>
						</td>
					</tr>
				</table>
		
				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;font-family:arial;">
					<tr>
						<td>
							<table style="width:100%; float:left;padding:30px 0px 44px;font-family:arial;" cellspacing="0" cellpadding="0">
								<tbody>
									<tr>
										<td style="width:565px; float:left;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">DIESEL CALCULATIONS</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Vehicle/Avg</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $tripDetails[0]['vehicle_average'];?>															
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Diesel Needed </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $tripDetails[0]['diesel_needed'];?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Avg cost of Diesel  </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo '$'.$tripDetails[0]['avg_cost_diesel'];?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;"></span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<b style="font-weight: bold; font-size: 15px;">
														<?php 
															echo $total = '$'.($tripDetails[0]['diesel_needed']*$tripDetails[0]['avg_cost_diesel']);
														?>
														</b>
													</span>
												</div>
											
											</div>
											<div style="width: 100%; float: left; margin-top: 21px;">
												<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">Toll and taxes</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">IFTA Taxes</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo '$'.$tripDetails[0]['ifta_taxes'];?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Tarps </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo '$'.$tripDetails[0]['tarps'];?>
															
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Detention Time </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo '$'.$tripDetails[0]['detention_time'];?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Tolls</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo '$'.$tripDetails[0]['tolls'];?> 
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #000;font-family: arial;font-weight: bold;font-size: 13px;float: left;">
														<b style="font-weight: bold; font-size: 14px;">CHARGES</b></span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<b style="font-weight: bold; font-size: 15px;">
															<?php 
															echo '$'.$total = ($tripDetails[0]['ifta_taxes']+$tripDetails[0]['detention_time']+$tripDetails[0]['tarps']+$tripDetails[0]['tolls'])
															?>
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
														<?php echo $tripDetails[0]['origin_to_dest'];?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Dead Miles to Destination </span>
													<span style="float: right;font-size: 13px;color: #000000;"><?php echo $tripDetails[0]['deadmiles_dist'];?></span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Total Miles </span>
													<span style="float: right;font-size: 13px;color: #000000;">
													<?php echo $total = ($tripDetails[0]['origin_to_dest']+$tripDetails[0]['deadmiles_dist'])?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Dead miles not Paid</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $total = $tripDetails[0]['dead_miles_not_paid'];?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Dead head miles paid</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $total = $tripDetails[0]['dead_head_miles_paid'];?> 
														</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pay for dead head Miles</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $total = $tripDetails[0]['pay_for_dead_head_mile'];?> 
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Dead Miles Paid***</span>
													<span style="float: right;font-size: 13px;color: #000000;">$0.00 </span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Extra Stop charges</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo '$'.$extra_stop_charges;?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Pay for milesw/cargo</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $total = $tripDetails[0]['pay_for_miles_cargo'];?>  
													</span>
												</div>
												
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Amount Paid With Cargo</span>
													<span style="float: right;font-size: 13px;color: #000000;">
													<?php 
														$totalAmmout = $tripDetails[0]['origin_to_dest']+$tripDetails[0]['deadmiles_dist'];
														echo '$'.$total = ($totalAmmout*$tripDetails[0]['pay_for_dead_head_mile']);?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #000;font-family: arial;font-weight: bold;float: left;"><b style="font-weight: bold; font-size: 14px;">Due to Driver</b></span>
													<span style="float: right;color: #000000;">
														<b style="font-weight: bold; font-size: 15px;">
															<?php echo '$'.$dueToDriver = $total+$extra_stop_charges;?>
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
