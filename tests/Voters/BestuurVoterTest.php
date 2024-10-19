<?php

namespace Voters;

use CsrDelft\DataFixtures\AccountFixtures;
use CsrDelft\tests\AbstractVoterTestCase;

class BestuurVoterTest extends AbstractVoterTestCase
{
	public function testBestuurHtPraeses(): void
	{
		$htPraesesToken = $this->getToken(AccountFixtures::UID_BESTUUR_PRAESES);

		$this->assertToegang($htPraesesToken, 'bestuur:ht');
		$this->assertToegang($htPraesesToken, 'bestuur');
		$this->assertToegang($htPraesesToken, 'bestuur:ht:praeses');
		$this->assertToegang($htPraesesToken, 'bestuur:praeses');
		$this->assertGeenToegang($htPraesesToken, 'bestuur:abactis');
		$this->assertGeenToegang($htPraesesToken, 'bestuur:ot');
		$this->assertGeenToegang($htPraesesToken, 'bestuur:ft');
		$this->assertGeenToegang($htPraesesToken, 'bestuur:sjaars');
		$this->assertGeenToegang($htPraesesToken, 'bestuur:ht:sjaars');
	}

	public function testBestuurHtAbactis(): void
	{
		$htAbactisToken = $this->getToken(AccountFixtures::UID_BESTUUR_ABACTIS);

		$this->assertToegang($htAbactisToken, 'bestuur:ht');
		$this->assertToegang($htAbactisToken, 'bestuur');
		$this->assertToegang($htAbactisToken, 'bestuur:ht:abactis');
		$this->assertToegang($htAbactisToken, 'bestuur:abactis');
		$this->assertGeenToegang($htAbactisToken, 'bestuur:praeses');
		$this->assertGeenToegang($htAbactisToken, 'bestuur:ot');
		$this->assertGeenToegang($htAbactisToken, 'bestuur:ft');
	}

	public function testBestuurOtPraeses(): void
	{
		$otPraesesToken = $this->getToken(AccountFixtures::UID_BESTUUR_OT_PRAESES);

		$this->assertToegang($otPraesesToken, 'bestuur:ot');
		$this->assertGeenToegang($otPraesesToken, 'bestuur');
		$this->assertToegang($otPraesesToken, 'bestuur:ot:praeses');
		$this->assertGeenToegang($otPraesesToken, 'bestuur:ot:abactis');
		$this->assertGeenToegang($otPraesesToken, 'bestuur:abactis');
		$this->assertGeenToegang($otPraesesToken, 'bestuur:praeses');
		$this->assertGeenToegang($otPraesesToken, 'bestuur:ht');
		$this->assertGeenToegang($otPraesesToken, 'bestuur:ft');
	}

	public function testBestuurFtPraeses(): void
	{
		$ftPraesesToken = $this->getToken(AccountFixtures::UID_BESTUUR_FT_PRAESES);

		$this->assertToegang($ftPraesesToken, 'bestuur:ft');
		$this->assertGeenToegang($ftPraesesToken, 'bestuur');
		$this->assertToegang($ftPraesesToken, 'bestuur:ft:praeses');
		$this->assertGeenToegang($ftPraesesToken, 'bestuur:ft:abactis');
		$this->assertGeenToegang($ftPraesesToken, 'bestuur:abactis');
		$this->assertGeenToegang($ftPraesesToken, 'bestuur:praeses');
		$this->assertGeenToegang($ftPraesesToken, 'bestuur:ht');
		$this->assertGeenToegang($ftPraesesToken, 'bestuur:ot');
	}
}
