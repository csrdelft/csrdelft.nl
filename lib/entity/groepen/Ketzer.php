<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldMoment;
use Doctrine\ORM\Mapping as ORM;

/**
 * Ketzer.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een ketzer is een aanmeldbare groep.
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\groepen\KetzersRepository::class
	)
]
class Ketzer extends Groep implements HeeftAanmeldLimiet, HeeftAanmeldMoment
{
	use GroepAanmeldMoment;
	use GroepAanmeldLimiet;

	public function getUrl()
	{
		return '/groepen/ketzers/' . $this->id;
	}

	public function getAanmeldLimiet()
	{
		return $this->aanmeldLimiet;
	}
}
