<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\repository\groepen\WerkgroepenRepository;
use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use Doctrine\ORM\Mapping as ORM;

/**
 * Werkgroep.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[ORM\Entity(repositoryClass: WerkgroepenRepository::class)]
class Werkgroep extends Groep implements HeeftMoment
{
	use GroepMoment;

	public function getUrl()
	{
		return '/groepen/werkgroepen/' . $this->id;
	}
}
