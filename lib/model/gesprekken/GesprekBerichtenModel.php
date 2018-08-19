<?php

namespace CsrDelft\model\gesprekken;

use CsrDelft\model\entity\gesprekken\Gesprek;
use CsrDelft\model\entity\gesprekken\GesprekBericht;
use CsrDelft\model\entity\gesprekken\GesprekDeelnemer;
use CsrDelft\Orm\PersistenceModel;

/**
 * GesprekBerichtenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class GesprekBerichtenModel extends PersistenceModel {

	const ORM = GesprekBericht::class;

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'bericht_id ASC';

	/**
	 * @param int $bericht_id
	 *
	 * @return GesprekBericht|false
	 */
	public static function get($bericht_id) {
		return static::instance()->retrieveByPrimaryKey(array($bericht_id));
	}

	/**
	 * @param Gesprek $gesprek
	 * @param int $lastUpdate
	 *
	 * @return GesprekBericht[]|\PDOStatement
	 */
	public function getBerichtenSinds(Gesprek $gesprek, $lastUpdate) {
		return $this->find('gesprek_id = ? AND moment > ?', array($gesprek->gesprek_id, getDateTime($lastUpdate)));
	}

	/**
	 * @param Gesprek $gesprek
	 * @param int $lastUpdate
	 *
	 * @return int
	 */
	public function getAantalBerichtenSinds(Gesprek $gesprek, $lastUpdate) {
		return $this->count('gesprek_id = ? AND moment > ?', array($gesprek->gesprek_id, getDateTime($lastUpdate)));
	}

	/**
	 * @param Gesprek $gesprek
	 * @param GesprekDeelnemer $deelnemer
	 * @param string $inhoud
	 *
	 * @return GesprekBericht
	 */
	public function maakBericht(Gesprek $gesprek, GesprekDeelnemer $deelnemer, $inhoud) {
		// Maak bericht
		$bericht = new GesprekBericht();
		$bericht->gesprek_id = $gesprek->gesprek_id;
		$bericht->moment = getDateTime();
		$bericht->auteur_uid = $deelnemer->uid;
		$bericht->inhoud = $inhoud;
		$bericht->id = $this->create($bericht);
		// Update gesprek
		$gesprek->laatste_update = $bericht->moment;
		GesprekkenModel::instance()->update($gesprek);
		return $bericht;
	}

	/**
	 * @param Gesprek $gesprek
	 */
	public function verwijderBerichtenVoorGesprek(Gesprek $gesprek) {
		foreach ($this->find('gesprek_id = ?', array($gesprek->gesprek_id)) as $bericht) {
			$this->delete($bericht);
		}
	}

}
