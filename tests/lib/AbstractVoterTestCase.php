<?php

namespace CsrDelft\tests;

use CsrDelft\entity\security\Account;
use CsrDelft\repository\security\AccountRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;

/**
 * Test makkelijk toegangstrings
 *
 * @see AbstractVoterTestCase::assertToegang() Check of een token toegang heeft
 * @see AbstractVoterTestCase::assertGeenToegang() Check of een token geen toegang heeft
 * @see AbstractVoterTestCase::getToken() Haal een token op voor een specifieke gebuiker (faalt als de gebruiker niet bestaat)
 */
abstract class AbstractVoterTestCase extends CsrTestCase
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

	protected function assertToegang(
		UsernamePasswordToken $token,
		$permissie,
		$subject = null
	) {
		$this->assertTrue($this->adm->decide($token, [$permissie], $subject));
	}

	protected function assertGeenToegang(
		UsernamePasswordToken $token,
		$permissie,
		$subject = null
	) {
		$this->assertFalse($this->adm->decide($token, [$permissie], $subject));
	}

	/**
	 * @param $uid
	 * @return UsernamePasswordToken
	 */
	protected function getToken(string $uid): UsernamePasswordToken
	{
		$account = $this->accountRepository->find($uid);
		return new UsernamePasswordToken($account, 'main', $account->getRoles());
	}
}
