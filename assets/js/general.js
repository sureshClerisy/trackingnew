jQuery(document).ready(function () {
    window.baseurl = jQuery('.baseUrl').attr('id');
    
    jQuery('a.save').on('click', function (e) {
        e.preventDefault()
        var siteType = $(this).attr('id');
        var val = Array();
        $(this).closest('tr').children('td').each(function () {
            val.push($(this).text());
        });
        var record = $(this);

        jQuery.ajax({
            url: baseurl+'admin/jobs/storeData',
            type: 'POST',
            data: {'scrapData': val,'siteType': siteType},
            success: function (response) {
                
                $('body, html').animate({ scrollTop: 0 }, 800);
                $('.load-save-msg').html('');
                $('.load-save-msg').show();
                
                if (response == 1) {
                   $('.load-save-msg').css('color', 'green');
                   $('.load-save-msg').html('<span>Job has been saved successfully.</span>');
               } else if (response == 'record_exist') {
                   $('.load-save-msg').css('color', 'red');
                   $('.load-save-msg').html('<span>This Job has already been saved.</span>');
               } else {
                   $('.load-save-msg').css('color', 'red');
                   $('.load-save-msg').html('<span>Job could not be saved. Please try again.</span>');
               } 
               record.parent().parent("tr:first").remove();
                //~ //setTimeout( function(){$('.load-save-msg').hide();} , 4000);
            }
        });
    });

    jQuery('a.save-data').on('click', function (e) {
        e.preventDefault()
        var siteType = $(this).attr('id');
        var val = Array();
        $(this).closest('tr').children('td').each(function () {
            val.push($(this).text());
        });
        var record = $(this);

        jQuery.ajax({
            url: baseurl+'admin/jobs/saveJobData',
            type: 'POST',
            data: {'scrapData': val,'siteType': siteType},
            success: function (response) {
                
                $('body, html').animate({ scrollTop: 0 }, 800);
                $('.load-save-msg').html('');
                $('.load-save-msg').show();
                
                if (response == 'true') {
                   $('.load-save-msg').css('color', 'green');
                   $('.load-save-msg').html('<span>Job has been saved successfully.</span>');
               }  else {
                   $('.load-save-msg').css('color', 'red');
                   $('.load-save-msg').html('<span>Job could not be saved. Please try again.</span>');
               } 
               record.parent().parent("tr:first").remove();
                //~ //setTimeout( function(){$('.load-save-msg').hide();} , 4000);
            }
        });
    });

    jQuery('a.save-trackingplanet').on('click', function (e) {
        e.preventDefault()
        var siteType = $(this).attr('id');
        var val = Array();
        $(this).closest('tr').children('td').each(function () {
            val.push($(this).text());
        });
        var record = $(this);

        jQuery.ajax({
            url: baseurl+'admin/jobs/saveTrackingPlanet',
            type: 'POST',
            data: {'scrapData': val,'siteType': siteType},
            success: function (response) {
				
                $('body, html').animate({ scrollTop: 0 }, 800);
                $('.load-save-msg').html('');
                $('.load-save-msg').show();
                
                if (response == 'true') {
                   $('.load-save-msg').css('color', 'green');
                   $('.load-save-msg').html('<span>Job has been saved successfully.</span>');
               }  else {
                   $('.load-save-msg').css('color', 'red');
                   $('.load-save-msg').html('<span>Job could not be saved. Please try again.</span>');
               } 
               record.parent().parent("tr:first").remove();
               //~ //setTimeout( function(){$('.load-save-msg').hide();} , 4000);
            }
        });
    });



    $("#slide-btn").click(function(){
        $(".logo-lg-top").toggle();
    });

    $('.cancel-vehicle').on('click',function(){
        var controler = $(this).attr('id');
        window.location.href = baseurl+'admin/'+controler;
    });
});

$(document).ready(function() {
    $('#checkAll').click(function(event) {   //on click
        if(this.checked) { // check select status
            $('.checkbox').each(function() { //loop through each checkbox
                $('.save-checkbox').show(1000);
                this.checked = true;  //select all checkboxes with class "checkbox1"              
            });
        }else{
            $('.checkbox').each(function() { //loop through each checkbox
                $('.save-checkbox').hide(1000);
                this.checked = false; //deselect all checkboxes with class "checkbox1"                      
            });        
        }
    }); 

});

$(document).on('change','#jobs-table input:checkbox',function () {
    if($('#jobs-table input:checkbox:checked').length > 1) {
        $('.save-checkbox').show(1000);
    }
    else {
     $('.save-checkbox').hide(1000);
 }
});


$(document).on('click', '#save-selected-jobs', function () {
	var multiVal = Array();
	var site_type = $('#jobs-table tbody tr:first td:last a').attr('id');

	$("input:checkbox[name=checkboxRow]:checked").each(function () {
        if ( site_type == 'loadup' || site_type == 'quicktransportsolutions') {
			 multiVal.push($(this).val());
			
		} else {
			multiVal.push($(this).next('div').text());
		}

    });


  $.ajax({
    url: baseurl + 'admin/jobs/storeMultiRows',
    type: 'POST',
    data: {'scrapData': multiVal ,siteType : site_type},
    success: function (response) {

        $('body, html').animate({scrollTop: 0}, 800);
        $('.load-save-msg').html('');
        $('.load-save-msg').show();

           
			$("input:checkbox[name=checkboxRow]:checked").each(function () {
                $(this).closest('tr').remove();
            });
			$('.load-save-msg').html('<span>'+response+'</span>');
		//	window.location = baseurl + 'admin/jobs/saved';
        //setTimeout( function(){window.location = baseurl + 'admin/jobs/saved';} , 5000);          
            //setTimeout(function () {$('.load-save-msg').hide();}, 4000);
        }
    });
});

$(function () {
	if ( $('.disable-select').val() != '' ) {
		$('.disable-select').prop('disabled', false);
	} else {
		$('.disable-select').prop('disabled', true);
	}
});

$(document).on('change','#scrap_url',function(){
	
    var select_val = $(this).val();
	
	if ( select_val != '' && select_val != 'http://www.dssln.com/loads.php') {
		$('.disable-select').prop('disabled', false);
	} else {
		$('.disable-select').prop('disabled', true);
	}

    if(select_val == 'http://www.quicktransportsolutions.com'){
        // alert('///');
        $('.disable-select').prop('disabled', true);
        $('.disable-select:last').prop('disabled', false);
        jQuery('#fetch-job').attr('action',baseurl+'admin/jobs/getData');
    }

});




