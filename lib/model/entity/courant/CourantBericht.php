<?php


namespace CsrDelft\model\entity\courant;


use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class CourantBericht extends PersistentEntity {
	public $id;
	public $courantId;
	public $titel;
	public $cat;
	public $bericht;
	public $volgorde;
	public $uid;
	public $datumTijd;

	protected static $table_name = 'courantbericht';
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'courantId' => [T::Integer, true],
		'titel' => [T::String],
		'cat' => [T::Enumeration, false, CourantCategorie::class],
		'bericht' => [T::Text],
		'volgorde' => [T::Integer],
		'uid' => [T::UID, true],
		'datumTijd' => [T::DateTime],
	];
	protected static $primary_key = ['id'];
}
