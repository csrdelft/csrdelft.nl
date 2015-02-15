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
				'map' => 'P_LOGGED_IN'
			);
		} else {
			$this->acl = array(
				'save'	 => 'P_LOGGED_IN',
				'get'	 => 'ouderejaars'
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
			echo '"pasfoto": ' . json_encode($profiel->getPasfotoTag('pasfoto', true)) . ',' . "\n";
			echo '"datetime": ' . json_encode(reldate($loc->moment)) . ',' . "\n";
			echo '"position": ' . $loc->position . "\n";
			echo '}';
		}
		echo ']';
		exit;
	}

	public function map($uid = null) {
		if (ProfielModel::existsUid($uid)) {
			$data = json_encode(array('uid' => $uid));
		} else {
			$data = '{}';
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

						var drawLocation = function (location) {

							var geolocate = new google.maps.LatLng(location.position.latitude, location.position.longitude);
							var html = location.pasfoto + '<p style="text-align: right;">' + location.datetime + '</p>';
							html += '<div style="max-width: 173px; word-wrap: break-word;">' + JSON.stringify(location.position) + '</div>';

							var marker = new google.maps.Marker({
								position: geolocate,
								map: map
							});

							var infowindow = new google.maps.InfoWindow({
								content: html
							});

							google.maps.event.addListener(marker, 'click', function () {
								infowindow.open(map, marker);
							});
							infowindow.open(map, marker);

							map.setCenter(geolocate);

						};

						var getLocation = function () {

							$.post('/geolocation/get', <?= $data; ?>, function (data, textStatus, jqXHR) {

								$.each(data, function (index) {
									drawLocation(data[index]);
								});
								//window.setTimeout(getLocation, Math.round(new Date()) - locations.timestamp); // auto update
							});

						};

						getLocation();

					})();

				</script>
			</body>
		</html>
		<?php
		exit;
	}

}
