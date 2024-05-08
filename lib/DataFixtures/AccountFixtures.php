<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\DataFixtures\Util\ProfielFixtureUtil;
use CsrDelft\entity\Geslacht;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\service\AccountService;
use CsrDelft\service\security\LoginService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as Faker;
use Faker\Generator;
use Symfony\Component\Uid\Uuid;

class AccountFixtures extends Fixture
{
	/**
	 * Gebruik deze consts om naar accounts/profielen te verwijzen in tests.
	 */
	const UID_BESTUUR_PRAESES = 'x001';
	const UID_BESTUUR_ABACTIS = 'x002';
	const UID_BESTUUR_FISCUS = 'x003';
	const UID_BESTUUR_VICEABACTIS = 'x004';
	const UID_BESTUUR_VICEPRAESES = 'x005';
	const UID_BESTUUR_OT_PRAESES = 'x006';
	const UID_BESTUUR_OT_ABACTIS = 'x007';
	const UID_BESTUUR_OT_FISCUS = 'x008';
	const UID_BESTUUR_OT_VICEABACTIS = 'x009';
	const UID_BESTUUR_OT_VICEPRAESES = 'x010';
	const UID_BESTUUR_FT_PRAESES = 'x011';
	const UID_BESTUUR_FT_ABACTIS = 'x012';
	const UID_BESTUUR_FT_FISCUS = 'x013';
	const UID_BESTUUR_FT_VICEABACTIS = 'x014';
	const UID_BESTUUR_FT_VICEPRAESES = 'x015';
	const UID_LID_MAN = 'x016';
	const UID_LID_VROUW = 'x017';
	const UID_SOCCIE_PRAESES = 'x018';
	const UID_SOCCIE_FISCUS = 'x019';
	const UID_PUBCIE = 'x101';

	/**
	 * @var AccountService
	 */
	private $accountService;
	/**
	 * @var \Faker\Generator
	 */
	private $faker;

	public function __construct(AccountService $accountService)
	{
		$this->accountService = $accountService;
		$this->faker = Faker::create('nl_NL');
	}

	public function load(ObjectManager $manager): void
	{
		$this->maakExternAccount($manager);

		// Maak PubCie account
		$profielPubCie = ProfielFixtureUtil::maakProfiel(
			$this->faker,
			self::UID_PUBCIE,
			'pubcie',
			'Pub',
			'Cie',
			'P.'
		);
		$profielPubCie->email = 'test-mail@csrdelft.nl';
		$profielPubCie->sec_email = 'test-mail2@csrdelft.nl';
		$this->setReference(self::UID_PUBCIE, $profielPubCie);
		$manager->persist($profielPubCie);
		$account = $this->accountService->maakAccount(self::UID_PUBCIE);
		$this->accountService->wijzigWachtwoord($account, 'stek open u voor mij!');
		$account->perm_role = AccessRole::PubCie;

		// Maak een bestuur
		$this->maakProfielEnAccount(
			$manager,
			self::UID_BESTUUR_PRAESES,
			AccessRole::Bestuur
		);
		$this->maakProfielEnAccount(
			$manager,
			self::UID_BESTUUR_ABACTIS,
			AccessRole::Bestuur
		);
		$this->maakProfielEnAccount(
			$manager,
			self::UID_BESTUUR_FISCUS,
			AccessRole::Bestuur
		);
		$this->maakProfielEnAccount(
			$manager,
			self::UID_BESTUUR_VICEABACTIS,
			AccessRole::Bestuur
		);
		$this->maakProfielEnAccount(
			$manager,
			self::UID_BESTUUR_VICEPRAESES,
			AccessRole::Bestuur
		);

		// Maak een o.t. bestuur
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_OT_PRAESES);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_OT_ABACTIS);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_OT_FISCUS);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_OT_VICEABACTIS);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_OT_VICEPRAESES);

		// Maak een f.t. bestuur
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_FT_PRAESES);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_FT_ABACTIS);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_FT_FISCUS);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_FT_VICEABACTIS);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_FT_VICEPRAESES);

		// Maak een doodgewoon lid
		$man = $this->maakProfielEnAccount($manager, self::UID_LID_MAN);
		$man->geslacht = Geslacht::Man();
		$vrouw = $this->maakProfielEnAccount($manager, self::UID_LID_VROUW);
		$vrouw->geslacht = Geslacht::Vrouw();

		$this->maakProfielEnAccount($manager, self::UID_SOCCIE_PRAESES);
		$this->maakProfielEnAccount(
			$manager,
			self::UID_SOCCIE_FISCUS,
			AccessRole::Fiscaat
		);

		$manager->flush();
	}

	/**
	 * @param ObjectManager $manager
	 * @return void
	 */
	private function maakExternAccount(ObjectManager $manager): void
	{
		$externProfiel = ProfielFixtureUtil::maakProfiel(
			$this->faker,
			LoginService::UID_EXTERN,
			'nobody',
			'Niet',
			'ingelogd'
		);
		$externProfiel->status = LidStatus::Nobody;
		$externProfiel->gebdatum = date_create_immutable('1960-01-01');

		$manager->persist($externProfiel);

		$externAccount = new Account();
		$externAccount->uuid = Uuid::v4();
		$externAccount->username = '';
		$externAccount->email = '';
		$externAccount->pass_hash = '';
		$externAccount->failed_login_attempts = 0;
		$externAccount->pass_since = date_create_immutable();
		$externAccount->uid = $externProfiel->uid;
		$externAccount->profiel = $externProfiel;
		$externAccount->perm_role = AccessRole::Nobody;

		$manager->persist($externAccount);
	}

	/**
	 * @param $uid
	 * @param $permRole
	 * @return Account
	 */
	private function maakAccount($uid, $permRole): Account
	{
		$account = $this->accountService->maakAccount($uid);
		$account->perm_role = $permRole;
		return $account;
	}

	/**
	 * @param ObjectManager $manager
	 * @param $uid
	 * @param string $permRole
	 * @return void
	 */
	private function maakProfielEnAccount(
		ObjectManager $manager,
		string $uid,
		string $permRole = AccessRole::Lid
	): Profiel {
		$profiel = ProfielFixtureUtil::maakProfiel($this->faker, $uid);
		$this->setReference($uid, $profiel);
		$manager->persist($profiel);
		$manager->persist($this->maakAccount($uid, $permRole));

		return $profiel;
	}
}
