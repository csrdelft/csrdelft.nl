<?php

namespace CsrDelft\service;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\entity\corvee\CorveeFunctie;
use CsrDelft\entity\corvee\CorveeKwalificatie;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\Bestuur;
use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\groepen\enum\CommissieFunctie;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\entity\groepen\Kring;
use CsrDelft\entity\groepen\Ondervereniging;
use CsrDelft\entity\groepen\RechtenGroep;
use CsrDelft\entity\groepen\Werkgroep;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;
use function preg_replace;
use function preg_replace as preg_replace1;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * RBAC met MAC en DAC implementatie.
 *
 * @see http://csrc.nist.gov/groups/SNS/rbac/faq.html
 */
class AccessService
{
	const PREFIX_ACTIVITEIT = 'ACTIVITEIT';
	const PREFIX_BESTUUR = 'BESTUUR';
	const PREFIX_COMMISSIE = 'COMMISSIE';
	const PREFIX_GROEP = 'GROEP';
	const PREFIX_KETZER = 'KETZER';
	const PREFIX_ONDERVERENIGING = 'ONDERVERENIGING';
	const PREFIX_WERKGROEP = 'WERKGROEP';
	const PREFIX_WOONOORD = 'WOONOORD';
	const PREFIX_VERTICALE = 'VERTICALE';
	const PREFIX_KRING = 'KRING';
	const PREFIX_GESLACHT = 'GESLACHT';
	const PREFIX_STATUS = 'STATUS';
	const PREFIX_LICHTING = 'LICHTING';
	const PREFIX_LIDJAAR = 'LIDJAAR';
	const PREFIX_OUDEREJAARS = 'OUDEREJAARS';
	const PREFIX_EERSTEJAARS = 'EERSTEJAARS';
	const PREFIX_MAALTIJD = 'MAALTIJD';
	const PREFIX_KWALIFICATIE = 'KWALIFICATIE';

	/**
	 * Standaard toegestane authenticatie methoden
	 * @var array
	 */
	private static $defaultAllowedAuthenticationMethods = [
		AuthenticationMethod::impersonate,
		AuthenticationMethod::temporary,
		AuthenticationMethod::cookie_token,
		AuthenticationMethod::password_login,
		AuthenticationMethod::recent_password_login,
		AuthenticationMethod::password_login_and_one_time_token,
	];

	/**
	 * Geldige prefixes voor rechten
	 * @var array
	 */
	private static $prefix = [
		self::PREFIX_ACTIVITEIT,
		self::PREFIX_BESTUUR,
		self::PREFIX_COMMISSIE,
		self::PREFIX_GROEP,
		self::PREFIX_KETZER,
		self::PREFIX_ONDERVERENIGING,
		self::PREFIX_WERKGROEP,
		self::PREFIX_WOONOORD,
		self::PREFIX_VERTICALE,
		self::PREFIX_KRING,
		self::PREFIX_GESLACHT,
		self::PREFIX_STATUS,
		self::PREFIX_LICHTING,
		self::PREFIX_LIDJAAR,
		self::PREFIX_OUDEREJAARS,
		self::PREFIX_EERSTEJAARS,
		self::PREFIX_MAALTIJD,
		self::PREFIX_KWALIFICATIE,
	];

	/**
	 * Permissies die we gebruiken om te vergelijken met de permissies van een gebruiker.
	 */
	private $permissions = [];

	/**
	 * @var CacheInterface
	 */
	private $cache;

	/**
	 * @var EntityManagerInterface
	 */
	private $em; /**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var AccessDecisionManagerInterface
	 */
	private $accessDecisionManager;

	/**
	 * @param CacheInterface $cache
	 * @param EntityManagerInterface $em
	 * @throws InvalidArgumentException
	 */
	public function __construct(
		CacheInterface $cache,
		EntityManagerInterface $em,
		Security $security,
		AccessDecisionManagerInterface $accessDecisionManager,
		AccountRepository $accountRepository
	) {
		$this->cache = $cache;
		$this->em = $em;
		$this->loadPermissions();
		$this->accountRepository = $accountRepository;
		$this->security = $security;
		$this->accessDecisionManager = $accessDecisionManager;
	}

