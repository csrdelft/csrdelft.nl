<?php

namespace Voters;

use CsrDelft\DataFixtures\AccountFixtures;
use CsrDelft\tests\AbstractVoterTestCase;

class GeslachtVoterTest extends AbstractVoterTestCase
{
	public function testGeslacht()
	{
		$man = $this->getToken(AccountFixtures::UID_LID_MAN);
		$vrouw = $this->getToken(AccountFixtures::UID_LID_VROUW);

		$this->assertToegang($man, 'geslacht:m');
		$this->assertGeenToegang($vrouw, 'geslacht:m');

		$this->assertToegang($vrouw, 'geslacht:v');
		$this->assertGeenToegang($man, 'geslacht:v');
	}
}
