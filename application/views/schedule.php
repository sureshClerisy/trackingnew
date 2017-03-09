<!-- START CONTAINER FLUID -->
<style>
body {
  font-family: arial;
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
.top-tab-bar td { width: 20%;width:100%;}
table.top-tab-bar { border: 2px solid #000;}
table.top-tab-bar tr th { border: 1px solid #000;}
table.top-tab-bar tr td { border: 1px solid #000; text-align: center;}
.invoice-schedule {float: left; width: 100%;margin:20px 0;}
.invoice-payment-text {float: left;font-weight: bold;text-align: center; width: 50%;}
.invoice-payment-amount {float: left; text-align: right;}
.normal-text { float: left; width: 100%;}
.company-name {float: left; width: 100%;margin:20px 0;}
.company-name-heading {float: left; font-weight: bold; width: 100%;}
.signature-assignment-div {float: left; width: 100%;}
.signature-main { float: left; width: 50%;}
.signature-heading { float: left; font-weight: bold; width: 100%;}
</style>

<!-- START CONTAINER FLUID -->
<div class="container-fluid container-fixed-lg">
    <!-- START PANEL -->
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="invoice padding-50 sm-padding-10">
                <table class="top-tab-bar">
					<thead>
						<tr>
							<th>DTR_NAME</th>
							<th>INVOICE</th>
							<th>INVOICEDATE</th>
							<th>PO</th>
							<th>INV_AMT</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$length = count($loadInfo);
						for( $i  = 0; $i < $length; $i++ ) {  ?>
						
							<tr>
								<td><?php echo $loadDocs[$i][0]['doc_name']; ?></td>
								<td><?php echo $loadInfo[$i]['invoiceNo']; ?></td>
								<td><?php echo $loadInfo[$i]['invoicedDate']; ?></td>
								<td><?php echo $loadDocs[$i][1]['doc_name']; ?></td>
								<td><?php echo $loadInfo[$i]['PaymentAmount']; ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				
				<br>
                               
				<div class="invoice-schedule" style="text-align:center;width:100%;">
					
					
					<div class="invoice-payment-text" style="padding-left:20%;">Invoice the schedule</div>
					<div class="invoice-payment-amount" style="float:right;"> $<?php echo $totalPaymentAmount; ?></div>
				</div>
			
                <div class="normal-text">
					<?php echo $content; ?>
                </div>
              
				<div class="company-name">
					<div class="company-name-heading">Company Name</div>
					<div class="company-name-value">Vika Logistics Corp c/o Triumph Buisness Capital LLC</div>
				</div>
				
				<div class="signature-assignment-div">
					<div class="signature-main">
						<div class="signature-heading">Company Name</div>
						<div class="signature-heading"><img src="./assets/img/invoice/signature2x.png" alt="" style="width:200px"></div>
					</div>
					
					<div class="signature-main" style="float:right;width:150px;" >
						<div class="signature-heading">Date of Assignment</div>
						<div class="assignment-value"><?php echo date('Y-m-d'); ?></div>
					</div>
				</div>
                
                
            </div>
        </div>
    </div>
    <!-- END PANEL -->
</div>
<!-- END CONTAINER FLUID -->
