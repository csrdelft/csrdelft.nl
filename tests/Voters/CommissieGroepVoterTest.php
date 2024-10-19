<?php

namespace Voters;

use CsrDelft\DataFixtures\AccountFixtures;
use CsrDelft\tests\AbstractVoterTestCase;

class CommissieGroepVoterTest extends AbstractVoterTestCase
{
	public function testCommissieSoccie(): void
	{
		$socciePraeses = $this->getToken(AccountFixtures::UID_SOCCIE_PRAESES);

		$this->assertToegang($socciePraeses, 'commissie:soccie');
		$this->assertToegang($socciePraeses, 'commissie:soccie:praeses');
		$this->assertGeenToegang($socciePraeses, 'commissie:soccie:fiscus');
		$this->assertGeenToegang($socciePraeses, 'commissie:novcie:fiscus');
		$this->assertGeenToegang($socciePraeses, 'commissie:novcie');

		$pubcie = $this->getToken(AccountFixtures::UID_PUBCIE);

		$this->assertGeenToegang($pubcie, 'commissie:soccie');
	}
}
