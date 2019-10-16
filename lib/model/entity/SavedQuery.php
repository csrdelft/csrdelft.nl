<?php

namespace CsrDelft\model\entity;

use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class SavedQuery extends PersistentEntity {
	public $ID;
	public $savedquery;
	public $beschrijving;
	public $permissie;
	public $categorie;

	public function magBekijken() {
		return LoginModel::mag($this->permissie) || LoginModel::mag(P_ADMIN);
	}

	protected static $primary_key = ['ID'];
	protected static $table_name = 'savedquery';
	protected static $persistent_attributes = [
		'ID' => [T::Integer, false, 'auto_increment'],
		'savedquery' => [T::Text, false],
		'beschrijving' => [T::String, false],
		'permissie' => [T::String, false],
		'categorie' => [T::String, false]
	];
}
