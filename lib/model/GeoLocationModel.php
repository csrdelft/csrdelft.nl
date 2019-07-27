<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\GeoLocation;
use CsrDelft\Orm\PersistenceModel;

/**
 * GeoLocationModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class GeoLocationModel extends PersistenceModel {

	const ORM = GeoLocation::class;

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'moment DESC';

	public function savePosition($uid, $timestamp, array $position) {
		$location = new GeoLocation();
		$location->uid = $uid;
		$location->moment = getDateTime($timestamp);
		$location->position = json_encode($position);
		if ($this->exists($location)) {
			$this->update($location);
		} else {
			$this->create($location);
		}
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
