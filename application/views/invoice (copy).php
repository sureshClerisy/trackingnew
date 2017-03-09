<!-- START CONTAINER FLUID -->
<style>
body {
  font-family: arial;
}
.new-in-css {
  float: left;
  width: 50%;
  border-right: 1px solid #000;padding:4px 
}
.new-rig-css {
  float: left;
}
.full-widht{float: left; width: 100%; box-sizing: border-box; }
.full-widht:last-child {
  border-bottom: 0 none;
}
.container-sm-table tr td {
  width: 49.8%;
  float: left;
   padding: 15px;
}
.container-sm-table tbody tr {
  
  float: left;
  width: 100%;
}
table.m-t-50,.container-sm-table  {
  float: left;
  width: 100%;
}
.load-head {
  background: #eeeeee none repeat scroll 0 0;
  padding: 9px;
  text-align: left;
  width: 100%;
  
}

.text-black {
  float: left;width:auto
}

b {
  float: left;width:300px;margin-right:12px
}
.full-td{
  float: left;
  width: 100%;
}
.table thead tr th {
  border-bottom: 1px solid rgba(230, 230, 230, 0.698);
  color: rgba(44, 44, 44, 0.35);
  font-size: 13px;
  font-weight: bold;
  padding-bottom: 14px;
  padding-top: 14px;
  text-transform: uppercase;
  vertical-align: middle;
  padding-left: 20px !important;
  padding-right: 20px !important;
}
.table tbody tr td {
  background: #fff none repeat scroll 0 0;
  border-bottom: 1px solid rgba(230, 230, 230, 0.698);
  border-top: 0 none;
  font-size: 13px;
  padding: 20px;
}

.text-center {
  text-align: center !important;
}
.text-right {
  text-align: right !important;
}

address{
	font-size: 13px;
	color: #626262;
}
.container-sm-table tr td{
 	width:50%;
}
.bg-menu {
  background-color: #2b303b;
}
.text-white {
    color: #fff !important;
}
.text-white {
    color: #fff !important;
}
.hint-text {
    opacity: 0.7;
}
.all-caps {
  text-transform: uppercase;
}
.pull-right{
	float:right
}
.pull-left{
	float:left
}
.top-lg-main {
  width: 100%;
  float: left;
}
.top-lg-main td {
 padding:10px;
  vertical-align:top
}
.top-tab-bar td {
  width: 33%;
}
.top-lg-main td  table td{
  width: 50%;padding:auto;
}
</style>


