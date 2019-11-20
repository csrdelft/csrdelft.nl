<?php


namespace CsrDelft\model\entity\courant;


use CsrDelft\model\CourantBerichtModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class Courant extends PersistentEntity {
	public $id;
	public $verzendMoment;
	public $inhoud;
	public $verzender;

	protected static $table_name = 'courant';

	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'verzendMoment' => [T::DateTime],
		'inhoud' => [T::Text],
		'verzender' => [T::UID],
	];

	protected static $primary_key = ['id'];

	public function getJaar() {
		return date('Y', strtotime($this->verzendMoment));
	}
}
