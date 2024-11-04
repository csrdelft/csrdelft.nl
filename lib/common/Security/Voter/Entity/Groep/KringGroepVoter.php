<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Kring;

class KringGroepVoter extends AbstractGroepVoter
{
	/**
	 * @return string
	 *
	 * @psalm-return Kring::class
	 */
	protected function getGroepType(): string
	{
		return Kring::class;
	}
}
