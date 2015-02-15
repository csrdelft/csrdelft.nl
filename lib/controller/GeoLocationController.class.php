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
			echo '"woonadres": ' . json_encode(nl2br(str_replace('Nederland', '', $profiel->getFormattedAddress()))) . ',' . "\n";
			echo '"ouders": ' . json_encode(nl2br(str_replace('Nederland', '', $profiel->getFormattedAddressOuders()))) . ',' . "\n";
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
			$data = 'null';
		}
		?>
		<html>
			<body>
				<div id="google_canvas" style="height: 100%;"></div>
				<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
				<script src="//maps.googleapis.com/maps/api/js?v=3.exp&sensor=true"></script>
				<script src="//<?= CSR_DOMAIN; ?>/layout/js/google.maps.v3.StyledMarker.js"></script>
				<script type="text/javascript">

					(function () {

						var map = new google.maps.Map(document.getElementById('google_canvas'), {
							zoom: 15,
							mapTypeId: google.maps.MapTypeId.ROADMAP,
							center: new google.maps.LatLng(52.006066, 4.360246)
						});

						var markers = {};
						var infowindows = {};

						var drawLocation = function (location) {
							var openwindow = <?= $data === 'null' ? 'false' : 'true'; ?>;

							var geolocate;
							// backwards compatibility
							if (location.position.coords) {
								geolocate = new google.maps.LatLng(location.position.coords.latitude, location.position.coords.longitude);
							}
							else {
								geolocate = new google.maps.LatLng(location.position.latitude, location.position.longitude);
							}

							var html = '<table><tr><td>' + location.pasfoto + '<p>' + location.datetime + '</p></td>';
							html += '<td style="max-width: 173px; word-wrap: break-word;">' + location.woonadres + '<br />' + location.ouders + '<br />' + JSON.stringify(location.position, undefined, 4) + '</td>';
							html += '</tr></table>';

							if (markers[location.uid]) {
								marker = markers[location.uid];

								if (infowindows[location.uid].get('isopen')) {
									openwindow = true;
								}

								google.maps.event.clearListeners(marker, 'click');
								infowindows[location.uid].close();
								delete infowindows[location.uid];

								marker.setPosition(geolocate);
							}
							else {
								var randomColor = "#000000".replace(/0/g, function () {
									return (~~(Math.random() * (16 - 8) + 8)).toString(16);
								});

								var styleIconClass = new StyledIcon(StyledIconTypes.CLASS, {
									color: randomColor
								});

								var marker = new StyledMarker({
									styleIcon: new StyledIcon(StyledIconTypes.MARKER, {text: location.uid}, styleIconClass),
									position: geolocate,
									map: map
								});
								markers[location.uid] = marker;
							}

							var infowindow = new google.maps.InfoWindow({
								content: html
							});
							infowindows[location.uid] = infowindow;

							google.maps.event.addListener(infowindow, 'closeclick', function () {
								infowindow.set('isopen', false);
							});
							google.maps.event.addListener(marker, 'click', function () {
								infowindow.open(map, marker);
								infowindow.set('isopen', true);
							});

							if (openwindow) {
								infowindow.open(map, marker);
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

						getLocation(<?= $data; ?>);

					})();

				</script>
			</body>
		</html>
		<?php
		exit;
	}

}
