<!-- START CONTAINER FLUID -->
<style>
body{font-family:arial}
p,div,i,span,h1,h2,h3,h4,h5,h6,b,table,td,th{padding:0;margin:0}
.header{background:#ebebeb none repeat scroll 0 0;border-radius:3px;float:left;margin-bottom:20px;padding:15px 2%;width:96%}
.top-head{float:left;margin-bottom:19px;text-align:center;width:100%}
.top-head h2{color:#077ed0;font-size:20px;margin:0;padding:0}
.top-head > p{color:#626262;font-size:14px;margin:0;padding-top:3px}
.after-head{float:left;width:100%}
.svehicle{margin-right:15px}
.uppder-cont{float:left;width:100%}
.invoice{float:left;width:100%}
table.top-tab-bar{border:1px solid #dedede;float:left;margin:0;padding:0;width:100%}
table.top-tab-bar th{background:#077ed0 none repeat scroll 0 0;color:#fff;text-transform:uppercase}
.lth{width:345px}
table.top-tab-bar th,table.top-tab-bar td{font-size:12px;padding:13px 10px;text-align:left}
table.top-tab-bar td.text-center,table.top-tab-bar th.text-center{text-align:center}
.vth{width:100px}
table.top-tab-bar tr:nth-child(2n){background:#f1f1f1 none repeat scroll 0 0}
table.ttable{width:100%;float:left}
table.ttable tr td.slabel{float:left;font-weight:700;font-size:12px}
table.ttable tr td.stext{color:#626464;float:left;width:80%;font-size:12px}
table.theadtable tr td{text-align: center;}
table.theadtable {width: 100%; float: left;}
table.theadtable tr td.h2{color:#077ed0;font-size:20px;margin:0;padding:0; font-weight: bold;}
table.theadtable tr td.p{color:#626262;font-size:12px;margin:0;padding-top:3px}
table.ttable tr td {
    padding: 5px 0;
}
.ttable td:nth-child(2n) {
    padding-right: 10px;
}
</style>

<!-- START CONTAINER FLUID -->
<div class="container-fluid container-fixed-lg">
    <!-- START PANEL -->
    <div class="panel panel-default">
        <div class="panel-body">
        	<div class="uppder-cont">
        		<div class="header">
	        		<div class="top-head">
	        			<table class="theadtable">
	        				<?php $phead = $_GET['phead']; ?>
	        				<tr><td class="h2"><?php echo strtoupper($report['title']); ?></td></tr>
	        				<tr><td class="p"><?php echo $report['context']; ?></td></tr>
	        			</table>
	        			
	        			
	        		</div>
	        		<div class="after-head">
	        		<table class="ttable">
	        			<tr>
	        				<td class="slabel"><?php echo $phead['daterange']; ?></td>
	        				<td>:</td>
	        				<td class="stext">
	        				<?php 
	        					if ( isset($args['customDate']) && $args['customDate'] != '') {
	        						echo date('M-d-Y', strtotime($args['customDate'])).' 12:00 AM <i>to</i> '.date('M-d-Y', strtotime($args['customDate'])).' 11:59 PM EST';
	        					} else if ( isset($args['startDate']) && $args['startDate'] != '' ) {
									echo date('M-d-Y', strtotime($args['startDate'])). ' 12:00 AM <i>to</i> '.date('M-d-Y', strtotime($args['endDate'])).' 11:59 PM EST';	
								}
	        				?>
	        				</td>
	        			</tr>
	        			<tr>
	        				<td class="slabel"><?php echo $phead['scope']; ?></td>
	        				<td>:</td>
	        				<td class="stext">
	        					<?php 
			        				if (isset($args['deviceID']) && count($args['deviceID']) > 0) {
			        					foreach ($args['deviceID'] as $key => $value) {
			        						echo '<span class="svehicle"> Vehicle - '.$value.", </span>";
			        					}
			        				}else{
			        						echo '<span class="svehicle">All Vehicles</span>';
			        				}
			        			?>
		        			</td>
	        			</tr>
	        			<tr>
	        				<td class="slabel"><?php echo $phead['generatedby']; ?></td>
	        				<td>:</td>
	        				<td class="stext"><?php echo $byuser; ?></td>
	        			</tr>
	        			
	        			<tr>
	        				<td class="slabel"><?php echo $phead['filteronstatus']; ?></td>
	        				<td>:</td>
	        				<td class="stext">
	        					<?php 
		        					$string = '';
		        					if (isset($eventType) && !empty($eventType) && count($eventType) > 0 && is_array($eventType) ) {
			        					foreach ($eventType as $key => $value) {
			        						$string.= $value['label'].", ";
			        					}
			        				}else{
			        					$string =  'IDLE, SPEED, TRAVEL, IGOFF, PTO_OFF, PTO_ON';
			        				}

			        				echo rtrim($string, ",");
		        				?>
	        				</td>
	        			</tr>
	        			<tr>
	        				<td class="slabel"><?php echo $phead['timezone']; ?></td>
	        				<td>:</td>
	        				<td class="stext">Eastern</td>
	        			</tr>
	        		</table>
	        	</div>
        	</div>
            <div class="invoice padding-50 sm-padding-10">
                <table class="top-tab-bar" cellpadding="0" cellspacing="0">
					<thead>
						<tr>
	                      <?php foreach ($column_mappings as $key => $value) { ?>                      		
	                      <?php if( (!isset($args['includeLatLong']) || !$args['includeLatLong'] || $args['includeLatLong']== "false" ) && ($value == 'Latitude' || $value == 'Longitude')){
	                      		 	continue; 
	                      		} ?>

	                      		<?php
	                      			$class = "";
	                      			switch ($value) {
	                      				case 'Vehicle ID': $class = "vth text-center" ; break;
	                      				case 'Location'  : $class = "lth" ; break;
	                      				case 'Truck No'	 		:	 
	                      				case 'Speed Limit (MPH)':	 
	                      				case 'Latitude'	 		:	 
	                      				case 'Longitude'	 	:	 
	                      				case 'Odometer(Mi)'	 	:  $class = "text-center" ; break;
	                      			}

	                      		?>


	                      		<th class="<?php echo $class; ?>" ><?php echo $value; ?></th>
	                      <?php	} ?>
	                    </tr>
					</thead>
					<tbody>
						<?php 
							if(is_array($result) && count($result) > 0){
								foreach ($result as $key => $value) { ?>
								
								<tr>
									<td class="vth text-center"><?php echo $value['deviceID']; ?></td>
									<td class="text-center"><?php echo $value['label']; ?></td>
									<td><?php echo $value['driverName']; ?></td>
									<td><?php echo $value['GMTTime']; ?></td>
									<td><?php echo $value['eventType']." "; 
										echo strtolower($value['eventType']) == 'moving' ? $value['hdirection'].':'.$value['vehicleSpeed'].'mph' : '';
										?>
									</td>
									<?php if($args['includeLatLong'] == "true") { ?>
										<td class="text-center"><?php echo $value['latitude']; ?></td>
										<td class="text-center"><?php echo $value['longitude']; ?></td>
									<?php } ?>
									<td class="lth"><?php echo $value['location']; ?></td>
									<td class="text-center"><?php echo $value['vehicleSpeed']; ?></td>
									<td class="text-center"><?php echo $value['odometer']; ?></td>
									
								</tr>
							<?php } 
							}?>
					</tbody>
				</table>
            </div>
        </div>
    </div>
    <!-- END PANEL -->
</div>
<!-- END CONTAINER FLUID -->
