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
		$position = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$location = $this->model->savePosition(LoginModel::getUid(), $position);
		$this->view = new JsonResponse($location);
	}

	public function get() {
		$uid = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_STRING);
		$location = $this->model->getLastPosition($uid);
		if ($location) {
			echo $location->position;
			exit;
		} else {
			$this->view = new JsonResponse(false, 404);
		}
	}

	public function map($uid = null) {
		$profiel = ProfielModel::get($uid);
		if (!$profiel) {
			die('invalid uid');
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

						var drawPosition = function (position) {

							var geolocate = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
							var html = '<?= $profiel->getLink('pasfoto'); ?><div style="max-width: 173px; word-wrap: break-word;">' + JSON.stringify(position) + '</div>';

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

						var getPosition = function () {

							$.post('/geolocation/get', {
								uid: <?= $profiel->uid; ?>
							}, function (data, textStatus, jqXHR) {

								var position = $.parseJSON(data);
								window.setTimeout(getPosition, Math.round(new Date()) - position.timestamp); // auto update
								drawPosition(position);
							});

						};

						getPosition();

					})();

				</script>
			</body>
		</html>
		<?php
		exit;
	}

}
