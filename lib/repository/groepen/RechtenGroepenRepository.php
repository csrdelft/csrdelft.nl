<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\RechtenGroep;
use CsrDelft\repository\GroepLidRepository;
use CsrDelft\repository\GroepRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

class RechtenGroepenRepository extends GroepRepository
{


	public function getEntityClassName()
	{
		return RechtenGroep::class;
	}

	public function nieuw($soort = null)
	{
		/** @var RechtenGroep $groep */
		$groep = parent::nieuw();
		$groep->rechtenAanmelden = P_LEDEN_MOD;
		return $groep;
	}

	public static function getNaam()
	{
		return 'overig';
	}
}
