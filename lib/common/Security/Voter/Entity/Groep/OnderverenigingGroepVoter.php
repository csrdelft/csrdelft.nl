<?php

namespace CsrDelft\common\Security\Voter\Entity\Groep;

use CsrDelft\entity\groepen\Ondervereniging;

class OnderverenigingGroepVoter extends AbstractGroepVoter
{
	/**
	 * @return string
	 *
	 * @psalm-return Ondervereniging::class
	 */
	protected function getGroepType(): string
	{
		return Ondervereniging::class;
	}
}
