
    //<![CDATA[
	
var geocoder;
var gmarkers = [];
var i = 0;

function loadGmaps(divId, address) {
	if (GBrowserIsCompatible()) {
	    var map = new GMap2(document.getElementById(divId));
	    map.addControl(new GSmallMapControl());
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
function writeStaticGmap(div_id, address,width,height) {
    var div = document.getElementById(div_id);        
	var geocoder = new GClientGeocoder();
	geocoder.getLatLng(
		address, 
		function(adr_latlng) {
			if (!adr_latlng) {
				div.innerHTML=address + " niet gevonden";
			} else {
				var coordinates = adr_latlng.lat()+','+adr_latlng.lng(); 
				var imgtag = '<img src="https://maps.google.com/staticmap?\
					size='+width+'x'+height+'&\
					maptype=roadmap&\
					markers='+coordinates+',red&\
					key=ABQIAAAATQu5ACWkfGjbh95oIqCLYxRY812Ew6qILNIUSbDumxwZYKk2hBShiPLD96Ep_T-MwdtX--5T5PYf1A&\
					sensor=false\
					"></img>';
				div.innerHTML='<a href="https://maps.google.nl/maps?q='+address+'">'+imgtag+'</a>';
			}	    
		}
	);		
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