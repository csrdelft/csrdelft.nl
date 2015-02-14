<?php

/**
 * GeoLocationModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GeoLocationModel extends PersistenceModel {

	const orm = 'GeoLocation';

	protected static $instance;
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'moment DESC';

	public function savePosition($uid, $timestamp, array $position) {
		$location = new GeoLocation();
		$location->uid = $uid;
		$location->moment = getDateTime($timestamp / 1000);
		$location->position = json_encode($position);
		$this->create($location);
		return $location;
	}

	public function getLastLocation($uid) {
		return $this->find('uid = ?', array($uid), null, null, 1)->fetch();
	}

	public function getAllLastLocations() {
		$last = array();
		foreach ($this->find() as $loc) {
			if (!isset($last[$loc->uid])) {
				$last[$loc->uid] = $loc;
			}
		}
		return $last;
	}

	public function getRoute($uid) {
		return $this->find('uid = ?', array($uid));
	}

}
