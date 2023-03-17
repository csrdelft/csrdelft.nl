<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftAanmeldRechten;
use Doctrine\ORM\Mapping as ORM;

/**
 * RechtenGroep.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een groep beperkt voor rechten.
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\RechtenGroepenRepository")
 */
class RechtenGroep extends Groep implements HeeftAanmeldRechten
{
	use GroepAanmeldRechten;

	public function getUrl()
	{
		return '/groepen/overig/' . $this->id;
	}
}
