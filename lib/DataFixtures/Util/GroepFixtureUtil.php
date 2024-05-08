<?php

namespace CsrDelft\DataFixtures\Util;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\Persistence\ObjectManager;

class GroepFixtureUtil
{
	public static function maakGroepLid(ObjectManager $manager, Groep $groep, Profiel $profiel, string $opmerking = null): void {
		$groepLid = new GroepLid();
		$groepLid->uid = $profiel->uid;
		$groepLid->profiel = $profiel;
		$groepLid->groep = $groep;
		$groepLid->groepId = $groep->id;
		$groepLid->opmerking = $opmerking;
		$groepLid->lidSinds = date_create_immutable();
		$groepLid->doorProfiel = $profiel;
		$groepLid->doorUid = $groepLid->doorProfiel->uid;

		$manager->persist($groepLid);
	}
}
