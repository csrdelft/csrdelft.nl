<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Commissie;

class CommissieGroepVoter extends AbstractGroepVoter
{
	protected function getGroepType(): string
	{
		return Commissie::class;
	}
}
