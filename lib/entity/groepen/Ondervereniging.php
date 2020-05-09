<?php

namespace CsrDelft\entity\groepen;

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

	public function getUrl() {
		return '/groepen/onderverenigingen/' . $this->id;
	}
}