	/**
	 * Hier staan de 'vaste' permissies, die gegeven worden door de PubCie.
	 * In tegenstelling tot de variabele permissies zoals lidmaatschap van een groep.
	 *
	 * READ = Rechten om het onderdeel in te zien
	 * POST = Rechten om iets toe te voegen
	 * MOD  = Moderate rechten, dus verwijderen enzo
	 *
	 * Let op: de rechten zijn cumulatief (bijv: 7=4+2+1, 3=2+1)
	 * als je hiervan afwijkt, kun je (bewust) niveau's uitsluiten (bijv 5=4+1, sluit 2 uit)
	 * de levels worden omgezet in een karakter met die ASCII waarde (dit zijn vaak niet-leesbare symbolen, bijv #8=backspace)
	 * elke karakter van een string representeert een onderdeel
	 *
	 * @throws InvalidArgumentException
	 */
	private function loadPermissions()
	{
		// see if cached
		$this->permissions = $this->cache->get(
			'permissions-' . getlastmod(),
			function () {
				// build permissions
				return [
					P_PUBLIC => $this->createPermStr(0, 0), // Iedereen op het Internet
					P_LOGGED_IN => $this->createPermStr(0, 1), // Eigen profiel raadplegen
					P_ADMIN => $this->createPermStr(0, 1 + 2), // Super-admin
					P_VERJAARDAGEN => $this->createPermStr(1, 1), // Verjaardagen van leden zien
					P_PROFIEL_EDIT => $this->createPermStr(1, 1 + 2), // Eigen gegevens aanpassen
					P_LEDEN_READ => $this->createPermStr(1, 1 + 2 + 4), // Gegevens van leden raadplegen
					P_OUDLEDEN_READ => $this->createPermStr(1, 1 + 2 + 4 + 8), // Gegevens van oudleden raadplegen
					P_LEDEN_MOD => $this->createPermStr(1, 1 + 2 + 4 + 8 + 16), // (Oud)ledengegevens aanpassen
					P_FORUM_READ => $this->createPermStr(2, 1), // Forum lezen
					P_FORUM_POST => $this->createPermStr(2, 1 + 2), // Berichten plaatsen op het forum en eigen berichten wijzigen
					P_FORUM_MOD => $this->createPermStr(2, 1 + 2 + 4), // Forum-moderator mag berichten van anderen wijzigen of verwijderen
					P_FORUM_BELANGRIJK => $this->createPermStr(2, 8), // Forum belangrijk (de)markeren  [[let op: niet cumulatief]]
					P_FORUM_ADMIN => $this->createPermStr(2, 16), // Forum-admin mag deel-fora aanmaken en rechten wijzigen  [[let op: niet cumulatief]]
					P_AGENDA_READ => $this->createPermStr(3, 1), // Agenda bekijken
					P_AGENDA_ADD => $this->createPermStr(3, 1 + 2), // Items toevoegen aan de agenda
					P_AGENDA_MOD => $this->createPermStr(3, 1 + 2 + 4), // Items beheren in de agenda
					P_DOCS_READ => $this->createPermStr(4, 1), // Documenten-rubriek lezen
					P_DOCS_POST => $this->createPermStr(4, 1 + 2), // Documenten verwijderen of erbij plaatsen
					P_DOCS_MOD => $this->createPermStr(4, 1 + 2 + 4), // Documenten aanpassen
					P_ALBUM_READ => $this->createPermStr(5, 1), // Foto-album bekijken
					P_ALBUM_DOWN => $this->createPermStr(5, 1 + 2), // Foto-album downloaden
					P_ALBUM_ADD => $this->createPermStr(5, 1 + 2 + 4), // Fotos uploaden en albums toevoegen
					P_ALBUM_MOD => $this->createPermStr(5, 1 + 2 + 4 + 8), // Foto-albums aanpassen
					P_ALBUM_DEL => $this->createPermStr(5, 1 + 2 + 4 + 8 + 16), // Fotos uit fotoalbum verwijderen
					P_BIEB_READ => $this->createPermStr(6, 1), // Bibliotheek lezen
					P_BIEB_EDIT => $this->createPermStr(6, 1 + 2), // Bibliotheek wijzigen
					P_BIEB_MOD => $this->createPermStr(6, 1 + 2 + 4), // Bibliotheek zowel wijzigen als lezen
					P_NEWS_POST => $this->createPermStr(7, 1), // Nieuws plaatsen en wijzigen van jezelf
					P_NEWS_MOD => $this->createPermStr(7, 1 + 2), // Nieuws-moderator mag berichten van anderen wijzigen of verwijderen
					P_NEWS_PUBLISH => $this->createPermStr(7, 1 + 2 + 4), // Nieuws publiceren en rechten bepalen
					P_MAAL_IK => $this->createPermStr(8, 1), // Jezelf aan en afmelden voor maaltijd en eigen abo wijzigen
					P_MAAL_MOD => $this->createPermStr(8, 1 + 2), // Maaltijden beheren (MaalCie P)
					P_MAAL_SALDI => $this->createPermStr(8, 1 + 2 + 4), // MaalCie saldo aanpassen van iedereen (MaalCie fiscus)
					P_CORVEE_IK => $this->createPermStr(9, 1), // Eigen voorkeuren aangeven voor corveetaken
					P_CORVEE_MOD => $this->createPermStr(9, 1 + 2), // Corveetaken beheren (CorveeCaesar)
					P_CORVEE_SCHED => $this->createPermStr(9, 1 + 2 + 4), // Automatische corvee-indeler beheren
					P_MAIL_POST => $this->createPermStr(10, 1), // Berichten aan de courant toevoegen
					P_MAIL_COMPOSE => $this->createPermStr(10, 1 + 2), // Alle berichtjes in de courant bewerken en volgorde wijzigen
					P_MAIL_SEND => $this->createPermStr(10, 1 + 2 + 4), // Courant verzenden
					P_PEILING_VOTE => $this->createPermStr(11, 1), // Stemmen op peilingen
					P_PEILING_EDIT => $this->createPermStr(11, 1 + 2), // Peilingen aanmaken en eigen peiling bewerken
					P_PEILING_MOD => $this->createPermStr(11, 1 + 2 + 4), // Peilingen aanmaken en verwijderen
					P_FISCAAT_READ => $this->createPermStr(12, 1), // Fiscale dingen inzien
					P_FISCAAT_MOD => $this->createPermStr(12, 1 + 2), // Fiscale bewerkingen maken
					P_ALBUM_PUBLIC_READ => $this->createPermStr(13, 1), // Publiek foto-album bekijken
					P_ALBUM_PUBLIC_DOWN => $this->createPermStr(13, 1 + 2), // Publiek foto-album downloaden
					P_ALBUM_PUBLIC_ADD => $this->createPermStr(13, 1 + 2 + 4), // Publieke fotos uploaden en publieke albums toevoegen
					P_ALBUM_PUBLIC_MOD => $this->createPermStr(13, 1 + 2 + 4 + 8), // Publiek foto-albums aanpassen
					P_ALBUM_PUBLIC_DEL => $this->createPermStr(13, 1 + 2 + 4 + 8 + 16), // Fotos uit publiek fotoalbum verwijderen
				];
			}
		);
	}

