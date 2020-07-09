<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Bestuur.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\BesturenRepository")
 * @ORM\Table("besturen", indexes={
 *   @ORM\Index(name="status", columns={"status"}),
 *   @ORM\Index(name="begin_moment", columns={"begin_moment"}),
 *   @ORM\Index(name="familie", columns={"familie"})
 * })
 */
class Bestuur extends AbstractGroep {
	/**
	 * @var BestuursLid[]
	 * @ORM\OneToMany(targetEntity="BestuursLid", mappedBy="groep")
	 * @ORM\OrderBy({"lid_sinds"="ASC"})
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
	 * @Serializer\Groups("datatable")
	 */
	public $bijbeltekst;

	public function getUrl() {
		return '/groepen/besturen/' . $this->id;
	}

}
