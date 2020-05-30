<?php


namespace CsrDelft\service\security;


use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\entity\security\LoginSession;
use CsrDelft\entity\security\RememberLogin;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\repository\security\LoginSessionRepository;
use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\service\AccessService;
use CsrDelft\view\formulier\invoervelden\WachtwoordWijzigenField;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

/**
 * Deze service verteld je dingen over de op dit moment ingelogde gebruiker.
 *
 * @package CsrDelft\service
 */
class LoginService {
	/**
	 * Voorgedefinieerde uids
	 */
	public const UID_EXTERN = 'x999';
	public const UID_CLI = 'x900';
	/**
	 * Sessiesleutels
	 */
	const SESS_AUTH_ERROR = 'auth_error';
	const SESS_UID = '_uid';
	const SESS_AUTHENTICATION_METHOD = '_authenticationMethod';
	const SESS_SUED_FROM = '_suedFrom';
	/**
	 * Cookies
	 */
	const COOKIE_REMEMBER = 'remember';
	/**
	 * @var string Huidige uid als met cli is ingelogd.
	 */
	private static $cliUid = 'x999';
	/**
	 * @var LoginSession
	 */
	protected $current_session;
	/**
	 * @var LoginSessionRepository
	 */
	private $loginRepository;
	/**
	 * @var RememberLoginRepository
	 */
	private $rememberLoginRepository;
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager, LoginSessionRepository $loginRepository, RememberLoginRepository $rememberLoginRepository, AccountRepository $accountRepository) {
		$this->loginRepository = $loginRepository;
		$this->rememberLoginRepository = $rememberLoginRepository;
		$this->accountRepository = $accountRepository;
		$this->entityManager = $entityManager;
	}

	/**
	 * @param string $permission
	 * @param array|null $allowedAuthenticationMethods
	 *
	 * @return bool
	 */
	public static function mag($permission, array $allowedAuthenticationMethods = null) {
		return AccessService::mag(static::getAccount(), $permission, $allowedAuthenticationMethods);
	}

	/**
	 * @return Account|false
	 */
	public static function getAccount() {
		return AccountRepository::get(static::getUid());
	}

	/**
	 * @return string
	 */
	public static function getUid() {
		if (MODE === 'CLI') {
			return static::$cliUid;
		}
		return $_SESSION[self::SESS_UID] ?? self::UID_EXTERN;
	}

	/**
	 * @return Profiel|false
	 */
	public static function getProfiel() {
		return ProfielRepository::get(static::getUid());
	}

	/**
	 * @throws NonUniqueResultException
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @throws Exception
	 */
	public function authenticate() {
		/**
		 * Sessie valideren: is er iemand ingelogd en is alles OK?
		 * Zo ja, sessie verlengen.
		 * Zo nee, dan public gebruiker er in gooien.
		 */
		$this->current_session = $this->getCurrentSession();
		if ($this->validate()) {
			// Public gebruiker heeft geen DB sessie
			if ($_SESSION[self::SESS_UID] != self::UID_EXTERN) {
				$this->current_session->expire = date_create_immutable()->add(new DateInterval('PT' . getSessionMaxLifeTime() . 'S'));
				$this->loginRepository->update($this->current_session);
			}
		} else {
			// Subject assignment:
			$_SESSION[self::SESS_UID] = self::UID_EXTERN;
			$_SESSION[self::SESS_AUTHENTICATION_METHOD] = null;

			// Remember login
			if (isset($_COOKIE[self::COOKIE_REMEMBER])) {
				$remember = $this->rememberLoginRepository->verifyToken($_COOKIE[self::COOKIE_REMEMBER]);
				if ($remember) {
					$this->login($remember->uid, null, false, $remember, $remember->lock_ip);
				}
			}
		}
		if ($_SESSION[self::SESS_UID] == self::UID_EXTERN) {
			/**
			 * Als we x999 zijn checken we of er misschien een private token in de $_GET staat.
			 * Deze staat toe zonder wachtwoord gelimiteerde rechten te krijgen op iemands naam.
			 */
			$token = filter_input(INPUT_GET, 'private_token', FILTER_SANITIZE_STRING);
			if (preg_match('/^[a-zA-Z0-9]{150}$/', $token)) {
				$account = $this->accountRepository->findOneBy(['private_token' => $token]);
				if ($account) {
					$this->login($account->uid, null, false, null, true, true, date_create_immutable());
				}
			}
		}
		if (!static::getAccount()) {
			// public gebruiker stuk?
			header('Retry-After: 3600');
			http_response_code(503);
			die('<h1>503 Service Unavailable</h1>');
		}
	}

	/**
	 * @return LoginSession|null
	 */
	protected function getCurrentSession() {
		return $this->loginRepository->find(hash('sha512', session_id()));
	}

	/**
	 * Is de huidige gebruiker al actief in een sessie?
	 *
	 * @return bool
	 */
	public function validate() {
		// Er is geen _uid gezet in $_SESSION dus er is nog niemand ingelogd
		if (!isset($_SESSION[self::SESS_UID])) {
			return false;
		}
		// Public gebruiker vereist geen authenticatie
		if ($_SESSION[self::SESS_UID] === self::UID_EXTERN) {
			return true;
		}
		// Controleer of sessie niet gesloten is door gebruiker
		if (!$this->current_session) {
			return false;
		}
		// Controleer of sessie is verlopen
		if ($this->current_session->expire && $this->current_session->expire <= date_create_immutable()) {
			return false;
		}
		// Controleer gekoppeld ip
		if ($this->current_session->lock_ip && $this->current_session->ip !== $_SERVER['REMOTE_ADDR']) {
			return false;
		}
		// Controleer switch user status
		if (isset($_SESSION[self::SESS_SUED_FROM])) {
			$suedFrom = SuService::getSuedFrom();
			if (!$suedFrom || $this->current_session->uid !== $suedFrom->uid) {
				return false;
			}
			// Controleer of account bestaat
			if (!static::getAccount()) {
				return false;
			}
			return true;
		}
		// Controleer of sessie van gebruiker is
		$account = static::getAccount();
		if (!$account || $this->current_session->uid !== $account->uid) {
			return false;
		}
		// Controleer of wachtwoord is verlopen
		$pass_since = $account->pass_since->getTimestamp();
		$verloop_na = strtotime(instelling('beveiliging', 'wachtwoorden_verlopen_ouder_dan'));
		$waarschuwing_vooraf = strtotime(instelling('beveiliging', 'wachtwoorden_verlopen_waarschuwing_vooraf'), $verloop_na);
		if ($pass_since < $verloop_na) {
			if (!startsWith(REQUEST_URI, '/wachtwoord')
				&& !startsWith(REQUEST_URI, '/verify/')
				&& !startsWith(REQUEST_URI, '/styles/')
				&& !startsWith(REQUEST_URI, '/scripts/')
				&& REQUEST_URI !== '/endsu'
				&& REQUEST_URI !== '/logout') {
				setMelding('Uw wachtwoord is verlopen', 2);
				redirect('/wachtwoord/verlopen');
			}
		} elseif (REQUEST_URI == '' && $pass_since < $waarschuwing_vooraf) {
			$uren = ($waarschuwing_vooraf - $pass_since) / 3600;
			if ($uren < 24) {
				setMelding('Uw wachtwoord verloopt binnen ' . $uren . ' uur', 2);
			} else {
				$dagen = floor((double)$uren / (double)24) . ' dag';
				if ($dagen > 1) {
					$dagen .= 'en';
				}
				setMelding('Uw wachtwoord verloopt over ' . $dagen, 2);
			}
		}
		return true;
	}

	/**
	 * Inloggen met verschillende mogelijkheden:
	 *
	 * Als een gebruiker wordt ingelogd met $wacht == true, dan wordt gekeken of
	 * er een timeout nodig is vanwege eerdere mislukte inlogpogingen.
	 *
	 * Als een gebruiker wordt ingelogd met $lockIP == true, dan wordt het IP-adres
	 * van de gebruiker opgeslagen in de sessie, en het sessie-cookie zal ALLEEN
	 * vanaf dat adres toegang geven tot de website.
	 *
	 * Als een gebruiker wordt ingelogd met $tokenAuthenticated == true, dan wordt het wachtwoord
	 * van de gebruiker NIET gecontroleerd en wordt er ook GEEN timeout geforceerd, er wordt
	 * vanuit gegaan dat VOORAF een token is gecontroleerd en dat voldoende is voor authenticatie.
	 *
	 * Als een gebruiker wordt ingelogd met $expire == DateTime, dan verloopt de sessie
	 * van de gebruiker op het gegeven moment en wordt de gebruiker uigelogd.
	 *
	 * @param string $user
	 * @param string $pass_plain
	 * @param boolean $evtWachten
	 * @param RememberLogin $remember
	 * @param boolean $lockIP
	 * @param boolean $alreadyAuthenticatedByUrlToken
	 * @param DateTimeImmutable $expire
	 * @return boolean
	 * @throws Exception
	 */
	public function login($user, $pass_plain, $evtWachten = true, RememberLogin $remember = null, $lockIP = false, $alreadyAuthenticatedByUrlToken = false, $expire = null) {
		$user = filter_var($user, FILTER_SANITIZE_STRING);

		if ($user == self::UID_EXTERN || $user == self::UID_CLI) {
			throw new CsrGebruikerException('Kan niet inloggen op dit account');
		}

		// Inloggen met lidnummer of gebruikersnaam
		if ($this->accountRepository::isValidUid($user)) {
			$account = $this->accountRepository->get($user);
		} else {
			$account = $this->accountRepository->findOneByUsername($user);

			if (!$account) {
				$account = $this->accountRepository->findOneByEmail($user);
			}
		}

		// Onbekende gebruiker
		if (!$account) {
			$_SESSION[self::SESS_AUTH_ERROR] = 'Inloggen niet geslaagd';
			return false;
		}

		// Clear session
		session_unset();

		// Autologin
		if ($remember) {
			$_SESSION[self::SESS_AUTHENTICATION_METHOD] = AuthenticationMethod::cookie_token;
		} // Previously(!) verified private token or OneTimeToken
		elseif ($alreadyAuthenticatedByUrlToken) {
			$_SESSION[self::SESS_AUTHENTICATION_METHOD] = AuthenticationMethod::url_token;
		} else {
			// Moet eventueel wachten?
			if ($evtWachten) {
				// Check timeout
				$timeout = $this->accountRepository->moetWachten($account);
				if ($timeout > 0) {
					$_SESSION[self::SESS_AUTH_ERROR] = 'Wacht ' . $timeout . ' seconden';
					return false;
				}
			}

			if (!empty($account->blocked_reason)) {
				$_SESSION[self::SESS_AUTH_ERROR] = 'Dit account is geblokkeerd: ' . $account->blocked_reason;
				return false;
			}

			// Check password
			if ($this->accountRepository->controleerWachtwoord($account, $pass_plain)) {
				$this->accountRepository->successfulLoginAttempt($account);
				$_SESSION[self::SESS_AUTHENTICATION_METHOD] = AuthenticationMethod::password_login;
			} // Wrong password
			else {
				// Password deleted (by admin)
				if ($account->pass_hash == '') {
					$_SESSION[self::SESS_AUTH_ERROR] = 'Gebruik wachtwoord vergeten of mail de PubCie';
				} // Regular failed username+password
				else {
					$_SESSION[self::SESS_AUTH_ERROR] = 'Inloggen niet geslaagd';
					$this->accountRepository->failedLoginAttempt($account);
				}
				return false;
			}
		}

		// Subject assignment:
		$_SESSION[self::SESS_UID] = $account->uid;

		if ($account->uid !== self::UID_EXTERN) {
			// Permissions change: delete old session
			session_regenerate_id(true);

			if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
				$user_agent = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING);
			} else {
				$user_agent = '';
			}
			if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
				$remote_addr = filter_var($_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING);
			} else {
				$remote_addr = '';
			}

			// Login sessie aanmaken in database
			$this->current_session = new LoginSession();
			$this->current_session->session_hash = hash('sha512', session_id());
			$this->current_session->uid = $account->uid;
			$this->current_session->account = $account;
			$this->current_session->login_moment = date_create_immutable();
			$this->current_session->expire = $expire ? $expire : date_create_immutable()->add(new DateInterval('PT' . getSessionMaxLifeTime() . 'S'));
			$this->current_session->user_agent = $user_agent;
			$this->current_session->ip = $remote_addr;
			$this->current_session->lock_ip = $lockIP; // sessie koppelen aan ip?
			$this->current_session->authentication_method = $_SESSION[self::SESS_AUTHENTICATION_METHOD];
			$this->loginRepository->update($this->current_session);

			if ($remember) {
				setMelding('Welkom ' . ProfielRepository::getNaam($account->uid, 'civitas') . '! U bent <a href="/instellingen#table-automatisch-inloggen" style="text-decoration: underline;">automatisch ingelogd</a>.', 0);
			} elseif (!$alreadyAuthenticatedByUrlToken) {

				// Controleer actief wachtwoordbeleid
				$_POST['checkpw_new'] = $pass_plain;
				$_POST['checkpw_confirm'] = $pass_plain;
				$field = new WachtwoordWijzigenField('checkpw', $account, false); // fetches POST values itself
				if (!$field->validate()) {
					$_SESSION['password_unsafe'] = true;
					setMelding('Uw wachtwoord is onveilig: ' . str_replace('nieuwe', 'huidige', $field->getError()), 2);
					redirect('/wachtwoord/wijzigen');
				}

				// Welcome message
				setMelding('Welkom ' . ProfielRepository::getNaam($account->uid, 'civitas') . '! U bent momenteel <a href="/instellingen#table-automatisch-inloggen" style="text-decoration: underline;">' . $this->loginRepository->getActiveSessionCount($account->uid) . 'x ingelogd</a>.', 0);
			}
		}
		return true;
	}

	/**
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function logout() {
		// Forget autologin
		if (isset($_COOKIE[self::COOKIE_REMEMBER])) {
			$this->rememberLoginRepository->verwijder(hash('sha512', $_COOKIE[self::COOKIE_REMEMBER]));
			setRememberCookie(null);
		}
		// Destroy login session
		$this->loginRepository->removeByHash(hash('sha512', session_id()));
		session_destroy();
	}

	/**
	 * Indien de huidige gebruiker is geauthenticeerd door middel van een token in de url
	 * worden Permissies hierdoor beperkt voor de veiligheid.
	 * @return string|null uit AuthenticationMethod
	 * @see AccessService::mag()
	 */
	public function getAuthenticationMethod() {
		if (MODE == 'CLI') {
			return AuthenticationMethod::password_login;
		}

		if (!isset($_SESSION[self::SESS_AUTHENTICATION_METHOD])) {
			return null;
		}
		$method = $_SESSION[self::SESS_AUTHENTICATION_METHOD];
		if ($method === AuthenticationMethod::password_login) {
			if ($this->current_session && $this->current_session->isRecent()) {
				return AuthenticationMethod::recent_password_login;
			}
		}
		return $method;
	}

	public function resetLoginMoment() {
		$this->current_session->login_moment = date_create_immutable();
		$this->entityManager->flush();
	}

	/**
	 * Na opvragen resetten.
	 *
	 * @return mixed null or string
	 */
	public function getError() {
		if (!$this->hasError()) {
			return null;
		}
		$error = $_SESSION[self::SESS_AUTH_ERROR];
		unset($_SESSION[self::SESS_AUTH_ERROR]);
		return $error;
	}

	/**
	 * @return bool
	 */
	public function hasError() {
		return isset($_SESSION[self::SESS_AUTH_ERROR]);
	}

	/**
	 * Maak een sessie aan voor command line gebruik. Zo worden veranderingen die met cron of cli worden gedaan
	 * toegewezen aan een gebruiker.
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function loginCli() {
		$account = $this->accountRepository->get(self::UID_CLI);

		// Onbekende gebruiker
		if (!$account) {
			die('Cron user bestaat niet!');
		}

		// Clear session
		session_unset();

		static::$cliUid = $account->uid;

		$this->current_session = $this->loginRepository->find(hash('sha512', session_id()));

		if (!$this->current_session) {
			$this->current_session = new LoginSession();
			$this->entityManager->persist($this->current_session);
		}

		// Login sessie aanmaken in database
		$this->current_session->session_hash = hash('sha512', session_id());
		$this->current_session->uid = $account->uid;
		$this->current_session->account = $account;
		$this->current_session->login_moment = date_create_immutable();
		$this->current_session->expire = date_create_immutable()->add(new DateInterval('PT' . getSessionMaxLifeTime() . 'S'));
		$this->current_session->user_agent = MODE;
		$this->current_session->ip = '';
		$this->current_session->lock_ip = true; // sessie koppelen aan ip?
		$this->current_session->authentication_method = AuthenticationMethod::password_login;

		$this->entityManager->flush();

		return true;
	}
}
