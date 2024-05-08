<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\repository\groepen\BesturenRepository;
use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Bestuur.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[ORM\Entity(repositoryClass: BesturenRepository::class)]
class Bestuur extends Groep implements HeeftMoment
{
	use GroepMoment;
	/**
  * Bestuurstekst
  * @var string
  */
 #[ORM\Column(type: 'text')]
 #[Serializer\Groups('datatable')]
 public $bijbeltekst;

	public function getUrl(): string
	{
		return '/groepen/besturen/' . $this->id;
	}
}
