<?php

namespace Voters;

use CsrDelft\DataFixtures\AccountFixtures;
use CsrDelft\tests\AbstractVoterTestCase;

class UidVoterTest extends AbstractVoterTestCase
{
	public function testUidVoter()
	{
		$lid = $this->getToken(AccountFixtures::UID_PUBCIE);

		$this->assertToegang($lid, AccountFixtures::UID_PUBCIE);
		$this->assertGeenToegang($lid, AccountFixtures::UID_LID_VROUW);
	}
}
