<?php

namespace Voters;

use CsrDelft\DataFixtures\AccountFixtures;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\tests\CsrTestCase;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;

class BestuurVoterTest extends CsrTestCase
{
	/**
	 * @var EntityManager
	 */
	private $em;
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var AccessDecisionManager
	 */
	private $adm;

	public function setUp(): void
	{
		parent::setUp();

		$this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
		$this->accountRepository = $this->em->getRepository(Account::class);
		$this->adm = $this->getContainer()->get('security.access.decision_manager');
	}

	public function testBestuurHtPraeses()
	{
		$htPraesesToken = $this->getToken(AccountFixtures::UID_BESTUUR_PRAESES);

		$this->assertTrue($this->adm->decide($htPraesesToken, ['bestuur:ht']));
		$this->assertTrue($this->adm->decide($htPraesesToken, ['bestuur']));
		$this->assertTrue(
			$this->adm->decide($htPraesesToken, ['bestuur:ht:praeses'])
		);
		$this->assertTrue($this->adm->decide($htPraesesToken, ['bestuur:praeses']));
		$this->assertFalse(
			$this->adm->decide($htPraesesToken, ['bestuur:abactis'])
		);
		$this->assertFalse($this->adm->decide($htPraesesToken, ['bestuur:ot']));
		$this->assertFalse($this->adm->decide($htPraesesToken, ['bestuur:ft']));
		$this->assertFalse($this->adm->decide($htPraesesToken, ['bestuur:sjaars']));
		$this->assertFalse(
			$this->adm->decide($htPraesesToken, ['bestuur:ht:sjaars'])
		);
	}

	public function testBestuurHtAbactis()
	{
		$htAbactisToken = $this->getToken(AccountFixtures::UID_BESTUUR_ABACTIS);

		$this->assertTrue($this->adm->decide($htAbactisToken, ['bestuur:ht']));
		$this->assertTrue($this->adm->decide($htAbactisToken, ['bestuur']));
		$this->assertTrue(
			$this->adm->decide($htAbactisToken, ['bestuur:ht:abactis'])
		);
		$this->assertTrue($this->adm->decide($htAbactisToken, ['bestuur:abactis']));
		$this->assertFalse(
			$this->adm->decide($htAbactisToken, ['bestuur:praeses'])
		);
		$this->assertFalse($this->adm->decide($htAbactisToken, ['bestuur:ot']));
		$this->assertFalse($this->adm->decide($htAbactisToken, ['bestuur:ft']));
	}

	public function testBestuurOtPraeses()
	{
		$otPraesesToken = $this->getToken(AccountFixtures::UID_BESTUUR_OT_PRAESES);

		$this->assertTrue($this->adm->decide($otPraesesToken, ['bestuur:ot']));
		$this->assertFalse($this->adm->decide($otPraesesToken, ['bestuur']));
		$this->assertTrue(
			$this->adm->decide($otPraesesToken, ['bestuur:ot:praeses'])
		);
		$this->assertFalse(
			$this->adm->decide($otPraesesToken, ['bestuur:ot:abactis'])
		);
		$this->assertFalse(
			$this->adm->decide($otPraesesToken, ['bestuur:abactis'])
		);
		$this->assertFalse(
			$this->adm->decide($otPraesesToken, ['bestuur:praeses'])
		);
		$this->assertFalse($this->adm->decide($otPraesesToken, ['bestuur:ht']));
		$this->assertFalse($this->adm->decide($otPraesesToken, ['bestuur:ft']));
	}

	public function testBestuurFtPraeses()
	{
		$ftPraesesToken = $this->getToken(AccountFixtures::UID_BESTUUR_FT_PRAESES);

		$this->assertTrue($this->adm->decide($ftPraesesToken, ['bestuur:ft']));
		$this->assertFalse($this->adm->decide($ftPraesesToken, ['bestuur']));
		$this->assertTrue(
			$this->adm->decide($ftPraesesToken, ['bestuur:ft:praeses'])
		);
		$this->assertFalse(
			$this->adm->decide($ftPraesesToken, ['bestuur:ft:abactis'])
		);
		$this->assertFalse(
			$this->adm->decide($ftPraesesToken, ['bestuur:abactis'])
		);
		$this->assertFalse(
			$this->adm->decide($ftPraesesToken, ['bestuur:praeses'])
		);
		$this->assertFalse($this->adm->decide($ftPraesesToken, ['bestuur:ht']));
		$this->assertFalse($this->adm->decide($ftPraesesToken, ['bestuur:ot']));
	}

	/**
	 * @param $uid
	 * @return UsernamePasswordToken
	 */
	private function getToken(string $uid): UsernamePasswordToken
	{
		$account = $this->accountRepository->find($uid);
		return new UsernamePasswordToken($account, 'main', $account->getRoles());
	}
}
