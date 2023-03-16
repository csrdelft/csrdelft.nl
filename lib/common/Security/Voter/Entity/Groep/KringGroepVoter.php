<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Kring;

class KringGroepVoter extends AbstractGroepVoter
{
	protected function getGroepType(): string
	{
		return Kring::class;
	}
}
