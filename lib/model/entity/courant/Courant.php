<?php


namespace CsrDelft\model\entity\courant;


use CsrDelft\model\CourantBerichtModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class Courant extends PersistentEntity {
	public $id;
	public $verzendMoment;
	public $template;
	public $verzender;

	protected static $table_name = 'courant';

	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'verzendMoment' => [T::DateTime],
		'template' => [T::String],
		'verzender' => [T::UID],
	];

	protected static $primary_key = ['id'];

	public function getJaar() {
		return date('Y', strtotime($this->verzendMoment));
	}

	public function getBerichten() {
		if (!$this->id) {
			return CourantBerichtModel::instance()->find('courantID IS NULL');
		}
		return CourantBerichtModel::instance()->find('courantID = ?', [$this->id]);
	}

}
