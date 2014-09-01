<?php
require_once 'configuratie.include.php';

/**
 * ledenkaart.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 * googlemaps-probeerseltje.
 * 
 */
if (!LoginModel::mag('P_LEDEN_READ')) {
	redirect(CSR_ROOT);
}

if (isset($_GET['xml'])) {
	$sLedenQuery = "
		SELECT 
			uid, voornaam, achternaam, tussenvoegsel, adres, postcode, woonplaats
		FROM
			lid
		WHERE
			status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL'
		ORDER BY adres;";

	$rLeden = $db->query($sLedenQuery);
	header('Content-Type: text/xml');
	echo '<?xml version="1.0" encoding="utf-8"?><markers>' . "\n";
	$current = '';
	while ($aLid = $db->next($rLeden)) {
		$adres = $aLid['adres'] . ', ' . $aLid['woonplaats'];

		if ($adres != $current) {
			if ($current != '')
				;

			$current = $address;
		}

		if ($aLid['adres'] != '') {
			echo '<marker address="' . $adres . '" label="' . Lid::naamLink($aLid['uid'], 'civitas', 'plain') . '">';
			echo '<infowindow><![CDATA[';
			echo Lid::naamLink($aLid['uid'], 'civitas', 'link') . '';
			echo ']]></infowindow></marker>' . "\n";
		}
	}
	echo '</markers>';
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Leden der Civitas in een kaartje van Google.</title>
		<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAATQu5ACWkfGjbh95oIqCLYxRY812Ew6qILNIUSbDumxwZYKk2hBShiPLD96Ep_T-MwdtX--5T5PYf1A"
		type="text/javascript"></script>
		<script type="text/javascript">

			//<![CDATA[

			var address;
			var geocoder;
			var gmarkers = [];
			var html;
			var htmls = [];
			var i = 0;
			var icon;
			var label;
			var map;
			var marker;
			var markers;
			var randomnumber;
			var side_bar_html = "";
			var xml;


			function load() {
				if (GBrowserIsCompatible()) {
					var map = new GMap2(document.getElementById("map"));
					map.addControl(new GLargeMapControl());
					map.addControl(new GMapTypeControl());
					var geocoder = new GClientGeocoder();
					var randomnumber = Math.floor(Math.random() * 11111)
					GDownloadUrl("/tools/ledenkaart.php?xml=true&random=" + randomnumber, function(data, responseCode) {
						var xml = GXml.parse(data);

						//store markers in markers array
						var markers = xml.documentElement.getElementsByTagName("marker");


						//loop over the markers array
						for (var i = 0; i < markers.length; i++) {
							var address = markers[i].getAttribute("address");
							var html = GXml.value(markers[i].getElementsByTagName("infowindow")[0]);
							var label = markers[i].getAttribute("label");
							showAddress(map, geocoder, address, html, label);
						} //close for loop

					}
					); //close GDownloadUrl


				}
				map.setCenter(new GLatLng(52.015, 4.356667), 14);

			}
			//
			// This function picks up the click and opens the corresponding info window
			function myclick(i) {
				GEvent.trigger(gmarkers[i], "click");
			}
			//Create marker and set up event window
			function createMarker(point, html, label) {
				var marker = new GMarker(point);
				GEvent.addListener(marker, "click", function() {
					marker.openInfoWindowHtml(html);
				});
				// save the info we need to use later for the side_bar
				gmarkers[i] = marker;
				htmls[i] = html;
				// add a line to the side_bar html
				side_bar_html += '<a href="javascript:myclick(' + i + ')">' + label + '</a><br>';
				document.getElementById("side_bar").innerHTML = side_bar_html;
				i++;

				return marker;
			}

			//showAddress
			function showAddress(map, geocoder, address, html, label) {
				geocoder.getLatLng(
						address,
						function(point) {
							if (!point) {
								//  alert(address + " niet gevonden");
							} else {
								var marker = createMarker(point, html + '<br/><br/>' + address, label);
								map.addOverlay(marker);

							}
						}
				);
			}

			//]]>
		</script>
	</head>
	<body onload="load()" onunload="GUnload()">
		<div id="side_bar" style="width: 200px; float: right; height: 600px; background-color: #bbb; overflow-y: scroll;"></div>
		<div id="map" style="width: 700px; height: 600px"></div>

	</body>
</html>

