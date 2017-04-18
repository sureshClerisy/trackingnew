app.controller('investorController', function(dataFactory, $scope, PubNub , $http ,$rootScope ,vehicles, portlets, investorService, $sce, $state, $stateParams){
	if($rootScope.loggedInUser == false)
		$state.go('login');
	var inv = this; 
	inv.vehicle = {};
	inv.vehicleList = vehicles.list;
	inv.liveTrucks  = portlets.mapList;
	inv.vehicle.selected= {id:"all",vehicleName:"All Vehicles"};
	








	inv.renderMap = function(param, map_height){
        if ( map_height == undefined || map_height == '' ){ map_height = '90%'; }
        var zoomoptn = 5;
        if(param != undefined && param != ''){ zoomoptn = param; }

       	var mapOptions = {
            zoom: zoomoptn,
            center: {lat: 37.09024, lng: -95.712891},
            scrollwheel: false, 
            scaleControl: false, 
            icon:"./pages/img/truck-stop.png"
        } 

        inv.map = new google.maps.Map(document.getElementById('live-trucks-map'), mapOptions);
        angular.element('#live-trucks-map').height( map_height ) ;  
        var markers = [];
        
        inv.directionsService = new google.maps.DirectionsService;
        var infowindow        = new google.maps.InfoWindow();
        inv.directionDisplay  = [];
        var i=0;
        var bounds = new google.maps.LatLngBounds();
        angular.forEach(inv.liveTrucks, function(value, key) {
            var position = { lat: parseFloat( value.latitude ), lng: parseFloat( value.longitude ) };
            var icon = "./pages/img/"+value.heading.toLowerCase()+"_live.png";
            var timestamp = "Live telemetry from tracker as of ("+value.timestamp+")";
            var msgClass = "truck-live";

            if(value.mintues_ago > 2){
                icon = "./pages/img/"+value.heading.toLowerCase()+"_stale.png";
                timestamp = "Last known location telemetry stopped ("+value.timestamp+")";
                msgClass = "truck-stale";
            }

            marker = new google.maps.Marker( { position: position, icon: icon } );
            markers.push(marker);
            google.maps.event.addListener(marker, 'click', function() {
                infowindow.setContent('<div class="info-container">\
                    <p class="'+msgClass+'">'+ timestamp+'</p>\
                    <p><b>Driver : </b>'+ value.driverName+'</p>\
                    <p><b>Truck Name : </b> '+value.label+ '</p>\
                    <p><b>Address : </b> '+value.vehicle_address+', '+ value.city+', '+value.state+'</p>\
                    </div>');
                infowindow.open(inv.map, this);
            });
            bounds.extend(position);
        });

	    var mcOptions = { imagePath: "https://cdn.rawgit.com/googlemaps/js-marker-clusterer/gh-pages/images/m" };
	    var mc = new MarkerClusterer( inv.map,markers,mcOptions );     
	    google.maps.event.addListener( infowindow, 'domready', function() {
	        var iwOuter = $('.gm-style-iw').parent().addClass('live-trucks');
	    });
    }

    inv.vehicleChangeCallBack = function(item, model){
		var data = {vehicle_id: item.id};
		investorService.getPortletsData(data).
			then(function (response) {
				inv.liveTrucks = response.mapList;
				inv.renderMap();
				console.log(inv.liveTrucks);
		});
	}

    inv.renderMap();
});