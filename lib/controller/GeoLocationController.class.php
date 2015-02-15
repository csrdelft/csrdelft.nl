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
			echo '{' . "\n";
			echo '"uid": "' . $loc->uid . '",' . "\n";
			echo '"pasfoto": ' . json_encode($profiel->getPasfotoTag('pasfoto', true)) . ',' . "\n";
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
			$data = 'null';
		}
		?>
		<html>
			<body>
				<div id="google_canvas" style="height: 100%;"></div>
				<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
				<script src="//maps.googleapis.com/maps/api/js?v=3.exp&sensor=true"></script>
				<script type="text/javascript">

					(function () {

						var map = new google.maps.Map(document.getElementById('google_canvas'), {
							zoom: 15,
							mapTypeId: google.maps.MapTypeId.ROADMAP
						});

						var markers = {};
						var infowindows = {};

						var drawLocation = function (location) {


							var geolocate;
							// backwards compatibility
							if (location.position.coords) {
								geolocate = new google.maps.LatLng(location.position.coords.latitude, location.position.coords.longitude);
							}
							else {
								geolocate = new google.maps.LatLng(location.position.latitude, location.position.longitude);
							}

							var html = location.pasfoto + '<p style="text-align: right;">' + location.datetime + '</p>';
							html += '<div style="max-width: 173px; word-wrap: break-word;">' + JSON.stringify(location.position) + '</div>';

							if (markers[location.uid]) {
								google.maps.event.clearListeners(markers[location.uid], 'click');
								delete infowindows[location.uid];

								marker.setPosition(geolocate);
							}
							else {
								var pinColor = Math.floor(Math.random() * 16777215).toString(16);
								var pinImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + pinColor,
										new google.maps.Size(21, 34),
										new google.maps.Point(0, 0),
										new google.maps.Point(10, 34)
										);

								var marker = new google.maps.Marker({
									position: geolocate,
									map: map,
									icon: pinImage
								});
								marker[location.uid] = marker;
							}

							var infowindow = new google.maps.InfoWindow({
								content: html
							});
							infowindows[location.uid] = infowindow;

							google.maps.event.addListener(marker, 'click', function () {
								infowindow.open(map, marker);
							});

							if (<?= $data; ?>) {
								infowindow.open(map, marker);
							}

							return marker;
						};

						var getLocation = function (uid) {

							$.post('/geolocation/get', uid, function (data, textStatus, jqXHR) {

								var last = false;

								$.each(data, function (index) {
									last = drawLocation(data[index]);

									var autoUpdate = Math.round(new Date() / 1000) - data[index].timestamp;
									if (autoUpdate < 86400000) { // binnen 24h
										if (autoUpdate < 10000) { // min delay 10s
											autoUpdate = 10000;
										}
										window.setTimeout(function () {
											getLocation({
												uid: data[index].uid
											});
										}, autoUpdate);
									}
								});

								if (<?= $data; ?> && last) {
									map.setCenter(last.getPosition());
								}
							});

						};

						getLocation(<?= $data; ?>);

					})();

				</script>
			</body>
		</html>
		<?php
		exit;
	}

}
