<?php

namespace Voters;

use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\DataFixtures\AccountFixtures;
use CsrDelft\entity\groepen\Commissie;
use CsrDelft\tests\AbstractVoterTestCase;

class GroepVoterTest extends AbstractVoterTestCase
{
	public function testCommissieAanmaken()
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
}
