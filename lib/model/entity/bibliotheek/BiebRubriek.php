<?php


namespace CsrDelft\model\entity\bibliotheek;


use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class BiebRubriek extends PersistentEntity {

	protected static $table_name = 'biebcategorie';

	public $id;
	/**
	 * @var string parent rubriek
	 */
	public $p_id;
	/**
	 * @var string naam
	 */
	public $categorie;

	/**
	 * @var array
	 */
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, "auto_increment"],
		'boek_id' => [T::Integer, false],
		'schrijver_uid' => [T::String, false],
		'beschrijving' => [T::Text, false],
		'toegevoegd' => [T::DateTime, false],
		'bewerkdatum' => [T::DateTime, false]
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];
}