<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\enum\OnderverenigingStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Ondervereniging.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\OnderverenigingenRepository")
 * @ORM\Table("onderverenigingen", indexes={
 *   @ORM\Index(name="begin_moment", columns={"begin_moment"}),
 *   @ORM\Index(name="status", columns={"status"}),
 *   @ORM\Index(name="familie", columns={"familie"}),
 *   @ORM\Index(name="soort", columns={"soort"}),
 * })
 */
class Ondervereniging extends AbstractGroep {
	public function __construct() {
		parent::__construct();
		$this->leden = new ArrayCollection();
	}

	/**
	 * (Adspirant-)Ondervereniging
	 * @var OnderverenigingStatus
	 * @ORM\Column(type="string")
	 * @Serializer\Groups("datatable")
	 */
	public $soort;

	/**
	 * @var OnderverenigingsLid[]
	 * @ORM\OneToMany(targetEntity="OnderverenigingsLid", mappedBy="groep")
	 * @ORM\OrderBy({"lid_sinds"="ASC"})
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
