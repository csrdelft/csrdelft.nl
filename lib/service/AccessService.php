<?php

namespace CsrDelft\service;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Security;
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
		Security $security,
		AccessDecisionManagerInterface $accessDecisionManager,
		AccountRepository $accountRepository
	) {
		$this->cache = $cache;
		$this->accountRepository = $accountRepository;
		$this->security = $security;
		$this->accessDecisionManager = $accessDecisionManager;
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
			return $this->security->isGranted($permission, $subject);
		});
	}

	/**
	 * Controleert of een specifieke gebruiker de juiste rechten heeft.
	 *
	 * @param UserInterface $user
	 * @param $attribute
	 * @param $subject
	 * @return bool
	 */
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
