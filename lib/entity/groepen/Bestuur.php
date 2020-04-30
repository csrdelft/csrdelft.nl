<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\repository\groepen\leden\BestuursLedenRepository;
use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * Bestuur.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\BesturenRepository")
 * @ORM\Table("besturen")
 */
class Bestuur extends AbstractGroep {

	const LEDEN = BestuursLedenRepository::class;

	/**
	 * @var BestuursLid[]
	 * @ORM\OneToMany(targetEntity="BestuursLid")
	 */
	public $leden;

	/**
	 * Bestuurstekst
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $bijbeltekst;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'bijbeltekst' => [T::Text]
	];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'besturen';

	public function getUrl() {
		return '/groepen/besturen/' . $this->id;
	}

}
