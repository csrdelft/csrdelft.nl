<?php

namespace Voters;

use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\DataFixtures\AccountFixtures;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\Commissie;
use CsrDelft\tests\AbstractVoterTestCase;

class GroepVoterAanmakenTest extends AbstractVoterTestCase
{
	public function testCommissieAanmaken(): void
	{
		$commissie = new Commissie();
		// Lid mag geen commissie maken
		$this->assertGeenToegang(
			$this->getToken(AccountFixtures::UID_LID_MAN),
			AbstractGroepVoter::AANMAKEN,
			$commissie
		);
		// Bestuur mag wel commissie maken
		$this->assertToegang(
			$this->getToken(AccountFixtures::UID_BESTUUR_FISCUS),
			AbstractGroepVoter::AANMAKEN,
			$commissie
		);
	}

	public function testActiviteitAanmaken(): void
	{
		$activiteit = new Activiteit();
		// Lid mag activiteit maken
		$this->assertToegang(
			$this->getToken(AccountFixtures::UID_LID_MAN),
			AbstractGroepVoter::AANMAKEN,
			$activiteit
		);
		// Bestuur mag activiteit maken
		$this->assertToegang(
			$this->getToken(AccountFixtures::UID_BESTUUR_FISCUS),
			AbstractGroepVoter::AANMAKEN,
			$activiteit
		);
	}
}
