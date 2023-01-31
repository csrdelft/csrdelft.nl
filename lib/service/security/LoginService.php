<?php

namespace CsrDelft\service\security;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Security\JwtToken;
use CsrDelft\common\Security\PrivateTokenToken;
use CsrDelft\common\Security\TemporaryToken;
use CsrDelft\common\Util\HostUtil;
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
use League\Bundle\OAuth2ServerBundle\Security\Authentication\Token\OAuth2Token;

/**
 * Deze service verteld je dingen over de op dit moment ingelogde gebruiker.
 *
 * @package CsrDelft\service
 */
class LoginService
{
	/**
	 * Voorgedefinieerde uids
	 */
	public const UID_EXTERN = 'x999';
	public const UID_CLI = 'x900';
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

	public function __construct(
		Security $security,
		AccountRepository $accountRepository,
		TokenStorageInterface $tokenStorage
	) {
		$this->accountRepository = $accountRepository;
		$this->security = $security;
		$this->tokenStorage = $tokenStorage;
	}

	/**
	 * @param string $permission
	 * @param array|null $allowedAuthenticationMethods
	 * @deprecated Gebruik CsrSecurity::mag
	 *
	 * @return bool
	 */
	public static function mag($permission)
	{
		return ContainerFacade::getContainer()
			->get('security')
			->isGranted($permission);
	}

	/**
	 * @return string
	 * @deprecated Gebruik _getUid of CsrSecurity::getAccount()->uid
	 */
	public static function getUid()
	{
		return ContainerFacade::getContainer()
			->get(LoginService::class)
			->_getUid();
	}

	public function _getUid()
	{
		if (HostUtil::isCLI()) {
			return static::$cliUid;
		}

		$token = $this->security->getToken();

		if (!$token) {
			return self::UID_EXTERN;
		}

		return $token->getUserIdentifier();
	}

	/**
	 * @return UserInterface|Account|null
	 * @deprecated Gebruik CsrSecurity::getAccount
	 */
	public static function getAccount()
	{
		return ContainerFacade::getContainer()
			->get(LoginService::class)
			->_getAccount();
	}

	public function _getAccount()
	{
		return $this->security->getUser() ??
			$this->accountRepository->find(self::UID_EXTERN);
	}

	/**
	 * @return Profiel|null
	 * @deprecated Gebruik CsrSecurity::getProfiel
	 */
	public static function getProfiel()
	{
		$account = static::getAccount();
		if ($account) {
			return $account->profiel;
		}
		return null;
	}

	/**
	 * Indien de huidige gebruiker is geauthenticeerd door middel van een token in de url
	 * worden Permissies hierdoor beperkt voor de veiligheid.
	 * @return string|null uit AuthenticationMethod
	 */
	public function getAuthenticationMethod()
	{
		if (HostUtil::isCLI()) {
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
	public function setRecentLoginToken()
	{
		$token = $this->security->getToken();

		if ($token instanceof RememberMeToken) {
			$this->tokenStorage->setToken(
				new UsernamePasswordToken(
					$token->getUser(),
					[],
					$token->getFirewallName(),
					$token->getRoleNames()
				)
			);
		}
	}
}
