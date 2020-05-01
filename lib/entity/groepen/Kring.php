<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\repository\groepen\leden\KringLedenRepository;
use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * Kring.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\KringenRepository")
 * @ORM\Table("kringen")
 */
class Kring extends AbstractGroep {

	const LEDEN = KringLedenRepository::class;

	/**
	 * Verticaleletter
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $verticale;
	/**
	 * Kringnummer
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $kring_nummer;

	/**
	 * @var KringLid[]
	 * @ORM\OneToMany(targetEntity="KringLid", mappedBy="groep")
	 */
	public $leden;

	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'verticale' => [T::Char],
		'kring_nummer' => [T::Integer]
	];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'kringen';

	public function getUrl() {
		return '/groepen/kringen/' . $this->verticale . '.' . $this->kring_nummer;
	}

	/**
	 * @return KringLid[]
	 */
	public function getLeden(){
		return $this->leden;
	}

	public function getLidType() {
		returN KringLid::class;
	}

}
