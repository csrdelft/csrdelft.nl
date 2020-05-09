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
	/**
	 * @var BestuursLid[]
	 * @ORM\OneToMany(targetEntity="BestuursLid", mappedBy="groep")
	 */
	public $leden;

	public function getLeden() {
		return $this->leden;
	}

	public function getLidType() {
		return BestuursLid::class;
	}

	/**
	 * Bestuurstekst
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $bijbeltekst;

	public function getUrl() {
		return '/groepen/besturen/' . $this->id;
	}

}
