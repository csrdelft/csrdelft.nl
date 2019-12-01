<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\LedenMemoryScore;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;
use PDO;

/**
 * LedenMemoryScoresModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class LedenMemoryScoresModel extends PersistenceModel {

	const ORM = LedenMemoryScore::class;

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'tijd ASC, beurten DESC';

	public function nieuw() {
		$score = new LedenMemoryScore();
		$score->door_uid = LoginModel::getUid();
		$score->wanneer = getDateTime();
		return $score;
	}

	public function getGroepTopScores(AbstractGroep $groep, $limit = 10) {
		return $this->find('eerlijk = 1 AND groep = ?', array($groep->getUUID()), null, null, $limit);
	}

	public function getAllTopScores() {
		$fields = $this->getAttributes();
		$fields[1] = 'MIN(tijd) as tijd';
		$results = $this->database->sqlSelect($fields, $this->getTableName(), 'eerlijk = 1', array(), 'groep, door_uid');
		$results->setFetchMode(PDO::FETCH_CLASS, static::ORM, array($cast = true));
		return $results;
	}

}
