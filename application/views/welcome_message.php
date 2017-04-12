<html lang="en" ng-app="app" >
<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta content="" name="description" />
    <meta content="" name="author" />
    <title ng-bind = "pageTitle">Vika Logistics</title>
    <link rel="apple-touch-icon" href="pages/ico/60.png">
    <link rel="apple-touch-icon" sizes="76x76" href="pages/ico/76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="pages/ico/120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="pages/ico/152.png">
    <link rel="icon" type="image/x-icon" href="pages/ico/favicon.ico" />
    
   
    <?php 
        createScriptOrLinkTag('pages/css/css.css');
        createScriptOrLinkTag('pages/css/css-table.css');
    ?>

    <link href="pages/css/css-table.css" rel="stylesheet" type="text/css">
    <link href="assets/plugins/pace/pace-theme-flash.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/bootstrapv3/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/jquery-scrollbar/jquery.scrollbar.css" rel="stylesheet" type="text/css" media="screen" />
    <link href="pages/css/pages-icons.css" rel="stylesheet" type="text/css">
    <?php createScriptOrLinkTag('pages/css/pages.css');?>

    <link href="assets/plugins/dropzone/css/dropzone.css" class="main-stylesheet" rel="stylesheet" type="text/css" />  <!-- dropzone css -->
    <link href="assets/plugins/angular-gantt/angular-gantt/assets/angular-gantt.css" rel="stylesheet" type="text/css" media="screen">
    <link href="assets/plugins/angular-gantt/angular-gantt/assets/angular-gantt-plugins.css" rel="stylesheet" type="text/css" media="screen">
    <link href="assets/plugins/angular-gantt/angular-ui-tree/dist/angular-ui-tree.css" rel="stylesheet" type="text/css" media="screen">
    <link href="assets/plugins/angular-gantt/angular-gantt/src/plugins/tooltips/tooltips.css" rel="stylesheet" type="text/css" media="screen">
    <link href="assets/plugins/bootstrap-timepicker/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/bootstrap-select2/select2.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/angular-ui-select/select.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/bootstrap-datepicker/css/datepicker3.css" rel="stylesheet" type="text/css" />
    <link type="text/css" rel="stylesheet" href="assets/plugins/jquery-datatable/media/css/jquery.dataTables.css">
    <link type="text/css" rel="stylesheet" href="assets/plugins/jquery-datatable/extensions/fixedheader/css/fixedHeader.dataTables.min.css">
    <link type="text/css" rel="stylesheet" href="assets/plugins/jquery-datatable/extensions/RowReorder/css/rowReorder.dataTables.min.css">
    <link type="text/css" rel="stylesheet" href="assets/plugins/jquery-datatable/extensions/Responsive/css/responsive.dataTables.min.css">    
   <!-- BEGIN VENDOR JS -->


