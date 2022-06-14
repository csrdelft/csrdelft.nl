<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\DataFixtures\Util\AccountFixtureUtil;
use CsrDelft\DataFixtures\Util\ProfielFixtureUtil;
use CsrDelft\entity\Geslacht;
use CsrDelft\entity\MenuItem;
use CsrDelft\entity\OntvangtContactueel;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\service\AccountService;
use CsrDelft\service\security\LoginService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as Faker;
use Symfony\Component\Uid\Uuid;

class AccountFixtures extends Fixture
{
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

	public function load(ObjectManager $manager)
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
		$this->setReference(self::UID_PUBCIE, $profielPubCie);
		$manager->persist($profielPubCie);
		$account = $this->accountService->maakAccount(self::UID_PUBCIE);
		$this->accountService->wijzigWachtwoord($account, 'stek open u voor mij!');
		$account->perm_role = AccessRole::PubCie;

		// Maak een bestuur

		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_PRAESES);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_ABACTIS);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_FISCUS);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_VICEABACTIS);
		$this->maakProfielEnAccount($manager, self::UID_BESTUUR_VICEPRAESES);

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

		$pubcieMenu = new MenuItem();
		$pubcieMenu->tekst = 'x101';
		$pubcieMenu->rechten_bekijken = 'x101';

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
	 * @param $perm_role
	 * @return Account
	 */
	private function maakAccount($uid, $perm_role): Account
	{
		$account = $this->accountService->maakAccount($uid);
		$account->perm_role = $perm_role;
		return $account;
	}

	/**
	 * @param ObjectManager $manager
	 * @param $uid
	 * @return void
	 */
	private function maakProfielEnAccount(ObjectManager $manager, $uid): void
	{
		$profielPraeses = ProfielFixtureUtil::maakProfiel($this->faker, $uid);
		$this->setReference($uid, $profielPraeses);
		$manager->persist($profielPraeses);
		$manager->persist($this->accountService->maakAccount($uid));
	}
}
