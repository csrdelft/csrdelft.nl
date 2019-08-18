<?php


namespace CsrDelft\model\entity\bibliotheek;


use CsrDelft\model\bibliotheek\BiebRubriekModel;
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
		'p_id' => [T::Integer, false],
		'categorie' => [T::String, false]
	];

	public function __toString()
	{
		if ($this->p_id == $this->id) {
			return '';
		}
		else {
			$parent = (string) BiebRubriekModel::instance()->get($this->p_id);
			if ($parent !== '') {
				$parent .= ' - ';
			}
			return $parent.$this->categorie;

		}
	}

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];
}
