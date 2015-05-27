var geocoder = new google.maps.Geocoder();

var lat;
var lon;



function updateMarkerStatus(str) {
  document.getElementById('markerStatus').innerHTML = str;
}

function updateMarkerPosition(latLng) {
    lat = (latLng.lat());
    lon = (latLng.lng());
}

function updateMarkerAddress(str) {
  //document.getElementById('address').innerHTML = str;
  
  // $('#address').val(str)
}

function initialize() {
    
  var latLng = new google.maps.LatLng(lat, lon);
  var map = new google.maps.Map(document.getElementById('mapCanvas'), {
    zoom: 16,
    center: latLng,
    mapTypeId: google.maps.MapTypeId.SATELLITE
  });
  var marker = new google.maps.Marker({
    position: latLng,
    title: 'Point A',
    map: map
  });
  
  // Update current position info.
  updateMarkerPosition(latLng);
  
  // Add dragging event listeners.
  google.maps.event.addListener(marker, 'dragstart', function() {
  });
  
  google.maps.event.addListener(marker, 'drag', function() {
    console.log(marker.getPosition());
    updateMarkerPosition(marker.getPosition());
  });
  
  google.maps.event.addListener(marker, 'dragend', function() {
    geocodePosition(marker.getPosition());
  });
}

// Onload handler to fire off the app.
google.maps.event.addDomListener(window, 'load', initialize);

var geocoder;
var map;
  $(document).ready(function() {
   	omzetten();
   	  
  });


function omzetten() {
  $('#coMsg').html('coördinaten opzoeken...');

  geocoder = new google.maps.Geocoder();
  var address = document.getElementById('address').innerHTML;
  console.log(address);
  	
  geocoder.geocode( { 'address': address}, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
    	$('#coMsg').html('');
      	
                var latLng = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                
                console.log(latLng);

                updateMarkerPosition(latLng);
                
                initialize();
    } 
    else 
    {
    	$('#coMsg').html('Verkeerd adres ingegeven, kan geen coördinaten vinden.');
	    console.log('Geocode was not successful for the following reason: ' + status );	          
    }
  });
}