<!-- START CONTAINER FLUID -->
<div class="container-fluid container-fixed-lg">
    <!-- START PANEL -->
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="invoice padding-50 sm-padding-10">
                <table class="top-tab-bar" >
					<tbody>
						<tr>
                    <td>
                        <h5><img width="235" height="47" alt="" class="invoice-logo" ui-jq="unveil" data-src-retina="assets/img/logo.png" data-src="assets/img/logo.png" src="assets/img/logo.png"><br><br>
                        Vika Logistics Corp <br>c/o Triumph Buisness Capital LLC</h5><br>
					<address style="font-style: normal; font-family: arial; font-weight: normal;">
						<strong style="font-weight: normal;">P.O BOX 610028 </strong>
						<br> Dallas, TX 75261-0028 USA<br>
						 Tel: 866-368-2482
					</address>
				</td>
				<td><img width="350px" height="auto" alt="" class="invoice-logo" ui-jq="unveil" src="assets/img/stamp.png" ></td>
				<td style="vertical-align:top;text-align:right;">
				<h2 class="all-caps">Invoice</h2>
				</td>
				</tr>
				</tbody>
				</table>
			</div>
              
                <table class="top-lg-main" >
                    <tbody>
						<tr>
							<td>
								<p class="small no-margin">Invoice to</p>
								<h5 class="semi-bold m-t-0" style="float: left; margin: 0px;"> <?php echo $jobRecord["TruckCompanyName"]; //$this->config->item('transportComp_name'); ?></h5>
								<address  style="font-style: normal; font-family: arial; font-weight: normal; float: left; margin-top: 0px;">
									TEL: <?php echo $jobRecord["TruckCompanyPhone"]; //$this->config->item('transportComp_phoneNo'); ?>
									<br>Address: <?php echo $jobRecord["postingAddress"].', '.$jobRecord["city"].', '.$jobRecord["state"].', '.$jobRecord["zipcode"]; ?>
									<br>FAX: <?php if($jobRecord["TruckCompanyFax"] !=0 ) echo $jobRecord["TruckCompanyFax"]; ?>
									<br>Broker MC: <?php if($jobRecord["MCNumber"] !=0 ) echo $jobRecord["MCNumber"]; ?>
									<br>Carrier MC: <?php if($jobRecord["CarrierMC"] !=0 ) echo $jobRecord["CarrierMC"]; ?>
									<br>Email: <?php echo $jobRecord["TruckCompanyEmail"]; ?>
									<br>Handle: <?php echo $jobRecord["PointOfContact"]; ?>
									<br>DOT Number: <?php if($jobRecord["DOTNumber"] !=0 ) echo $jobRecord["DOTNumber"]; ?>
									
								</address>
							</td>
							<td>
								&nbsp;&nbsp;
							<!--img width="250px" height="auto" alt="" class="invoice-logo" ui-jq="unveil" src="http://localhost/trackingnew/assets/img/stamp.png"-->
							</td>
							<td>
								
								<table>
									<tbody>
										<tr>
											<td><b>Invoice No :</b></td>
											<td style="text-align:right;"><?php echo $jobRecord['invoiceNo']; ?></td>
										</tr>
										<tr>
											<td><b>Invoice Date :</b></td>
											<td style="text-align:right;"><?php echo $jobRecord['invoicedDate']; ?></td>
										</tr>
										<tr>
											<td><b>Load# :</b></td>
											<td style="text-align:right;"><?php echo $jobRecord['id']; ?></td>
										</tr>
										<tr>
											<td><b>Ship Date</b></td>
											<td style="text-align:right;"><?php echo $jobRecord['PickupDate']; ?></td>
										</tr>
										<tr>
											<td><b>Delivery Date :</b></td>
											<td style="text-align:right;"><?php echo (isset($jobRecord['DeliveryDate']) && $jobRecord['DeliveryDate'] != '' && $jobRecord['DeliveryDate'] != '0000-00-00') ? $jobRecord['DeliveryDate'] : ''; ?></td>
										</tr>
										<tr>
											<td><b>W/O # Ref No. :</b></td>
											<td style="text-align:right;"><?php echo $jobRecord['woRefno']; ?></td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
                    </tbody>
                </table>
                <br>
                <br>
                <div class="table-responsive">
                    <table class="table m-t-50">
                        <thead>
                            <tr>
                                <th class="" style="text-align:left;">Load Details</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">PickupDate</th>
                                <th class="text-right">Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="">
									<p class="text-black"><b>Shipper:</b> <?php echo $jobRecord['shipper_name']; ?></p>
									<p class="small hint-text"><b>Type:</b> <?php echo $jobRecord['LoadType']; ?></p>
									<p class="small hint-text"><b>Address:</b> <?php echo $jobRecord['PickupAddress']; ?> </p>
									<p class="small hint-text"><b>Phone:</b> <?php echo $jobRecord['shipper_phone']; ?></p>
								</td>
								
                                <td class="text-center"><?php echo $jobRecord['Quantity']; ?></td>
                                <td class="text-center"><?php echo $jobRecord['PickupDate']; ?></td>
                                <td class="text-right"><?php echo $jobRecord['Weight']; ?></td>
                            </tr>
                            <tr>
								<td class="">
									<p class="text-black"><b>Consignee:</b> <?php echo $jobRecord['consignee_name']; ?></p>
									<p class="small hint-text"><b>Type:</b> <?php echo $jobRecord['LoadType']; ?></p>
									<p class="small hint-text"><b>Address:</b> <?php echo $jobRecord['DestinationAddress']; ?></p> 
									<p class="small hint-text"><b>Phone:</b> <?php echo $jobRecord['consignee_phone']; ?></p> 
								</td>
								<td class="text-center"><?php echo $jobRecord['Quantity']; ?></td>
                                <td class="text-center"><?php echo (isset($jobRecord['DeliveryDate']) && $jobRecord['DeliveryDate'] != '' && $jobRecord['DeliveryDate'] != '0000-00-00' ) ? $jobRecord['DeliveryDate'] : ''; ?></td>
                                <td class="text-right"><?php echo $jobRecord['Weight']; ?></td>
                            </tr>
                           
                        </tbody>
                    </table>
                </div>
               <br>
               <br>
               
                <table class="container-sm-table">
                    <tbody>
						<tr>
                        <td style="border: 1px solid #e6e6e6;">
                            <h5 class="all-caps small no-margin hint-text bold">Invoice Amount</h5>
                      
                        </td>
                        <td class="bg-menu  text-right">
                            <h5 class="all-caps small no-margin hint-text text-white bold">Total</h5>
                            <h1 class="text-white"><span style="float: right; text-align: right;width:100%;">$<?php echo number_format($jobRecord['PaymentAmount'],2); ?></span> </h1>
                        </td>
                    </tr>
                </table>
                
            </div>
        </div>
    </div>
    <!-- END PANEL -->
</div>
<!-- END CONTAINER FLUID -->