</head>
<body class="fixed-header" ng-class="showBackground == true ? 'login-bg-head' : ''" ng-controller="mainController as mctrl">
   
    <div ng-if="showHeader == true">
        <ng-include src="'./assets/templates/sidebar.html'"></ng-include>
    </div>
           
    <div class="page-container">
        <div ng-if="showHeader == true">
            <ng-include src="'./assets/templates/header.html'"></ng-include>
            
            <!-- START OVERLAY -->
			<div ng-include src=" './assets/templates/quick_search.html' " include-replace>	</div>
			<!-- END OVERLAY -->
        </div>
        <ui-view></ui-view>
    </div>
    
    <link id="lazyload_placeholder">

    <script type="text/javascript">
        window.paceOptions = {
            ajax: {
                ignoreURLs: ['pubnub.com/time','pubnub.com/subscribe','pubnub.com/publish','pubnub.com/v2/presence','login/getNotifications']
            }
        };
    </script>

    <script src="//cdn.pubnub.com/pubnub.min.js"></script>
    <script src="app/dependecies.min.js"></script>
    <script src="//pubnub.github.io/angular-js/scripts/pubnub-angular.js"></script>
    <?php 
    createScriptOrLinkTag('app/routes.js','script');
    createScriptOrLinkTag('app/mainController.js','script');?>
    
    <script src="app/config.lazyload.js" type="text/javascript"></script>
    <script src="assets/js/jstz.min.js" type="text/javascript"></script>

    <script src="assets/js/directives/pg-sidebar.js" type="text/javascript"></script>
    <script src="assets/js/directives/datepicker.js" type="text/javascript"></script>
    <script src="assets/js/directives/pg-dropdown.js" type="text/javascript"></script>
    <script src="assets/js/directives/pg-form-group.js" type="text/javascript"></script>
    <script src="assets/js/directives/pg-navigate.js" type="text/javascript"></script>
    <script src="assets/js/directives/pg-portlet.js" type="text/javascript"></script>
    <script src="assets/js/directives/pg-tab.js" type="text/javascript"></script>
    <script src="assets/js/directives/pg-search.js" type="text/javascript"></script>
    <script src="assets/js/directives/pg-quickview.js" type="text/javascript"></script>
    <script src="assets/js/directives/pg-notification-center.js" type="text/javascript"></script>
    <script src="assets/js/directives/pg-horizontal-menu.js" type="text/javascript"></script>
    <script src="assets/js/directives/pg-tab-dropdownfx.js" type="text/javascript"></script>
    <script src="assets/plugins/angular-signature/src/signature.js"></script>
    <script src="assets/plugins/angular/angular-sortable-view.min.js"></script>
    <script src="assets/plugins/angular/angular.dcb-img-fallback.min.js"></script>
    
    <script src="assets/js/directives/skycons.js"></script>
    <script src='assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js'></script>
   
    <script src="app/packages/dirPagination.js"></script>
    <?php createScriptOrLinkTag('app/services/myServices.js','script');?>
    <script src="app/services/sample.js"></script>
    <script src="app/helper/myHelper.js"></script>
    

    <!-- App Controller -->
    <?php 
    createScriptOrLinkTag('app/controllers/ItemController.js','script');
    createScriptOrLinkTag('app/controllers/authCtrl.js','script');
    createScriptOrLinkTag('app/controllers/trucksController.js','script');
    createScriptOrLinkTag('app/controllers/truckstopController.js','script');
    createScriptOrLinkTag('app/controllers/driversController.js','script');
    createScriptOrLinkTag('app/controllers/loadsController.js','script');
    createScriptOrLinkTag('app/controllers/iterationLoadsController.js','script');
    createScriptOrLinkTag('app/controllers/autoLoadsController.js','script');
    createScriptOrLinkTag('app/controllers/assignedLoadsController.js','script');
    createScriptOrLinkTag('app/controllers/search.js','script');
    createScriptOrLinkTag('app/controllers/brockersController.js','script');
    createScriptOrLinkTag('app/controllers/docsController.js','script');
    createScriptOrLinkTag('app/controllers/billingsController.js','script');
    createScriptOrLinkTag('app/controllers/filteredBillingsController.js','script');
    createScriptOrLinkTag('app/controllers/reportsController.js','script');
    createScriptOrLinkTag('app/controllers/testController.js','script');
    createScriptOrLinkTag('app/controllers/trailersController.js','script');
    createScriptOrLinkTag('app/controllers/sendPaymentController.js','script');
    createScriptOrLinkTag('app/controllers/notificationsController.js','script');
    createScriptOrLinkTag('app/controllers/billingDashboardController.js','script');
    createScriptOrLinkTag('app/controllers/driversInsightsController.js','script');
    createScriptOrLinkTag('app/controllers/truckInsightsController.js','script');
    createScriptOrLinkTag('app/controllers/shipperController.js','script');
    createScriptOrLinkTag('app/services/shipperService.js','script');
    createScriptOrLinkTag('app/services/environmentconfig.js','script');
    ?>
    <script src="login/loadLanguages"></script>
    <!--for footable-->
    
    <!-- Add in FooTable itself-->
    
    <script type="text/javascript">
        window.onload = function() {     
            $('.menu-items li a').click(function(){
                $('.menu-items li a span').removeClass('open active');
                if($(this).parent().children('ul').is(':visible'))
                {
                    $('.menu-items li a span').removeClass('open active');
                    $('.menu-items li ul').hide();
                    $(this).parent().children('ul').hide();
                }
                else
                {
                    $(this).children('span').addClass('open active');
                    $('.menu-items li ul').hide();
                    $(this).parent().children('ul').show();
                }
            });
               
            $('body').on('focus', '.form-group.form-group-default :input', function() {
                $('.form-group.form-group-default').removeClass('focused');
                $(this).parents('.form-group').addClass('focused');
            });
           
            $('body').on('blur', '.form-group.form-group-default :input', function() {
                $(this).parents('.form-group').removeClass('focused');
                if ($(this).val()) {
                    $(this).closest('.form-group').find('label').addClass('fade');
                } else {
                    $(this).closest('.form-group').find('label').removeClass('fade');
                }
            });

            $('body').find('.checkbox, .radio').hover(function() {
                $(this).parents('.form-group').addClass('focused');
            }, function() {
                $(this).parents('.form-group').removeClass('focused');
            });


            
        }  
    </script>
    <script src="//maps.google.com/maps/api/js?key=<?php echo $key; ?>&libraries=places,geometry"></script>
   
</body>
</html>
