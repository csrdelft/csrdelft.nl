<?php

namespace Voters;

use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\DataFixtures\AccountFixtures;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\tests\AbstractVoterTestCase;

class GroepVoterAanmeldenTest extends AbstractVoterTestCase
{
	public function testActiviteitAanmelden_aanmeldLimiet()
	{
		$activiteit = new Activiteit();
		$activiteit->aanmeldLimiet = 2;
		$activiteit->setAanmeldenTot(date_create_immutable('+3 days'));
		$activiteit->setAanmeldenVanaf(date_create_immutable('-2 days'));

		$activiteit->getLeden()->add(new GroepLid());
		$lid2 = new GroepLid();
		$activiteit->getLeden()->add($lid2);

		$this->assertGeenToegang(
			$this->getToken(AccountFixtures::UID_LID_MAN),
			AbstractGroepVoter::AANMELDEN,
			$activiteit
		);

		$activiteit->getLeden()->removeElement($lid2);

		$this->assertToegang(
			$this->getToken(AccountFixtures::UID_LID_MAN),
			AbstractGroepVoter::AANMELDEN,
			$activiteit
		);
	}

	/**
	 * Mag niet aanmelden als aanmeldenVanaf in de toekomst is.
	 */
	public function testActiviteitAanmelden_aanmeldenVanaf()
	{
		$activiteit = new Activiteit();
		$activiteit->aanmeldLimiet = 2;
		$activiteit->setAanmeldenTot(date_create_immutable('+3 days'));
		$activiteit->setAanmeldenVanaf(date_create_immutable('+2 days'));

		$this->assertGeenToegang(
			$this->getToken(AccountFixtures::UID_LID_MAN),
			AbstractGroepVoter::AANMELDEN,
			$activiteit
		);
	}

	/**
	 * Mag niet aanmelden als aanmeldenTot in het verleden is.
	 */
	public function testActiviteitAanmelden_aanmeldenTot()
	{
		$activiteit = new Activiteit();
		$activiteit->aanmeldLimiet = 2;
		$activiteit->setAanmeldenTot(date_create_immutable('-1 days'));
		$activiteit->setAanmeldenVanaf(date_create_immutable('-2 days'));

		$this->assertGeenToegang(
			$this->getToken(AccountFixtures::UID_LID_MAN),
			AbstractGroepVoter::AANMELDEN,
			$activiteit
		);
	}

	/**
	 * Mag alleen aanmelden als je aanmeldRechten hebt.
	 */
	public function testActiviteitAanmelden_aanmeldRechten()
	{
		$activiteit = new Activiteit();
		$activiteit->aanmeldLimiet = 2;
		$activiteit->setAanmeldenTot(date_create_immutable('+3 days'));
		$activiteit->setAanmeldenVanaf(date_create_immutable('-2 days'));
		$activiteit->setAanmeldRechten(AccountFixtures::UID_LID_VROUW);

		$this->assertGeenToegang(
			$this->getToken(AccountFixtures::UID_LID_MAN),
			AbstractGroepVoter::AANMELDEN,
			$activiteit
		);
		$this->assertToegang(
			$this->getToken(AccountFixtures::UID_LID_VROUW),
			AbstractGroepVoter::AANMELDEN,
			$activiteit
		);
		$this->assertGeenToegang(
			$this->getToken(AccountFixtures::UID_PUBCIE),
			AbstractGroepVoter::AANMELDEN,
			$activiteit
		);
	}

	/**
	 * Mag niet aanmelden als al aangemeld.
	 */
	public function testActiviteitAanmelden_alAangemeld()
	{
		$activiteit = new Activiteit();
		$activiteit->aanmeldLimiet = 3;
		$activiteit->setAanmeldenTot(date_create_immutable('+3 days'));
		$activiteit->setAanmeldenVanaf(date_create_immutable('-2 days'));

		$token = $this->getToken(AccountFixtures::UID_LID_VROUW);
		$this->assertToegang($token, AbstractGroepVoter::AANMELDEN, $activiteit);

		$groepLid = new GroepLid();
		$groepLid->setProfiel($token->getUser()->profiel);
		$activiteit->getLeden()->add($groepLid);

		// Al aangemeld
		$this->assertGeenToegang(
			$token,
			AbstractGroepVoter::AANMELDEN,
			$activiteit
		);
	}
}
