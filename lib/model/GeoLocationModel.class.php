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

	public function savePosition($uid, array $position) {
		$location = new GeoLocation();
		$location->uid = $uid;
		$location->moment = getDateTime();
		$location->position = json_encode($position);
		$this->create($location);
		return $location;
	}

}
