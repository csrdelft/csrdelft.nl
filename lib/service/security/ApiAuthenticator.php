<?php

namespace CsrDelft\service\security;

use CsrDelft\common\Security\JwtToken;
use CsrDelft\common\Security\JwtTokenBadge;
use CsrDelft\common\Security\PersistentTokenProvider;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\security\AccountRepository;
use Exception;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;
use Symfony\Component\Security\Http\HttpUtils;

class ApiAuthenticator extends AbstractAuthenticator {
	/**
	 * @var UserProviderInterface
	 */
	private $userProvider;
	/**
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;
	/**
	 * @var HttpUtils
	 */
	private $httpUtils;
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var PersistentTokenProvider
	 */
	private $tokenProvider;

	public function __construct(
		UserProviderInterface $userProvider,
		PersistentTokenProvider $tokenProvider,
		TokenStorageInterface $tokenStorage,
		HttpUtils $httpUtils,
		AccountRepository $accountRepository
	) {
		$this->userProvider = $userProvider;
		$this->tokenStorage = $tokenStorage;
		$this->httpUtils = $httpUtils;
		$this->accountRepository = $accountRepository;
		$this->tokenProvider = $tokenProvider;
	}

	public function supports(Request $request): ?bool {
		if ($this->isAuthorizePath($request) || $this->isRefreshPath($request)) {
			return true;
		}

		if (null !== $this->tokenStorage->getToken()) {
			return false;
		}


		if (!$request->server->has('HTTP_X_CSR_AUTHORIZATION')) {
			return false;
		}

		return null;
	}

	private function isAuthorizePath(Request $request) {
		return $request->isMethod('POST')
			&& $this->httpUtils->checkRequestPath($request, '/API/2.0/auth/authorize')
			&& $request->request->has('user')
			&& $request->request->has('pass');
	}

	private function isRefreshPath(Request $request) {
		return $request->isMethod('POST')
			&& $this->httpUtils->checkRequestPath($request, '/API/2.0/auth/token')
			&& $request->request->has('refresh_token');
	}

	public function authenticate(Request $request): PassportInterface {
		if ($request->server->get('HTTP_X_CSR_AUTHORIZATION')) {
			return $this->authenticateHeader($request);
		}

		if ($this->isAuthorizePath($request)) {
			return $this->authorizeRequest($request);
		}

		if ($this->isRefreshPath($request)) {
			return $this->refreshRequest($request);
		}

		throw new LogicException("This request is not supported.");
	}

	private function authenticateHeader(Request $request) {
		$authHeader = $request->server->get('HTTP_X_CSR_AUTHORIZATION');

		$jwt = substr($authHeader, 7);

		if (!$jwt) {
			throw new AuthenticationException(400);
		}

		try {
			$token = JWT::decode($jwt, $_ENV['JWT_SECRET'], ['HS512']);
		} catch (Exception $e) {
			throw new AuthenticationException('', 401);
		}

		$user = $this->userProvider->loadUserByUsername($token->data->userId);

		if (!$user instanceof UserInterface) {
			throw new AuthenticationServiceException('The user provider must return a UserInterface object.');
		}

		return new SelfValidatingPassport($user);
	}

	private function authorizeRequest(Request $request) {
		$credentials = [
			'username' => $request->request->get('user'),
			'password' => $request->request->get('pass'),
		];

		/** @var Account $user */
		$user = $this->userProvider->loadUserByUsername($credentials['username']);

		if (!$user) {
			throw new AuthenticationException();
		}

		$timeout = $this->accountRepository->moetWachten($user);

		if ($timeout != 0) {
			throw new AuthenticationException("Moet wachten");
		}

		$validPassword = $this->accountRepository->controleerWachtwoord($user, $credentials['password']);

		if (!$validPassword) {
			$this->accountRepository->failedLoginAttempt($user);

			throw new AuthenticationException();
		}

		$this->accountRepository->successfulLoginAttempt($user);

		$token = $this->createJwtToken($user->uid);

		// Generate a refresh token
		$series = crypto_rand_token(255);
		$rand = crypto_rand_token(255);

		$_SERVER['HTTP_USER_AGENT'] = 'API 2.0: ' . filter_var(strval($_SERVER['HTTP_USER_AGENT']), FILTER_SANITIZE_STRING);

		$this->tokenProvider->createNewToken(
			new PersistentToken(
				Account::class,
				$user->uid,
				$series,
				hash('sha512', $rand),
				date_create()
			)
		);

		$refreshToken = $this->createRefreshToken($series, $rand);

		return new Passport($user, new PasswordCredentials($credentials['password']), [new JwtTokenBadge($token, $refreshToken)]);
	}

	private function createJwtToken(string $userId): string {
		// Generate new JWT
		$tokenId = base64_encode(openssl_random_pseudo_bytes(32));
		$issuedAt = time();

		$data = [
			'iat' => $issuedAt,
			'exp' => $issuedAt + $_ENV['JWT_LIFETIME'],
			'jti' => $tokenId,
			'data' => [
				'userId' => $userId
			]
		];

		// Encode the new JWT
		return JWT::encode($data, $_ENV['JWT_SECRET'], 'HS512');
	}

	private function createRefreshToken(string $series, string $token) {
		return base64_encode(implode(':', [$series, $token]));
	}

	private function refreshRequest(Request $request) {
		// Filter posted data
		$refresh_token = filter_var($request->request->get('refresh_token'), FILTER_SANITIZE_STRING);

		[$series, $rand] = $this->unpackRefreshToken($refresh_token);

		$remember = $this->tokenProvider->loadTokenBySeries($series);

		if (!$remember || $remember->getTokenValue() != hash('sha512', $rand)) {
			throw new UnauthorizedHttpException('Unauthorized');
		}

		$token = $this->createJwtToken($remember->getUsername());

		$user = $this->userProvider->loadUserByUsername($remember->getUserName());

		return new SelfValidatingPassport($user, [new JwtTokenBadge($token, null)]);
	}

	private function unpackRefreshToken(string $refreshToken) {
		return explode(':', base64_decode($refreshToken));
	}

	public function createAuthenticatedToken(PassportInterface $passport, string $firewallName): TokenInterface {
		$jwtBadge = $passport->getBadge(JwtTokenBadge::class);
		if ($passport instanceof UserPassportInterface && $jwtBadge instanceof JwtTokenBadge) {
			return new JwtToken($passport->getUser(), $jwtBadge->getToken(), $jwtBadge->getRefreshToken(), $firewallName, $passport->getUser()->getRoles());
		}

		if ($passport instanceof SelfValidatingPassport) {
			return new PostAuthenticationToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
		}

		throw new LogicException("Cannot create token for this passport");
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
		if ($token instanceof JwtToken) {
			return new JsonResponse([
				'token' => $token->getToken(),
				'refreshToken' => $token->getRefreshToken(),
			]);
		}

		// This request was just authenticated, continue
		return null;
	}

	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response {
		return new Response("", 401);
	}
}
