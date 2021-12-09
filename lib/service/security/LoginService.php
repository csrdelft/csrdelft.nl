<?php


namespace CsrDelft\service\security;


use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Security\JwtToken;
use CsrDelft\common\Security\PrivateTokenToken;
use CsrDelft\common\Security\TemporaryToken;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccessService;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;

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
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;
	/**
	 * @var AccessService
	 */
	private $accessService;

	public function __construct(
		Security $security,
		AccountRepository $accountRepository,
		AccessService $accessService,
		TokenStorageInterface $tokenStorage
	) {
		$this->accountRepository = $accountRepository;
		$this->security = $security;
		$this->tokenStorage = $tokenStorage;
		$this->accessService = $accessService;
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
		$account = $this->security->getUser();

		return $this->accessService->mag($account, $permission, $allowedAuthenticationMethdos);
	}

	/**
	 * @return string
	 */
	public static function getUid() {
		if (isCli()) {
			return static::$cliUid;
		}

		$account = static::getAccount();

		if ($account) {
			return $account->uid;
		}

		return self::UID_EXTERN;
	}

	/**
	 * @return UserInterface|Account|null
	 */
	public static function getAccount() {
		return ContainerFacade::getContainer()->get('security')->getUser()
			?? ContainerFacade::getContainer()->get(AccountRepository::class)->find(self::UID_EXTERN);
	}

	/**
	 * @return Profiel|null
	 */
	public static function getProfiel() {
		$account = static::getAccount();
		if ($account) {
			return $account->profiel;
		}
		return null;
	}

	public static function isExtern() {
		return !LoginService::mag(P_LOGGED_IN);
	}

	/**
	 * Indien de huidige gebruiker is geauthenticeerd door middel van een token in de url
	 * worden Permissies hierdoor beperkt voor de veiligheid.
	 * @return string|null uit AuthenticationMethod
	 * @see AccessService::mag()
	 */
	public function getAuthenticationMethod() {
		if (isCli()) {
			return AuthenticationMethod::password_login;
		}

		$token = $this->security->getToken();

		if ($token == null) {
			return null;
		}

		switch (get_class($token)) {
			case SwitchUserToken::class:
				$method = AuthenticationMethod::impersonate;
				break;
			case PrivateTokenToken::class:
			case TemporaryToken::class:
				$method = AuthenticationMethod::temporary;
				break;
			case UsernamePasswordToken::class:
			case PostAuthenticationToken::class:
				$method = AuthenticationMethod::recent_password_login;
				break;
			case RememberMeToken::class:
			case JwtToken::class:
			case OAuth2Token::class:
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
			$this->tokenStorage->setToken(
				new UsernamePasswordToken($token->getUser(), [], $token->getFirewallName(), $token->getRoleNames()));
		}
	}
}
