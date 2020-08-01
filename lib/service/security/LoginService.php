<?php


namespace CsrDelft\service\security;


use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Security\TemporaryToken;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\entity\security\LoginSession;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\repository\security\LoginSessionRepository;
use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\Security\LoginFormAuthenticator;
use CsrDelft\service\AccessService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

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
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var ContainerInterface
	 */
	private $container;
	/**
	 * @var GuardAuthenticatorHandler
	 */
	private $guardAuthenticatorHandler;
	/**
	 * @var AuthenticatorInterface
	 */
	private $authenticator;

	public function __construct(EntityManagerInterface $entityManager, Security $security, ContainerInterface $container, LoginFormAuthenticator $authenticator, GuardAuthenticatorHandler $guardAuthenticatorHandler, LoginSessionRepository $loginRepository, RememberLoginRepository $rememberLoginRepository, AccountRepository $accountRepository) {
		$this->loginRepository = $loginRepository;
		$this->rememberLoginRepository = $rememberLoginRepository;
		$this->accountRepository = $accountRepository;
		$this->entityManager = $entityManager;
		$this->security = $security;
		$this->container = $container;
		$this->guardAuthenticatorHandler = $guardAuthenticatorHandler;
		$this->authenticator = $authenticator;
	}

	/**
	 * @param string $permission
	 * @param array|null $allowedAuthenticationMethods
	 *
	 * @return bool
	 */
	public static function mag($permission, array $allowedAuthenticationMethods = null) {
		return ContainerFacade::getContainer()->get(LoginService::class)->_mag($permission, $allowedAuthenticationMethods);
	}

	public function _mag($permission, array $allowedAuthenticationMethdos = null) {
		return AccessService::mag($this->_getAccount(), $permission, $allowedAuthenticationMethdos);
	}

	public function _getAccount() {
		return $this->security->getUser() ?? $this->accountRepository->find(self::UID_EXTERN);
	}

	/**
	 * @return string
	 */
	public static function getUid() {
		if (MODE === 'CLI') {
			return static::$cliUid;
		}

		$account = static::getAccount();

		if (!$account) {
			return self::UID_EXTERN;
		}

		return $account->uid;
	}

	/**
	 * @return Account|false
	 */
	public static function getAccount() {
		if (static::$cliUid == self::UID_CLI) {
			return static::getCliAccount();
		}

		return ContainerFacade::getContainer()->get(LoginService::class)->_getAccount();
	}

	private static function getCliAccount() {
		$account = new Account();
		$account->email = env('EMAIL_PUBCIE');
		$account->uid = self::UID_CLI;
		$account->perm_role = 'R_PUBCIE';

		return $account;
	}

	/**
	 * @return Profiel|false
	 */
	public static function getProfiel() {
		return static::getAccount()->profiel;
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
	 * @param Request $request
	 * @param string $user
	 * @param string $pass_plain
	 * @return boolean
	 */
	public function login(Request $request, $user, $pass_plain) {
		$user = filter_var($user, FILTER_SANITIZE_STRING);

		if ($user == self::UID_EXTERN || $user == self::UID_CLI) {
			throw new CsrGebruikerException('Kan niet inloggen op dit account');
		}

		// Inloggen met lidnummer of gebruikersnaam
		$account = $this->accountRepository->findOneByUsername($user);

		// Onbekende gebruiker
		if (!$account) {
			return false;
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

		if ($account->uid !== self::UID_EXTERN) {
			$this->guardAuthenticatorHandler->authenticateUserAndHandleSuccess($account, $request, $this->authenticator, 'main');
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

		$token = $this->security->getToken();

		if ($token == null) {
			return null;
		}

		switch (get_class($token)) {
			case SwitchUserToken::class:
			case TemporaryToken::class:
				$method = AuthenticationMethod::temporary;
				break;
			case UsernamePasswordToken::class:
			case PostAuthenticationToken::class:
			case PostAuthenticationGuardToken::class:
				$method = AuthenticationMethod::recent_password_login;
				break;
			case RememberMeToken::class;
				$method = AuthenticationMethod::cookie_token;
				break;
			default:
				$method = null;
				break;
		}

		return $method;
	}

	/**
	 * Maak de gebruiker opnieuw recent ingelogd
	 */
	public function setRecentLoginToken() {
		$token = $this->security->getToken();

		if ($token instanceof RememberMeToken) {
			$this->container->get('security.token_storage')
				->setToken(new UsernamePasswordToken($token->getUser(), [], $token->getProviderKey(), $token->getRoleNames()));
		}
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
}
