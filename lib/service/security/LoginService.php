<?php


namespace CsrDelft\service\security;


use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Security\JwtToken;
use CsrDelft\common\Security\TemporaryToken;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\repository\security\LoginSessionRepository;
use CsrDelft\service\AccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManagerInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
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
	 * @var LoginSessionRepository
	 */
	private $loginRepository;
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
	 * @var UserAuthenticatorInterface
	 */
	private $userAuthenticator;
	/**
	 * @var FormLoginAuthenticator
	 */
	private $formLoginAuthenticator;
	/**
	 * @var AuthenticatorManagerInterface
	 */
	private $authenticatorManager;

	public function __construct(
		EntityManagerInterface $entityManager,
		Security $security,
		ContainerInterface $container,
		LoginSessionRepository $loginRepository,
		AccountRepository $accountRepository,
		AuthenticatorManagerInterface $authenticatorManager,
		UserAuthenticatorInterface $userAuthenticator,
		FormLoginAuthenticator $formLoginAuthenticator
	) {
		$this->loginRepository = $loginRepository;
		$this->accountRepository = $accountRepository;
		$this->entityManager = $entityManager;
		$this->security = $security;
		$this->container = $container;
		$this->authenticatorManager = $authenticatorManager;
		$this->userAuthenticator = $userAuthenticator;
		$this->formLoginAuthenticator = $formLoginAuthenticator;
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
		if (MODE == 'CLI') {
			return static::getCliAccount();
		}

		return $this->security->getUser() ?? $this->accountRepository->find(self::UID_EXTERN);
	}

	private static function getCliAccount() {
		$account = new Account();
		$account->email = env('EMAIL_PUBCIE');
		$account->uid = self::UID_CLI;
		$account->perm_role = 'R_PUBCIE';

		return $account;
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
		return ContainerFacade::getContainer()->get(LoginService::class)->_getAccount();
	}

	/**
	 * @return Profiel|false
	 */
	public static function getProfiel() {
		return ContainerFacade::getContainer()->get(LoginService::class)->_getProfiel();
	}

	private function _getProfiel() {
		return $this->_getAccount()->profiel;
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
				$method = AuthenticationMethod::recent_password_login;
				break;
			case RememberMeToken::class:
			case JwtToken::class:
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
}
