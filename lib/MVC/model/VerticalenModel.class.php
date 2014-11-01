<?php

/**
 * VerticalenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class VerticalenModel extends PersistenceModel {

	const orm = 'Verticale';

	protected static $instance;

	public function getVerticaleById($id) {
		return $this->retrieveByPrimaryKey(array((int) $id));
	}

	public function getVerticaleByLetter($letter) {
		return $this->find('letter = ?', array($letter))->fetch();
	}

	public function findVerticaleByName($naam) {
		return $this->find('naam LIKE ?', array('%' . $naam . '%'));
	}

	/**
	 * Get uid of verticale leider.
	 * 
	 * @param Verticale $verticale
	 * @return string
	 */
	public function getVerticaleLeider(Verticale $verticale) {
		return Database::instance()->sqlSelect(array('uid'), 'lid', 'verticale = ? AND motebal = 1', array($verticale->id), null, null, 1)->fetchColumn();
	}

}
