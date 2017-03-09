$(document).ready(function(){

	$("#truck-loads").validate({
		rules:{
			// origin_city:{required:true},
			//origin_state:{required:true},
			//destination_city:{required:true},
			//destination_state:{required:true},
		},
		message:{
			// origin_city:{required:'Please enter city.'},
			//origin_state:{required:'Please enter city.'},
			//destination_city:{required:'Please enter city.'},
			//destination_state:{required:'Please enter city.'},
		},
		errorElement:"span",
		errorClass:"login-error",
	});

	//======================================
	jQuery('.more-option').on('click',function(){
		jQuery('.more-options-section').slideToggle();
	});
	//======================================

	$( "#PickupDate").datepicker({'dateFormat':'yy-mm-d'});

	//
	jQuery('.vehicle-list').on('change',function(){
		
		eltVal = $(this).val();
		
		if(eltVal == 'all'){
			$('div.job-list').fadeIn();
		}else{

			$('div.job-list').fadeOut();

			$('#'+eltVal).fadeIn('slow');
		}
	});

	$('.vehicle-address div').each(function(index){
		
		if(index>0){

			element = $(this);
			elID = element.attr('id');
			elDate = element.attr('text');
			elDestDate = element.attr('value');
			
			var load_search = element.attr('id').split('-');
						
			if (elDate != '' && elDate != '0000-00-00') {
				var newurl = baseurl+'admin/truckstop/get_load/'+elDate;
			} else {
				var newurl = baseurl+'admin/truckstop/get_load';
			}
			
			$.ajax({
				url: newurl,
				type: 'get',
				beforeSend: function() {
				//$('.loader-class').show();
			},
			data: {'state':load_search['0'] ,'vtype':load_search['1'],'id':elID, 'destDate' : elDestDate},
			success: function (response) {

				data = jQuery.parseJSON(response);
				$('#'+data.element).append(data.row);			
			}
		});
		}
		
	});
});

function fetch_load_detail( jobId ) {
	
	$.ajax({
		url: baseurl+'admin/truckstop/matchLoadDetail/'+jobId,
		type: 'Get',
		beforeSend: function() {
			$('.loader-class').show();
		},
		//data: {'scrapData': val,'siteType': siteType},
		success: function (response) {
			$('.ajax-content').show();
			$('.show-load-result').html('');
			$('.show-load-result').html(response);
			$('.loader-class').hide();
		}
	});		
}

jQuery('#deadmiles').on('change',function(){
		
		var thisEl = jQuery(this).val();
		
		var targetEl = jQuery('.vehicle-list').val();

		$('#'+targetEl+' table tr').hide();

		if(thisEl != 'all'){

			$('body #'+targetEl+' table tbody tr').each(function(){
				
				console.log($(this).attr('class') +'>='+ thisEl);
				
				if($(this).attr('class')){
					
					val1 = parseInt($(this).attr('class'));
					val2 = parseInt(thisEl);
					
					if(val1 >= val2){
						$(this).hide();
					}else{
						$(this).show();
					}
					
				}else{
					$(this).hide();				
				}
				/*if($(this).attr('class') >= thisEl || $(this).attr('class') ==''){
					$(this).hide();
				}else{
					$(this).show();
				}*/
			});
		}else{
			$('#'+targetEl+' table tr').each(function(){

				$(this).show();
				
			});
		}		
	});
