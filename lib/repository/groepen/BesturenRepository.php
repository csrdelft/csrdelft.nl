<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Bestuur;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\repository\GroepRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class BesturenRepository extends GroepRepository
{
	public function getEntityClassName()
	{
		return Bestuur::class;
	}

	public function nieuw($soort = null)
	{
		/** @var Bestuur $bestuur */
		$bestuur = parent::nieuw();
		$bestuur->bijbeltekst = '';
		return $bestuur;
	}
}
