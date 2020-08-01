<?php

namespace CsrDelft\Security;

use CsrDelft\entity\security\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractAuthenticator implements PasswordAuthenticatedInterface, AuthenticatorInterface {
	use TargetPathTrait;

	public const LOGIN_ROUTE = 'app_login';

	public const CREDENTIALS_USER = 'user';
	public const CREDENTIALS_PASS = 'pass';
	public const CREDENTIALS_CSRF = 'csrf_token';

	private $entityManager;
	private $urlGenerator;
	private $csrfTokenManager;
	private $passwordEncoder;

	public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder) {
		$this->entityManager = $entityManager;
		$this->urlGenerator = $urlGenerator;
		$this->csrfTokenManager = $csrfTokenManager;
		$this->passwordEncoder = $passwordEncoder;
	}

	public function supports(Request $request): ?bool {
		return self::LOGIN_ROUTE === $request->attributes->get('_route')
			&& $request->isMethod('POST');
	}

	public function getCredentials(Request $request) {
		$credentials = [
			self::CREDENTIALS_USER => $request->request->get('user'),
			self::CREDENTIALS_PASS => $request->request->get('pass'),
			self::CREDENTIALS_CSRF => $request->request->get('X-CSRF-VALUE'),
		];
		$request->getSession()->set(
			Security::LAST_USERNAME,
			$credentials[self::CREDENTIALS_USER]
		);

		return $credentials;
	}

	public function getUser($credentials, UserProviderInterface $userProvider) {
		$token = new CsrfToken('global', $credentials[self::CREDENTIALS_CSRF]);
		if (!$this->csrfTokenManager->isTokenValid($token)) {
			throw new InvalidCsrfTokenException();
		}

		$accountRepository = $this->entityManager->getRepository(Account::class);

		$user = $accountRepository->findOneByUsername($credentials[self::CREDENTIALS_USER]);

		if (!$user) {
			// fail authentication with a custom error
			throw new CustomUserMessageAuthenticationException('Uid could not be found.');
		}

		return $user;
	}

	public function checkCredentials($credentials, UserInterface $user) {
		return $this->passwordEncoder->isPasswordValid($user, $credentials[self::CREDENTIALS_PASS]);
	}

	/**
	 * Used to upgrade (rehash) the user's password automatically over time.
	 */
	public function getPassword($credentials): ?string {
		return $credentials[self::CREDENTIALS_PASS];
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response {
		if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
			return new RedirectResponse($targetPath);
		}

		return new RedirectResponse($this->urlGenerator->generate('default'));
	}

	protected function getLoginUrl() {
		return $this->urlGenerator->generate(self::LOGIN_ROUTE);
	}

	public function authenticate(Request $request): PassportInterface {
		$credentials = $this->getCredentials($request);
		$token = new CsrfToken('global', $credentials[self::CREDENTIALS_CSRF]);
		if (!$this->csrfTokenManager->isTokenValid($token)) {
			throw new InvalidCsrfTokenException();
		}

		$accountRepository = $this->entityManager->getRepository(Account::class);

		$user = $accountRepository->findOneByUsername($credentials[self::CREDENTIALS_USER]);

		if (!$user) {
			// fail authentication with a custom error
			throw new CustomUserMessageAuthenticationException('Uid could not be found.');
		}

		$badges = [];

		if ($request->request->has('_remember_me')) {
			$badges[] = new RememberMeBadge();
		}

		return new Passport($user, new PasswordCredentials($credentials[self::CREDENTIALS_PASS]), $badges);
	}

	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response {
		return null;
	}
}
