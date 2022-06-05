<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\entity\Geslacht;
use CsrDelft\entity\MenuItem;
use CsrDelft\entity\OntvangtContactueel;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\ProfielLogTextEntry;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccountService;
use CsrDelft\service\security\LoginService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class AccountFixtures extends Fixture
{
	/**
	 * @var AccountService
	 */
	private $accountService;

	public function __construct(AccountService $accountService)
	{
		$this->accountService = $accountService;
	}

	public function load(ObjectManager $manager)
	{
		$externProfiel = new Profiel();
		$externProfiel->uid = LoginService::UID_EXTERN;
		$externProfiel->nickname = 'nobody';
		$externProfiel->voornaam = 'Niet';
		$externProfiel->achternaam = 'ingelogd';
		$externProfiel->voorletters = 'Niet';
		$externProfiel->land = 'Nederland';
		$externProfiel->geslacht = Geslacht::Man();
		$externProfiel->status = LidStatus::Nobody;
		$externProfiel->ontvangtcontactueel = OntvangtContactueel::Nee();
		$externProfiel->gebdatum = date_create_immutable('1960-01-01');
		$externProfiel->lengte = 0;
		$externProfiel->adres = '';
		$externProfiel->postcode = '';
		$externProfiel->woonplaats = '';
		$externProfiel->email = '';
		$externProfiel->lidjaar = 0;
		$externProfiel->changelog = [
			new ProfielLogTextEntry('Aangemaakt door fixtures'),
		];

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

		$pubcieProfiel = new Profiel();
		$pubcieProfiel->uid = 'x101';
		$pubcieProfiel->nickname = 'pubcie';
		$pubcieProfiel->voornaam = 'Pub';
		$pubcieProfiel->achternaam = 'Cie';
		$pubcieProfiel->voorletters = 'P.';
		$pubcieProfiel->land = 'Nederland';
		$pubcieProfiel->geslacht = Geslacht::Man();
		$pubcieProfiel->status = LidStatus::Lid;
		$pubcieProfiel->ontvangtcontactueel = OntvangtContactueel::Nee();
		$pubcieProfiel->gebdatum = date_create_immutable('1960-01-01');
		$pubcieProfiel->lengte = 0;
		$pubcieProfiel->adres = '';
		$pubcieProfiel->postcode = '';
		$pubcieProfiel->woonplaats = '';
		$pubcieProfiel->email = '';
		$pubcieProfiel->lidjaar = 0;
		$pubcieProfiel->changelog = [
			new ProfielLogTextEntry('Aangemaakt door fixtures'),
		];

		$manager->persist($pubcieProfiel);

		$account = $this->accountService->maakAccount('x101');

		$this->accountService->wijzigWachtwoord($account, 'stek open u voor mij!');

		$account->perm_role = AccessRole::PubCie;

		$pubcieMenu = new MenuItem();
		$pubcieMenu->tekst = 'x101';
		$pubcieMenu->rechten_bekijken = 'x101';

		$manager->flush();
	}
}