	/**
	 * Create permission string with character which has ascii value of request level.
	 *
	 * @param int $onderdeelnummer starts at zero
	 * @param int $level permissiewaarde
	 * @return string permission string
	 */
	private function createPermStr($onderdeelnummer, $level)
	{
		$nulperm = str_repeat(chr(0), 15);
		return substr_replace($nulperm, chr($level), $onderdeelnummer, 1);
	}

	public function checkAuthenticationMethod(
		$allowedAuthenticationMethods = null
	) {
		$method = ContainerFacade::getContainer()
			->get(LoginService::class)
			->getAuthenticationMethod();
		if ($allowedAuthenticationMethods == null) {
			$allowedAuthenticationMethods =
				self::$defaultAllowedAuthenticationMethods;
		}
		// Als de methode niet toegestaan is testen we met de permissies van niet-ingelogd
		if (!in_array($method, $allowedAuthenticationMethods)) {
			return false;
		}

		return true;
	}

	/**
	 * @param UserInterface|null $subject Het lid dat de gevraagde permissies zou moeten bezitten.
	 * @param string|null $permission Gevraagde permissie(s).
	 * @param array|null $allowedAuthenticationMethods Bij niet toegestane methode doen alsof gebruiker x999 is.
	 *
	 * Met deze functies kan op één of meerdere permissies worden getest,
	 * onderling gescheiden door komma's. Als een lid één van de
	 * permissies 'heeft', geeft de functie true terug. Het is dus een
	 * logische OF tussen de verschillende te testen permissies.
	 *
	 * Voorbeeldjes:
	 *  commissie:NovCie      geeft true leden van de h.t. NovCie.
	 *  commissie:SocCie:ot      geeft true voor alle leden die ooit SocCie hebben gedaan
	 *  commissie:PubCie,bestuur  geeft true voor leden van h.t. bestuur en h.t. pubcie
	 *  commissie:SocCie>Fiscus    geeft true voor h.t. Soccielid met functie fiscus
	 *  geslacht:m          geeft true voor alle mannelijke leden
	 *  verticale:d          geeft true voor alle leden van verticale d.
	 *
	 * Gecompliceerde voorbeeld:
	 *    commissie:NovCie+commissie:MaalCie|1337,bestuur
	 *
	 * Equivalent met haakjes:
	 *    (commissie:NovCie AND (commissie:MaalCie OR 1337)) OR bestuur
	 *
	 * Geeft toegang aan:
	 *    de mensen die én in de NovCie zitten én in de MaalCie zitten
	 *    of mensen die in de NovCie zitten en lidnummer 1337 hebben
	 *    of mensen die in het bestuur zitten
	 *
	 * @return bool Of $subject $permission heeft.
	 */
	public function mag(
		UserInterface $subject = null,
		$permission = null,
		array $allowedAuthenticationMethods = null
	) {
		if ($subject == null) {
			$subject = $this->accountRepository->find(LoginService::UID_EXTERN);
		}

		// Als voor het ingelogde lid een permissie gevraagd wordt
		if ($subject->uid == LoginService::getUid()) {
			// Controlleer hoe de gebruiker ge-authenticeerd is
			$method = ContainerFacade::getContainer()
				->get(LoginService::class)
				->getAuthenticationMethod();
			if ($allowedAuthenticationMethods == null) {
				$allowedAuthenticationMethods =
					self::$defaultAllowedAuthenticationMethods;
			}
			// Als de methode niet toegestaan is testen we met de permissies van niet-ingelogd
			if (!in_array($method, $allowedAuthenticationMethods)) {
				$subject = $this->accountRepository->find(LoginService::UID_EXTERN);
			}
		}

		// Rechten vergeten?
		if (empty($permission)) {
			return false;
		}

		// Altijd uppercase
		$permission = strtoupper($permission);

		// Try cache
		$key = sprintf(
			'hasPermission-%s-%s',
			urlencode(str_replace('-', '_', $permission)),
			$subject->uid
		);

		return $this->cache->get($key, function () use ($subject, $permission) {
			$permission1 = $permission;
			$permission1 = preg_replace1('/^P_/', 'ROLE_', $permission1);
			$permission1 = preg_replace1(
				'/^ROLE_PUBLIC/',
				'PUBLIC_ACCESS',
				$permission1
			);
			return $this->security->isGranted($permission1, $subject);
		});
	}

