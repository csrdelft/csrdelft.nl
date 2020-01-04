<?php

namespace CsrDelft\model\entity\fiscaat;

use CsrDelft\model\fiscaat\CiviBestellingInhoudModel;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use PDOStatement;

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
	public $comment;
	public $cie;

	/**
	 * @var CiviBestellingInhoud[]
	 */
	public $inhoud = array();

	/**
	 * @return PDOStatement|CiviBestellingInhoud[]
	 */
	public function getInhoud() {
		return CiviBestellingInhoudModel::instance()->find('bestelling_id = ?', array($this->id));
	}

	/**
	 * @return string[]
	 */
	public function jsonSerialize() {
		$data = parent::jsonSerialize();
		$data['inhoud'] = CiviBestellingModel::instance()->getBeschrijvingText($this->getInhoud());
		return $data;
	}

	protected static $table_name = 'CiviBestelling';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'uid' => array(T::UID),
		'totaal' => array(T::Integer),
		'deleted' => array(T::Boolean),
		'moment' => array(T::Timestamp),
		'comment' => array(T::String, true),
		'cie' => array(T::Enumeration, false, CiviSaldoCommissieEnum::class)
	);
	protected static $primary_key = array('id');
}
