<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Kring.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\KringenRepository")
 * @ORM\Table("kringen", indexes={
 *   @ORM\Index(name="begin_moment", columns={"begin_moment"}),
 *   @ORM\Index(name="kring_nummer", columns={"kring_nummer"}),
 *   @ORM\Index(name="familie", columns={"familie"}),
 *   @ORM\Index(name="verticale", columns={"verticale"}),
 * })
 */
class Kring extends AbstractGroep {
	/**
	 * Verticaleletter
	 * @var string
	 * @ORM\Column(type="string", length=1, options={"fixed"=true})
	 * @Serializer\Groups({"datatable", "log"})
	 */
	public $verticale;
	/**
	 * Kringnummer
	 * @var int
	 * @ORM\Column(type="integer")
	 * @Serializer\Groups({"datatable", "log"})
	 */
	public $kring_nummer;

	/**
	 * @var KringLid[]
	 * @ORM\OneToMany(targetEntity="KringLid", mappedBy="groep")
	 * @ORM\OrderBy({"uid"="ASC"})
	 */
	public $leden;

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
