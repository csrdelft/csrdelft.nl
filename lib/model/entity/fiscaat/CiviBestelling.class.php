<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

require_once 'model/fiscaat/CiviBestellingInhoudModel.class.php';
require_once 'model/entity/fiscaat/CiviBestelling.class.php';

/**
 * Class CiviBestelling
 *
 * Heeft een of meer @see CiviBestellingInhoud
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviBestelling extends PersistentEntity {
	public $id;
	public $uid;
	public $totaal = 0;
	public $deleted;
	public $moment;

	/**
	 * @var CiviBestellingInhoud[]
	 */
	public $inhoud = array();

	public function getInhoud() {
		return CiviBestellingInhoudModel::instance()->find('bestelling_id = ?', array($this->id));
	}

	public function getInhoudBeschrijving() {
		return implode(", ", array_map(function ($inhoud) {
			return $inhoud->aantal . " van " . CiviProductModel::instance()->getProduct($inhoud->product_id)->beschrijving;
		}, $this->getInhoud()->fetchAll()));
	}

	public function jsonSerialize() {
		$data = parent::jsonSerialize();
		$data['inhoud'] = $this->getInhoudBeschrijving();
		return $data;
	}

	protected static $table_name = 'CiviBestelling';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'uid' => array(T::UID),
		'totaal' => array(T::Integer),
		'deleted' => array(T::Boolean),
		'moment' => array(T::Timestamp)
	);
	protected static $primary_key = array('id');
}
