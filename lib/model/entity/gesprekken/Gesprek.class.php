<?php

namespace CsrDelft\model\entity\gesprekken;

use CsrDelft\common\CsrException;
use CsrDelft\model\gesprekken\GesprekBerichtenModel;
use CsrDelft\model\gesprekken\GesprekDeelnemersModel;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Gesprek.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Gesprek extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $gesprek_id;
	/**
	 * DateTime last message
	 * @var string
	 */
	public $laatste_update;
	/**
	 * Aantal nieuwe berichten sinds laatst gelezen
	 * @var int
	 */
	public $aantal_nieuw;
	/**
	 * Aantal seconden delay
	 * @var int
	 */
	public $auto_update;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'gesprek_id' => array(T::Integer, false, 'auto_increment'),
		'laatste_update' => array(T::DateTime)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('gesprek_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'gesprekken';

	public function getDeelnemers() {
		return GesprekDeelnemersModel::instance()->getDeelnemersVanGesprek($this);
	}

	public function getDeelnemersFormatted() {
		$deelnemers = '';
		foreach ($this->getDeelnemers() as $deelnemer) {
			if ($deelnemer->uid === LoginModel::getUid()) {
				continue;
			}
			if (!empty($deelnemers)) {
				$deelnemers .= ', <br />';
			}
			$deelnemers .= ProfielModel::getNaam($deelnemer->uid, 'civitas');
		}
		return $deelnemers;
	}

	public function getBerichten(GesprekDeelnemer $deelnemer, $lastUpdate) {
		if (!is_int($lastUpdate)) {
			throw new CsrException('lastUpdate invalid');
		}
		$toegevoegd = strtotime($deelnemer->toegevoegd_moment);
		if ($lastUpdate < $toegevoegd) {
			$lastUpdate = $toegevoegd;
		}
		// Auto update
		$threshold = (int)InstellingenModel::get('gesprekken', 'active_threshold_seconds');
		$anderen = 0;
		$active = 0;
		foreach ($this->getDeelnemers() as $d) {
			if ($d->uid !== $deelnemer->uid) {
				$anderen++;
				if (time() - strtotime($d->gelezen_moment) < $threshold) {
					$active++;
				}
			}
		}
		if ($active > 0) {
			$this->auto_update = 1000 * (int)InstellingenModel::get('gesprekken', 'active_interval_seconds');
		} elseif ($anderen > 0) {
			$this->auto_update = 1000 * (int)InstellingenModel::get('gesprekken', 'slow_interval_seconds');
		} else {
			$this->auto_update = false;
		}
		// Update deelnemer
		$deelnemer->gelezen_moment = getDateTime();
		GesprekDeelnemersModel::instance()->update($deelnemer);
		return GesprekBerichtenModel::instance()->getBerichtenSinds($this, $lastUpdate);
	}

	public function getAantalNieuweBerichten(GesprekDeelnemer $deelnemer, $lastUpdate) {
		if (!isset($this->aantal_nieuw)) {
			$toegevoegd = strtotime($deelnemer->toegevoegd_moment);
			if ($lastUpdate < $toegevoegd) {
				$lastUpdate = $toegevoegd;
			}
			$this->aantal_nieuw = GesprekBerichtenModel::instance()->getAantalBerichtenSinds($this, $lastUpdate);
		}
		return $this->aantal_nieuw;
	}

}
