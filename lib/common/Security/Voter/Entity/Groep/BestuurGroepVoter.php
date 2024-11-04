<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Bestuur;

class BestuurGroepVoter extends AbstractGroepVoter
{
	/**
	 * @return string
	 *
	 * @psalm-return Bestuur::class
	 */
	protected function getGroepType(): string
	{
		return Bestuur::class;
	}
}
