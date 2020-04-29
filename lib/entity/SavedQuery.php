<?php

namespace CsrDelft\entity;

use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CsrDelft\repository\SavedQueryRepository")
 * @ORM\Table("savedquery")
 */
class SavedQuery {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $ID;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $savedquery;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $beschrijving;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $permissie;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
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
