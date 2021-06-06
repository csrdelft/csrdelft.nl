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
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * RBAC met MAC en DAC implementatie.
 *
 * @see http://csrc.nist.gov/groups/SNS/rbac/faq.html
 */
class AccessService {
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
		AuthenticationMethod::password_login_and_one_time_token
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
		self::PREFIX_KWALIFICATIE
	];

	/**
	 * Gebruikt om ledengegevens te raadplegen
	 * @var array
	 */
	private static $ledenRead = [P_LEDEN_READ, P_OUDLEDEN_READ];

	/**
	 * Gebruikt om ledengegevens te wijzigen
	 * @var array
	 */
	private static $ledenWrite = [P_PROFIEL_EDIT, P_LEDEN_MOD];

	/**
	 * Partially ordered Role Hierarchy:
	 *
	 * A subject can have multiple roles.  <- NIET ondersteund met MAC, wel met DAC
	 * A role can have multiple subjects.
	 * A role can have many permissions.
	 * A permission can be assigned to many roles.
	 * An operation can be assigned many permissions.
	 * A permission can be assigned to many operations.
	 */
	private $roles = [];

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
	private $em;

	/**
	 * @param CacheInterface $cache
	 * @param EntityManagerInterface $em
	 * @throws InvalidArgumentException
	 */
	public function __construct(CacheInterface $cache, EntityManagerInterface $em) {
		$this->cache = $cache;
		$this->em = $em;
		$this->loadPermissions();
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
	private function loadPermissions() {
		// see if cached
		$this->permissions = $this->cache->get('permissions-' . getlastmod(), function () {
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
		});

		$this->roles = $this->cache->get('roles-' . getlastmod(), function () {
			/**
			 * Deze waarden worden  samengesteld uit bovenstaande permissies en
			 * worden in de gebruikersprofielen gebruikt als aanduiding voor
			 * welke permissie-groep (Role) de gebruiker in zit (max. 1 momenteel).
			 */
			$p = $this->permissions;

			// Permission Assignment:
			$roles = [];

			// use | $p[] for hierarchical RBAC (inheritance between roles)
			// use & ~$p[] for constrained RBAC (separation of duties)

			$roles[AccessRole::Nobody] = $p[P_PUBLIC] | $p[P_FORUM_READ] | $p[P_ALBUM_PUBLIC_READ];
			$roles[AccessRole::Eter] = $roles[AccessRole::Nobody] | $p[P_LOGGED_IN] | $p[P_PROFIEL_EDIT] | $p[P_MAAL_IK] | $p[P_AGENDA_READ];
			$roles[AccessRole::Lid] = $roles[AccessRole::Eter] | $p[P_OUDLEDEN_READ] | $p[P_FORUM_POST] | $p[P_DOCS_READ] | $p[P_BIEB_READ] | $p[P_CORVEE_IK] | $p[P_MAIL_POST] | $p[P_NEWS_POST] | $p[P_ALBUM_ADD] | $p[P_ALBUM_PUBLIC_DOWN] | $p[P_PEILING_VOTE] | $p[P_PEILING_EDIT];
			$roles[AccessRole::Oudlid] = $roles[AccessRole::Lid];
			$roles[AccessRole::Fiscaat] = $roles[AccessRole::Lid] | $p[P_FISCAAT_READ] | $p[P_FISCAAT_MOD];
			$roles[AccessRole::MaalCie] = $roles[AccessRole::Fiscaat] | $p[P_MAAL_MOD] | $p[P_CORVEE_MOD] | $p[P_MAAL_SALDI];
			$roles[AccessRole::BASFCie] = $roles[AccessRole::Lid] | $p[P_DOCS_MOD] | $p[P_ALBUM_PUBLIC_DEL] | $p[P_ALBUM_DEL] | $p[P_BIEB_MOD];
			$roles[AccessRole::Bestuur] = $roles[AccessRole::BASFCie] | $roles[AccessRole::MaalCie] | $p[P_LEDEN_MOD] | $p[P_FORUM_MOD] | $p[P_DOCS_MOD] | $p[P_AGENDA_MOD] | $p[P_NEWS_MOD] | $p[P_MAIL_COMPOSE] | $p[P_ALBUM_DEL] | $p[P_MAAL_MOD] | $p[P_CORVEE_MOD] | $p[P_MAIL_COMPOSE] | $p[P_FORUM_BELANGRIJK] | $p[P_PEILING_MOD];
			$roles[AccessRole::PubCie] = $roles[AccessRole::Bestuur] | $p[P_ADMIN] | $p[P_MAIL_SEND] | $p[P_CORVEE_SCHED] | $p[P_FORUM_ADMIN];
			$roles[AccessRole::ForumModerator] = $roles[AccessRole::Lid] | $p[P_FORUM_MOD];

			return $roles;
		});
	}

	/**
	 * Create permission string with character which has ascii value of request level.
	 *
	 * @param int $onderdeelnummer starts at zero
	 * @param int $level permissiewaarde
	 * @return string permission string
	 */
	private function createPermStr($onderdeelnummer, $level) {
		$nulperm = str_repeat(chr(0), 15);
		return substr_replace($nulperm, chr($level), $onderdeelnummer, 1);
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
	public function mag(UserInterface $subject = null, $permission = null, array $allowedAuthenticationMethods = null) {
		if ($subject == null) {
			$subject = AccountRepository::get(LoginService::UID_EXTERN);
		}

		// Als voor het ingelogde lid een permissie gevraagd wordt
		if ($subject->uid == LoginService::getUid()) {
			// Controlleer hoe de gebruiker ge-authenticeerd is
			$method = ContainerFacade::getContainer()->get(LoginService::class)->getAuthenticationMethod();
			if ($allowedAuthenticationMethods == null) {
				$allowedAuthenticationMethods = self::$defaultAllowedAuthenticationMethods;
			}
			// Als de methode niet toegestaan is testen we met de permissies van niet-ingelogd
			if (!in_array($method, $allowedAuthenticationMethods)) {
				$subject = AccountRepository::get(LoginService::UID_EXTERN);
			}
		}

		// case insensitive
		return $this->hasPermission($subject, strtoupper($permission));
	}

	/**
	 * @param UserInterface $subject
	 * @param null $permission
	 *
	 * @return bool|mixed
	 * @throws InvalidArgumentException
	 */
	private function hasPermission(UserInterface $subject, $permission = null) {
		// Rechten vergeten?
		if (empty($permission)) {
			return false;
		}

		// Try cache
		$key = 'hasPermission-' . urlencode(str_replace('-', '_', $permission)) . '-' . $subject->uid;

		return $this->cache->get($key, function () use ($subject, $permission) {
			// OR
			if (strpos($permission, ',') !== false) {
				/**
				 * Het gevraagde mag een enkele permissie zijn, of meerdere, door komma's
				 * gescheiden, waarvan de gebruiker er dan een hoeft te hebben. Er kunnen
				 * dan ook uid's tussen zitten, als een daarvan gelijk is aan dat van de
				 * gebruiker heeft hij ook rechten.
				 */
				$p = explode(',', $permission);
				$result = false;
				foreach ($p as $perm) {
					$result |= $this->hasPermission($subject, $perm);
				}
			} // AND
			elseif (strpos($permission, '+') !== false) {
				/**
				 * Gecombineerde permissie:
				 * gebruiker moet alle permissies bezitten
				 */
				$p = explode('+', $permission);
				$result = true;
				foreach ($p as $perm) {
					$result &= $this->hasPermission($subject, $perm);
				}
			} // OR (secondary)
			elseif (strpos($permission, '|') !== false) {
				/**
				 * Mogelijkheid voor OR binnen een AND
				 * Hierdoor zijn er geen haakjes nodig in de syntax voor niet al te ingewikkelde statements.
				 * Statements waarbij haakjes wel nodig zijn moet je niet willen.
				 */
				$p = explode('|', $permission);
				$result = false;
				foreach ($p as $perm) {
					$result |= $this->hasPermission($subject, $perm);
				}
			} // Is de gevraagde permissie het uid van de gevraagde gebruiker?
			elseif ($subject->uid == strtolower($permission)) {
				$result = true;
			} // Is de gevraagde permissie voorgedefinieerd?
			elseif (isset($this->permissions[$permission])) {
				$result = $this->mandatoryAccessControl($subject, $permission);
			} else {
				$result = $this->discretionaryAccessControl($subject, $permission);
			}

			return $result;
		});
	}

	/**
	 * @param Account|UserInterface $subject
	 * @param string $permission
	 *
	 * @return bool
	 */
	private function mandatoryAccessControl(UserInterface $subject, $permission) {

		if (isset($_SESSION['password_unsafe'])) {
			if (in_array_i($permission, self::$ledenRead) || in_array_i($permission, self::$ledenWrite)) {
				setMelding('U mag geen ledengegevens opvragen want uw wachtwoord is onveilig', 2);
				return false;
			}
		}

		// zoek de rechten van de gebruiker op
		$role = $subject->perm_role;

		// ga alleen verder als er een geldige AccessRole wordt teruggegeven
		if (!$this->isValidRole($role)) {
			return false;
		}

		// zoek de codes op
		$gevraagd = $this->permissions[$permission];
		$lidheeft = $this->roles[$role];

		/**
		 * permissies zijn een string, waarin elk kararakter de
		 * waarde heeft van een permissielevel voor een bepaald onderdeel.
		 *
		 * de mogelijke verschillende permissies voor een onderdeel zijn machten van twee:
		 * 1, 2, 4, 8, etc
		 * elk van deze waardes kan onderscheiden worden in een permissie, ook als je ze met elkaar combineert
		 * bijv.  3=1+2, 7=1+2+4, 5=1+4, 6=2+4, 12=4+8, etc
		 *
		 * $gevraagd is de gevraagde permissie als string,
		 * de permissies van de gebruiker $lidheeft kunnen we bij $this->lid opvragen
		 * als we die 2 met elkaar AND-en, dan moet het resultaat hetzelfde
		 * zijn aan de gevraagde permissie. In dat geval bestaat de permissie
		 * van het account dus minimaal uit de gevraagde permissie
		 *
		 * Bij het AND-en, wordt elke karakter bitwise vergeleken, dat betekent:
		 * - elke karakter van de string omzetten in de ASCII-waarde
		 *   (bijv. ?=63, A=65, a=97, etc zie ook www.ascii.cl)
		 * - deze ASCII-waarde omzetten in een binaire getal
		 *   (bijv. 2=00010, 4=00100, 5=00101, 14=01110, etc)
		 * - de bits van het binaire getal een-voor-een vergelijken met de bits van het binaire getal uit de
		 *   andere string. Als ze overeenkomen worden ze bewaard.
		 *   (bijv. 3&5=1 => 00011&00101=00001)
		 *
		 * voorbeeld (met de getallen 0 tot 7 als ASCII-waardes ipv de symbolen, voor de leesbaarheid)
		 * gevraagd:  P_FORUM_MOD : 0000000700
		 * account heeft: R_LID   : 0005544500
		 * AND resultaat          : 0000000500 -> is niet wat gevraagd is -> weiger
		 *
		 * gevraagd:  P_DOCS_READ : 0000004000
		 * account heeft: R_LID   : 0005544500
		 * AND resultaat          : 0000004000 -> ja!
		 *
		 */
		$resultaat = $gevraagd & $lidheeft;

		if ($resultaat === $gevraagd) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $role
	 *
	 * @return bool
	 */
	public function isValidRole($role) {
		if (isset($this->roles[$role])) {
			return true;
		}
		return false;
	}

	/**
	 * @param Account|UserInterface $subject
	 * @param string $permission
	 *
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	private function discretionaryAccessControl(UserInterface $subject, $permission) {

		// haal het profiel van de gebruiker op
		$profiel = $subject->profiel;

		// ga alleen verder als er een geldig profiel wordt teruggegeven
		if (!$profiel) {
			return false;
		}

		// splits permissie in type, waarde en rol
		$p = explode(':', $permission, 3);
		if (isset($p[0])) {
			$prefix = $p[0];
		} else {
			return false;
		}
		if (isset($p[1])) {
			$gevraagd = $p[1];
		} else {
			$gevraagd = false;
		}
		if (isset($p[2])) {
			$role = $p[2];
		} else {
			$role = false;
		}

		switch ($prefix) {

			/**
			 * Is lid man of vrouw?
			 */
			case self::PREFIX_GESLACHT:
				if ($profiel->geslacht && $gevraagd == strtoupper($profiel->geslacht->getValue())) {
					// Niet ingelogd heeft geslacht m dus check of ingelogd
					if ($this->hasPermission($subject, P_LOGGED_IN)) {
						return true;
					}
				}

				return false;

			/**
			 * Heeft lid status?
			 */
			case self::PREFIX_STATUS:
				$gevraagd = 'S_' . $gevraagd;
				if ($gevraagd == $profiel->status) {
					return true;
				} elseif ($gevraagd == LidStatus::Lid && LidStatus::isLidLike($profiel->status)) {
					return true;
				} elseif ($gevraagd == LidStatus::Oudlid && LidStatus::isOudlidLike($profiel->status)) {
					return true;
				}

				return false;

			/**
			 *  Behoort een lid tot een bepaalde lichting?
			 */
			case self::PREFIX_LICHTING:
			case self::PREFIX_LIDJAAR:
				return (string)$profiel->lidjaar === $gevraagd;

			case self::PREFIX_EERSTEJAARS:
				if ($profiel->lidjaar === LichtingenRepository::getJongsteLidjaar()) {
					return true;
				}
				return false;

			case self::PREFIX_OUDEREJAARS:
				if ($profiel->lidjaar === LichtingenRepository::getJongsteLidjaar()) {
					return false;
				}
				return true;

			/**
			 *  Behoort een lid tot een bepaalde verticale?
			 */
			case self::PREFIX_VERTICALE:
				if (!$profiel->verticale) {
					return false;
				} elseif ($profiel->verticale === $gevraagd || $gevraagd == strtoupper($profiel->getVerticale()->naam)) {
					if (!$role) {
						return true;
					} elseif ($role === 'LEIDER' && $profiel->verticaleleider) {
						return true;
					}
				}
				return false;

			/**
			 * Behoort een lid tot een f.t. / h.t. / o.t. bestuur of commissie?
			 */
			/** @noinspection PhpMissingBreakStatementInspection */
			case self::PREFIX_BESTUUR:
				$gevraagd = strtolower($gevraagd);
				if (in_array($gevraagd, GroepStatus::getEnumValues())) {
					return 1 === (int)($this->em->createQuery('SELECT COUNT(b) FROM CsrDelft\entity\groepen\Bestuur b JOIN b.leden l WHERE l.uid = :uid AND b.status = :gevraagd')
							->setParameter('gevraagd', $gevraagd)
							->setParameter('uid', $profiel->uid)
							->getSingleScalarResult());
				}

			/** @noinspection PhpMissingBreakStatementInspection */
			case self::PREFIX_COMMISSIE:
				$role = strtolower($role);
				// Alleen als GroepStatus is opgegeven, anders: fall through
				if (in_array($role, GroepStatus::getEnumValues())) {
					return 1 === (int)$this->em->createQuery('SELECT COUNT(c) FROM CsrDelft\entity\groepen\Commissie c JOIN c.leden l WHERE l.uid = :uid AND c.familie = :gevraagd AND c.status = :role')
							->setParameter('gevraagd', $gevraagd)
							->setParameter('role', $role)
							->setParameter('uid', $profiel->uid)
							->getSingleScalarResult();
				}
			// fall through

			/**
			 * Behoort een lid tot een bepaalde groep? Verticalen en kringen zijn ook groepen.
			 * Als een string als bijvoorbeeld 'pubcie' wordt meegegeven zoekt de ketzer de h.t.
			 * groep met die korte naam erbij, als het getal is uiteraard de groep met dat id.
			 * Met de toevoeging ':Fiscus' kan ook specifieke functie geëist worden binnen een groep.
			 */
			case self::PREFIX_KRING:
			case self::PREFIX_ONDERVERENIGING:
			case self::PREFIX_WOONOORD:
			case self::PREFIX_ACTIVITEIT:
			case self::PREFIX_KETZER:
			case self::PREFIX_WERKGROEP:
			case self::PREFIX_GROEP:
				switch ($prefix) {

					case self::PREFIX_BESTUUR:
						if (in_array($gevraagd, CommissieFunctie::getEnumValues())) {
							$gevraagd = false;
							$role = $gevraagd;
						}
						if ($gevraagd) {
							$groep = $this->em->getRepository(Bestuur::class)->get($gevraagd);
						} else {
							$groep = $this->em->getRepository(Bestuur::class)->get('bestuur'); // h.t.
						}
						break;

					case self::PREFIX_COMMISSIE:
						$groep = $this->em->getRepository(Commissie::class)->get($gevraagd);
						break;

					case self::PREFIX_KRING:
						$groep = $this->em->getRepository(Kring::class)->get($gevraagd);
						break;

					case self::PREFIX_ONDERVERENIGING:
						$groep = $this->em->getRepository(Ondervereniging::class)->get($gevraagd);
						break;

					case self::PREFIX_WOONOORD:
						$groep = $this->em->getRepository(Woonoord::class)->get($gevraagd);
						break;

					case self::PREFIX_ACTIVITEIT:
						$groep = $this->em->getRepository(Activiteit::class)->get($gevraagd);
						break;

					case self::PREFIX_KETZER:
						$groep = $this->em->getRepository(Ketzer::class)->get($gevraagd);
						break;

					case self::PREFIX_WERKGROEP:
						$groep = $this->em->getRepository(Werkgroep::class)->get($gevraagd);
						break;

					case self::PREFIX_GROEP:
					default:
						$groep = $this->em->getRepository(RechtenGroep::class)->get($gevraagd);
						break;
				}

				if (!$groep) {
					return false;
				}

				$lid = $groep->getLid($profiel->uid);
				if (!$lid) {
					return false;
				}

				// wordt er een functie gevraagd?
				if ($role && strtoupper($role) !== strtoupper($lid->opmerking)) {
					return false;
				}
				return true;

			/**
			 * Is een lid aangemeld voor een bepaalde maaltijd?
			 */
			case self::PREFIX_MAALTIJD:
				// Geldig maaltijd id?
				if (!is_numeric($gevraagd)) {
					return false;
				}
				// Aangemeld voor maaltijd?
				if (!$role && $this->em->getRepository(MaaltijdAanmelding::class)->getIsAangemeld((int)$gevraagd, $profiel->uid)) {
					return true;
				} // Mag maaltijd sluiten?
				elseif ($role === 'SLUITEN') {
					if ($this->hasPermission($subject, P_MAAL_MOD)) {
						return true;
					}
					try {
						$maaltijd = $this->em->getRepository(Maaltijd::class)->getMaaltijd((int)$gevraagd);
						if ($maaltijd && $maaltijd->magSluiten($profiel->uid)) {
							return true;
						}
					} catch (CsrException $e) {
						// Maaltijd bestaat niet
					}
				}

				return false;

			/**
			 * Heeft een lid een kwalficatie voor een functie in het covee-systeem?
			 */
			case self::PREFIX_KWALIFICATIE:

				if (is_numeric($gevraagd)) {
					$functie_id = (int)$gevraagd;
				} else {
					$corveeFunctiesRepository = $this->em->getRepository(CorveeFunctie::class);

					$functie = $corveeFunctiesRepository->findOneBy(['afkorting' => $gevraagd]);

					if (!$functie) {
						$functie = $corveeFunctiesRepository->findOneBy(['naam' => $gevraagd]);
					}

					if ($functie) {
						$functie_id = $functie->functie_id;
					} else {
						return false;
					}
				}

				return $this->em->getRepository(CorveeKwalificatie::class)->isLidGekwalificeerdVoorFunctie($profiel->uid, $functie_id);
		}
		return false;
	}

	/**
	 * @param string $lidstatus
	 *
	 * @return string
	 * @throws CsrException
	 */
	public function getDefaultPermissionRole($lidstatus) {
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
	public function getPermissionSuggestions() {
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
	public function getPermissionStringErrors($permissions) {
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
	public function isValidPermission($permission) {
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
