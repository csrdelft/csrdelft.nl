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

	public function nieuw() {
		$score = new LedenMemoryScore();
		$score->door_uid = LoginModel::getUid();
		$score->wanneer = getDateTime();
		return $score;
	}

	public function getAllScores() {
		return $this->find('eerlijk = 1', null, 'door_uid', 'tijd');
	}

	public function getScores(AbstractGroep $groep) {
		return $this->find('eerlijk = 1 AND groep = ?', array($groep->getUUID()), 'door_uid', 'tijd');
	}

}
