<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\repository\groepen\OnderverenigingenRepository;
use CsrDelft\entity\groepen\enum\OnderverenigingStatus;
use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Ondervereniging.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[ORM\Entity(repositoryClass: OnderverenigingenRepository::class)]
class Ondervereniging extends Groep implements HeeftMoment
{
	use GroepMoment;
	/**
	 * (Adspirant-)Ondervereniging
	 * @var OnderverenigingStatus
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'enumOnderverenigingStatus')]
	public $onderverenigingStatus;

	public function getUrl()
	{
		return '/groepen/onderverenigingen/' . $this->id;
	}
}
