<html>
<head>
<title></title>
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
						<h1 style="font-size:30px; color:#1f1f1f;padding:0px; margin:0px;">BROKER INFORMATION</h1></td>
						<td style="width:300px; ">
							<div style="width:300px; float:right;">
								<div style="float: left;width: 78px;font-size: 13px;">
									<b style="width: 100%;text-transform: uppercase;float: left;font-size: 13px;color: #868686;font-weight: bold;margin-bottom: 10px;">Load ID</b><?php echo $loadID;?>
								</div>
								<div style="float: left;width: 102px;font-size: 13px;">
									<b style="width: 100%;text-transform: uppercase;float: left;font-size: 13px;color: #868686;font-weight: bold;margin-bottom: 10px;">Invoice No</b><?php echo ($invoceNo)?$invoceNo:'NA';?>
								</div>
								<a href="#" style="width: 111px;float: right;border: 1px solid #6c6c6c;text-align: center;border-radius: 3px;text-decoration: none;font-size: 14px;color: #6c6c6c;padding: 11px 0px;">
									<?php echo ($JobStatus)?ucfirst($JobStatus):'NA';?>
								</a>
							</div>
						</td>
					</tr>
				</table>
		
				<table cellpadding="0" cellspacing="0" style="width:100%; float:left;font-family:arial;">
					<tr>
						<td>
							<h1 style="color: rgb(54, 54, 54); text-transform: uppercase; font-size: 25px; margin: 35px 0 30px;">
							<?php echo $brokerLoadDetail['TruckCompanyName'];?>
							<span style="font-size: 13px;padding-left: 8px;position: relative;top: -5px;color: #363636;text-transform: none;">(<?php echo $brokerLoadDetail['brokerStatus'];?>)</span></h1>
						</td>
					</tr>
					<tr>
						<td>
							<table style="width:100%; float:left;padding:0 0 44px;font-family:arial;" cellspacing="0" cellpadding="0">
								<tbody>
									<tr>
										<td style="width:368px; float:left;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">AUTHORITY</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Broker MC
													</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $brokerLoadDetail['MCNumber']?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Carrier MC </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $brokerLoadDetail['DOTNumber'];?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">US Dot  </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $brokerLoadDetail['DOTNumber'];?>
													</span>
												</div>											
											</div>
										</td>
									
										<td style="width: 368px; float: left; padding: 0px 33px;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">CONTACT INFORMATION</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Handle</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo ($brokerLoadDetail['PointOfContact'])?$brokerLoadDetail['PointOfContact']:'NA';?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Contact </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo ($brokerLoadDetail['PointOfContactPhone'])?$brokerLoadDetail['PointOfContactPhone']:'NA';?></span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Email </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo ($brokerLoadDetail['TruckCompanyEmail'])?$brokerLoadDetail['TruckCompanyEmail']:'NA';?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Office</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo ($brokerLoadDetail['TruckCompanyPhone'])?$brokerLoadDetail['TruckCompanyPhone']:'NA';?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Fax</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo ($brokerLoadDetail['TruckCompanyFax'])?$brokerLoadDetail['TruckCompanyFax']:'NA';?>
													</span>
												</div>
											</div>
										</td>
									
										<td style="width:368px; float:left;">
											<div>
												<h1 style="font-size: 18px;margin: 0px;color: #363636;text-transform: uppercase;border-bottom: 1px solid #b6b6b6;padding-bottom: 10px;margin-bottom: 15px;">ADDRESS</h1>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Street Address</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $brokerLoadDetail['postingAddress'];?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">City </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $brokerLoadDetail['city'];?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">State </span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $brokerLoadDetail['state'];?>
													</span>
												</div>
												<div style="width: 100%;float: left;padding-bottom:13px;">
													<span style="text-transform: uppercase;color: #6c6c6c;font-family: arial;font-weight: bold;font-size: 13px;float: left;">Zip Code
													</span>
													<span style="float: right;font-size: 13px;color: #000000;">
														<?php echo $brokerLoadDetail['zipcode'];?> 
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