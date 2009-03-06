
    //<![CDATA[
	
var geocoder;
var gmarkers = [];
var i = 0;

function loadGmaps(divId, address) {
	if (GBrowserIsCompatible()) {
	    var map = new GMap2(document.getElementById(divId));
		//map.addControl(new GLargeMapControl());
		//map.addControl(new GMapTypeControl());
		var geocoder = new GClientGeocoder();
	    geocoder.getLatLng(
			address, 
			function(adr_latlng) {
				if (!adr_latlng) {
					alert(address + " niet gevonden");
				} else {
					map.setCenter(adr_latlng, 14);
					var marker = createMarker(adr_latlng,address);
			        map.addOverlay(marker);
				}	    
			}
		);//close getLatLng	    
	}
}

// This function picks up the click and opens the corresponding info window
function myclick(i) {
	GEvent.trigger(gmarkers[i], "click");
}
//Create marker and set up event window
function createMarker(point,html){
	var marker = new GMarker(point);
	GEvent.addListener(marker, "click", function() {
		marker.openInfoWindowHtml(html);
	}); 
	// save the info we need to use later for the side_bar
	gmarkers[i] = marker;
	i++;
	  
	return marker;
}

//showAddress
function showAddress(map,geocoder,address,html,label) {
  geocoder.getLatLng(
    address,
    function(point) {
      if (!point) {
      //  alert(address + " niet gevonden");
      } else {
        var marker = createMarker(point,html+'<br/><br/>'+address,label);
        map.addOverlay(marker);
		
      }
    }
  );
}

    //]]>