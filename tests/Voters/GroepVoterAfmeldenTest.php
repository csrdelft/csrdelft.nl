<?php

namespace Voters;

use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\DataFixtures\AccountFixtures;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\tests\AbstractVoterTestCase;

class GroepVoterAfmeldenTest extends AbstractVoterTestCase
{
	public function testAfmelden(): void
	{
		$activiteit = new Activiteit();
		$activiteit->setAanmeldenTot(date_create_immutable('+3 days'));
		$activiteit->setAanmeldenVanaf(date_create_immutable('-2 days'));

		$groepLid = new GroepLid();
		$token = $this->getToken(AccountFixtures::UID_LID_MAN);
		$groepLid->setProfiel($token->getUser()->profiel);
		$activiteit->getLeden()->add($groepLid);

		$this->assertToegang($token, AbstractGroepVoter::AFMELDEN, $activiteit);
	}

	public function testAfmelden_nietAangemeld(): void
	{
		$activiteit = new Activiteit();
		$token = $this->getToken(AccountFixtures::UID_LID_MAN);

		$this->assertGeenToegang($token, AbstractGroepVoter::AFMELDEN, $activiteit);
	}

	public function testAfmelden_afmeldenTot(): void
	{
		$activiteit = new Activiteit();
		$activiteit->setAanmeldenTot(date_create_immutable('+3 days'));
		$activiteit->setAanmeldenVanaf(date_create_immutable('-2 days'));

		$activiteit->setAfmeldenTot(date_create_immutable('-1 days'));

		$groepLid = new GroepLid();
		$token = $this->getToken(AccountFixtures::UID_LID_MAN);
		$groepLid->setProfiel($token->getUser()->profiel);
		$activiteit->getLeden()->add($groepLid);

		$this->assertGeenToegang($token, AbstractGroepVoter::AFMELDEN, $activiteit);

		$activiteit->setAfmeldenTot(date_create_immutable('+1 days'));

		$this->assertToegang($token, AbstractGroepVoter::AFMELDEN, $activiteit);
	}

	public function testAfmelden_aanmeldRechten(): void
	{
		$activiteit = new Activiteit();
		$activiteit->setAanmeldenTot(date_create_immutable('+3 days'));
		$activiteit->setAanmeldenVanaf(date_create_immutable('-2 days'));
		$activiteit->setAanmeldRechten(AccountFixtures::UID_LID_VROUW);

		$groepLid = new GroepLid();
		$token = $this->getToken(AccountFixtures::UID_LID_MAN);
		$groepLid->setProfiel($token->getUser()->profiel);
		$activiteit->getLeden()->add($groepLid);

		$this->assertGeenToegang($token, AbstractGroepVoter::AFMELDEN, $activiteit);
	}
}
