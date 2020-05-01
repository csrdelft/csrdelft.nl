<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\repository\groepen\leden\OnderverenigingsLedenRepository;
use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;


/**
 * Ondervereniging.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\OnderverenigingenRepository")
 * @ORM\Table("onderverenigingen")
 */
class Ondervereniging extends AbstractGroep {

	const LEDEN = OnderverenigingsLedenRepository::class;

	/**
	 * (Adspirant-)Ondervereniging
	 * @var OnderverenigingStatus
	 * @ORM\Column(type="string")
	 */
	public $soort;

	/**
	 * @var OnderverenigingsLid[]
	 * @ORM\OneToMany(targetEntity="OnderverenigingsLid", mappedBy="groep")
	 */
	public $leden;

	public function getLeden() {
		return $this->leden;
	}

	public function getLidType() {
		return OnderverenigingsLid::class;
	}

	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'soort' => array(T::Enumeration, false, OnderverenigingStatus::class),
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'onderverenigingen';

	public function getUrl() {
		return '/groepen/onderverenigingen/' . $this->id;
	}

}
