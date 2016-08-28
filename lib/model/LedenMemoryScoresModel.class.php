<?php

/**
 * LedenMemoryScoresModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class LedenMemoryScoresModel extends PersistenceModel {

	const orm = 'LedenMemoryScore';

	protected static $instance;
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
		$fields = $this->orm->getAttributes();
		$fields[1] = 'MIN(tijd) as tijd';
		$results = Database::sqlSelect($fields, $this->orm->getTableName(), 'eerlijk = 1', array(), 'groep, door_uid');
		$results->setFetchMode(PDO::FETCH_CLASS, self::orm, array($cast = true));
		return $results;
	}

}
