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
 #[ORM\Column(type: 'enumOnderverenigingStatus')]
 #[Serializer\Groups('datatable')]
 public $onderverenigingStatus;

	public function getUrl(): string
	{
		return '/groepen/onderverenigingen/' . $this->id;
	}
}
