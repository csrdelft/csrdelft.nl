<?php

namespace Voters;

use CsrDelft\DataFixtures\AccountFixtures;
use CsrDelft\tests\AbstractVoterTestCase;

class ExpressionVoterTest extends AbstractVoterTestCase
{
	public function testEnExpressie(): void
	{
		$lidToken = $this->getToken(AccountFixtures::UID_LID_MAN);
		$bestuurToken = $this->getToken(AccountFixtures::UID_BESTUUR_PRAESES);

		$this->assertToegang($bestuurToken, 'ROLE_LOGGED_IN'); // true
		$this->assertToegang($bestuurToken, 'ROLE_BESTUUR'); // true
		$this->assertToegang($bestuurToken, 'ROLE_LOGGED_IN+ROLE_BESTUUR'); // true and true

		$this->assertToegang($lidToken, 'ROLE_LOGGED_IN'); // true
		$this->assertGeenToegang($lidToken, 'ROLE_BESTUUR'); // false
		$this->assertGeenToegang($lidToken, 'ROLE_LOGGED_IN+ROLE_BESTUUR'); // true and false
	}

	public function testOrPrimaryExpressie(): void
	{
		$lidToken = $this->getToken(AccountFixtures::UID_LID_MAN);

		$this->assertToegang($lidToken, 'ROLE_LOGGED_IN'); // true
		$this->assertGeenToegang($lidToken, 'ROLE_BESTUUR'); // false
		$this->assertToegang($lidToken, 'ROLE_LOGGED_IN,ROLE_BESTUUR'); // true or false
	}

	public function testOrSecondaryExpressie(): void
	{
		$lidToken = $this->getToken(AccountFixtures::UID_LID_MAN);

		$this->assertToegang($lidToken, 'ROLE_LOGGED_IN'); // true
		$this->assertGeenToegang($lidToken, 'ROLE_BESTUUR'); // false
		$this->assertToegang($lidToken, 'ROLE_LOGGED_IN|ROLE_BESTUUR'); // true or false
	}
}
