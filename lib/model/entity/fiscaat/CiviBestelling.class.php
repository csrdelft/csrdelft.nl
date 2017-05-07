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
	public $cie;

	/**
	 * @var CiviBestellingInhoud[]
	 */
	public $inhoud = array();

	public function getInhoud() {
		return CiviBestellingInhoudModel::instance()->find('bestelling_id = ?', array($this->id));
	}

	public function jsonSerialize() {
		$data = parent::jsonSerialize();
		$data['inhoud'] = CiviBestellingModel::instance()->getBeschrijving($this->getInhoud());
		return $data;
	}

	protected static $table_name = 'CiviBestelling';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'uid' => array(T::UID),
		'totaal' => array(T::Integer),
		'deleted' => array(T::Boolean),
		'moment' => array(T::Timestamp),
		'cie' => array(T::Enumeration, false, CiviSaldoCommissieEnum::class)
	);
	protected static $primary_key = array('id');
}
