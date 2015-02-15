<?php
require_once 'model/GeoLocationModel.class.php';

/**
 * GeoLocationController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GeoLocationController extends AclController {

	public function __construct($query) {
		parent::__construct($query, GeoLocationModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'map' => 'P_LEDEN_READ'
			);
		} else {
			$this->acl = array(
				'save'	 => 'P_LOGGED_IN',
				'get'	 => 'P_LEDEN_READ'
			);
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function save() {
		$timestamp = (int) filter_input(INPUT_POST, 'timestamp', FILTER_SANITIZE_NUMBER_INT);
		$coords = filter_input(INPUT_POST, 'coords', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$location = $this->model->savePosition(LoginModel::getUid(), $timestamp, $coords);
		$this->view = new JsonResponse($location);
	}

	public function get() {
		$uid = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_STRING);
		if ($uid) {
			$last = array($uid => $this->model->getLastLocation($uid));
		} else {
			$last = $this->model->getAllLastLocations();
		}
		header('Content-Type: application/json');
		$comma = false;
		echo '[';
		foreach ($last as $uid => $loc) {
			if ($comma) {
				echo ',' . "\n";
			} else {
				$comma = true;
			}
			$profiel = ProfielModel::get($loc->uid);
			$adres = $profiel->adres . ', ' . $profiel->woonplaats;
			if ($profiel->woonplaats !== $profiel->o_woonplaats) {
				$adres .= '<br /><br />' . $profiel->o_adres . ', ' . $profiel->o_woonplaats;
			}
			echo '{' . "\n";
			echo '"uid": "' . $loc->uid . '",' . "\n";
			echo '"naam": ' . json_encode($profiel->getNaam('civitas')) . ',' . "\n";
			echo '"adres": ' . json_encode($adres) . ',' . "\n";
			echo '"pasfoto": ' . json_encode($profiel->getLink('pasfoto')) . ',' . "\n";
			echo '"datetime": ' . json_encode(reldate($loc->moment)) . ',' . "\n";
			echo '"position": ' . $loc->position . ',' . "\n";
			echo '"timestamp": ' . strtotime($loc->moment) . "\n";
			echo '}';
		}
		echo ']';
		exit;
	}

	public function map($uid = null) {
		if (ProfielModel::existsUid($uid)) {
			$data = json_encode(array('uid' => $uid));
		} else {
			$data = json_encode(false);
		}
		?>
		<html>
			<body style="margin: 0;">
				<div id="google_canvas" style="height: 100%;"></div>
				<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
				<script src="//maps.googleapis.com/maps/api/js?v=3.exp&sensor=true"></script>
				<script src="//<?= CSR_DOMAIN; ?>/layout/js/google.maps.v3.StyledMarker.js"></script>
				<script src="//<?= CSR_DOMAIN; ?>/layout/js/Please.min.js"></script>
				<script type="text/javascript">

					(function () {

						// Draw
						var map = new google.maps.Map(document.getElementById('google_canvas'), {
							zoom: 15,
							mapTypeId: google.maps.MapTypeId.ROADMAP,
							center: new google.maps.LatLng(52.006066, 4.360246)
						});

						var markers = {};
						var infowindows = {};
						var openwindow = '<?= $uid; ?>';
						var radius;

						var drawLocation = function (location) {

							var latlon;

							if (location.position.coords) { // backwards compatibility
								location.position = location.position.coords;
							}

							latlon = new google.maps.LatLng(location.position.latitude, location.position.longitude);


							if (markers[location.uid]) {
								marker = markers[location.uid];

								google.maps.event.clearListeners(marker, 'click');
								infowindows[location.uid].close();
								delete infowindows[location.uid];

								marker.setPosition(latlon);
							}
							else {
								var styleIconClass = new StyledIcon(StyledIconTypes.CLASS, {
									color: Please.make_color({
										value: 1,
										saturation: .5
									})
								});

								var marker = new StyledMarker({
									styleIcon: new StyledIcon(StyledIconTypes.MARKER, {text: location.uid}, styleIconClass),
									position: latlon,
									map: map,
									title: location.naam
								});

								markers[location.uid] = marker;
							}

							var html = '<table><tr><td>' + location.pasfoto + '<p>' + location.datetime + '</p></td>';
							html += '<td style="max-width: 173px; word-wrap: break-word;">' + location.adres + '<br /><br />';
							html += JSON.stringify(location.position, undefined, 4) + '</td></tr></table>';

							var infowindow = new google.maps.InfoWindow({
								content: html
							});

							infowindows[location.uid] = infowindow;

							google.maps.event.addListener(infowindow, 'closeclick', function () {
								openwindow = false;
								if (radius) {
									radius.setMap(null);
								}
							});

							google.maps.event.addListener(marker, 'dblclick', function () {
								map.setZoom(17);
								map.panTo(marker.position);
							});

							google.maps.event.addListener(marker, 'click', function () {

								var options = {
									strokeColor: marker.styleIcon.color,
									strokeOpacity: 0.5,
									strokeWeight: 2,
									fillColor: marker.styleIcon.color,
									fillOpacity: 0.15,
									map: map,
									center: latlon,
									radius: parseInt(location.position.accuracy)
								};
								if (radius) {
									radius.setOptions(options);
								}
								else {
									radius = new google.maps.Circle(options);
								}

								if (openwindow) {
									infowindows[openwindow].close();
								}

								infowindow.open(map, marker);
								openwindow = location.uid;

							});

							if (openwindow === location.uid) {
								google.maps.event.trigger(marker, 'click');
							}

						};

						var getLocation = function (uid) {

							$.post('/geolocation/get', uid, function (data, textStatus, jqXHR) {

								$.each(data, function (index) {
									drawLocation(data[index]);
								});

								window.setTimeout(function () {
									getLocation(<?= $data; ?>);
								}, 10000); //TODO: server set auto update delay
							});

						};


						// Send
						var aLastPosition = false;

						var fnPositionSave = function (position) {

							if (!aLastPosition || ($(aLastPosition.coords).not(position.coords).length === 0 && $(position.coords).not(aLastPosition.coords).length === 0)) {

								$.post('/geolocation/save', {
									coords: position.coords,
									timestamp: Math.round(position.timestamp / 1000)
								}, function (data, textStatus, jqXHR) {

									drawLocation(data);

									if (!aLastPosition) {
										// Start auto update
										getLocation(<?= $data; ?>);
									}
									aLastPosition = position;
								});
							}
						};

						var fnPositionError = function (error) {
							switch (error.code) {
								case error.PERMISSION_DENIED:
									break;
								case error.POSITION_UNAVAILABLE:
									break;
								case error.TIMEOUT:
									break;
								case error.UNKNOWN_ERROR:
									break;
							}
						};

						if (navigator.geolocation) {
							navigator.geolocation.watchPosition(fnPositionSave, fnPositionError);
						}

					})();

				</script>
			</body>
		</html>
		<?php
		exit;
	}

}
