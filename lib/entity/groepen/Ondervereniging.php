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
 */
class Ondervereniging extends Groep {
	use GroepMoment;
	/**
	 * (Adspirant-)Ondervereniging
	 * @var OnderverenigingStatus
	 * @ORM\Column(type="enumOnderverenigingStatus")
	 * @Serializer\Groups("datatable")
	 */
	public $onderverenigingStatus;

	public function getUrl() {
		return '/groepen/onderverenigingen/' . $this->id;
	}
}