	public function isUserGranted(
		UserInterface $user,
		$attribute,
		$subject = null
	) {
		$token = new UsernamePasswordToken(
			$user,
			'none',
			'none',
			$user->getRoles()
		);

		return $this->accessDecisionManager->decide($token, [$attribute], $subject);
	}

	/**
	 * @param string $lidstatus
	 *
	 * @return string
	 * @throws CsrException
	 */
	public function getDefaultPermissionRole($lidstatus)
	{
		switch ($lidstatus) {
			case LidStatus::Kringel:
			case LidStatus::Noviet:
			case LidStatus::Lid:
			case LidStatus::Gastlid:
				return AccessRole::Lid;
			case LidStatus::Oudlid:
			case LidStatus::Erelid:
				return AccessRole::Oudlid;
			case LidStatus::Commissie:
			case LidStatus::Overleden:
			case LidStatus::Exlid:
			case LidStatus::Nobody:
				return AccessRole::Nobody;
			default:
				throw new CsrException('LidStatus onbekend');
		}
	}

	/**
	 * @return string[]
	 */
	public function getPermissionSuggestions()
	{
		$suggestions = array_keys($this->permissions);
		$suggestions[] = 'bestuur';
		$suggestions[] = 'geslacht:m';
		$suggestions[] = 'geslacht:v';
		$suggestions[] = 'ouderejaars';
		$suggestions[] = 'eerstejaars';
		return $suggestions;
	}

	/**
	 * Get error(s) in permission string, if any.
	 *
	 * @param string $permissions
	 * @return array empty if no errors; substring(s) of $permissions containing error(s) otherwise
	 */
	public function getPermissionStringErrors($permissions)
	{
		$errors = [];
		// OR
		$or = explode(',', $permissions);
		foreach ($or as $and) {
			// AND
			$and = explode('+', $and);
			foreach ($and as $or2) {
				// OR (secondary)
				$or2 = explode('|', $or2);
				foreach ($or2 as $perm) {
					if (!$this->isValidPermission($perm)) {
						$errors[] = $perm;
					}
				}
			}
		}
		return $errors;
	}

	/**
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function isValidPermission($permission)
	{
		// case insensitive
		$permission = strtoupper($permission);

		// Is de gevraagde permissie het uid van de gevraagde gebruiker?
		if (AccountRepository::isValidUid(strtolower($permission))) {
			return true;
		}

		// Is de gevraagde permissie voorgedefinieerd?
		if (isset($this->permissions[$permission])) {
			return true;
		}

		// splits permissie in type, waarde en rol
		$p = explode(':', $permission);
		if (in_array($p[0], self::$prefix) && sizeof($p) <= 3) {
			if (isset($p[1]) && $p[1] == '') {
				return false;
			}
			if (isset($p[2]) && $p[2] == '') {
				return false;
			}
			return true;
		}

		return false;
	}
}
