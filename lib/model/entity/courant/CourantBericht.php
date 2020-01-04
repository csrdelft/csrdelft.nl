<?php


namespace CsrDelft\model\entity\courant;


use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class CourantBericht extends PersistentEntity {
	public $id;
	public $titel;
	public $cat;
	public $bericht;
	public $volgorde;
	public $uid;
	public $datumTijd;

	public function setVolgorde() {
		$this->volgorde = [
			'voorwoord' => 0,
			'bestuur' => 1,
			'csr' => 2,
			'overig' => 3,
			'sponsor' => 4,
		][$this->cat];
	}

	protected static $table_name = 'courantbericht';
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'titel' => [T::String],
		'cat' => [T::Enumeration, false, CourantCategorie::class],
		'bericht' => [T::Text],
		'volgorde' => [T::Integer],
		'uid' => [T::UID, true],
		'datumTijd' => [T::DateTime],
	];
	protected static $primary_key = ['id'];
}
