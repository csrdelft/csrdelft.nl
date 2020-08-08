<?php

namespace CsrDelft\service\security;

use CsrDelft\common\Security\JwtToken;
use CsrDelft\common\Security\JwtTokenBadge;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\repository\security\RememberLoginRepository;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\HttpUtils;

class ApiJwtAuthenticator extends AbstractAuthenticator {
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
	 * @var RememberLoginRepository
	 */
	private $rememberLoginRepository;

	public function __construct(HttpUtils $httpUtils, UserProviderInterface $userProvider, TokenStorageInterface $tokenStorage, AccountRepository  $accountRepository, RememberLoginRepository  $rememberLoginRepository) {
		$this->userProvider = $userProvider;
		$this->tokenStorage = $tokenStorage;
		$this->httpUtils = $httpUtils;
		$this->accountRepository = $accountRepository;
		$this->rememberLoginRepository = $rememberLoginRepository;
	}

	public function supports(Request $request): ?bool {
		return $request->isMethod('POST') && $this->httpUtils->checkRequestPath($request, '/API/2.0/auth/authorize');
	}

	public function authenticate(Request $request): PassportInterface {
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

		// Generate JWT
		$tokenId = base64_encode(openssl_random_pseudo_bytes(32));
		$issuedAt = time();

		$data = [
			'iat' => $issuedAt,
			'exp' => $issuedAt + env('JWT_LIFETIME'),
			'jti' => $tokenId,
			'data' => [
				'userId' => $user->uid
			]
		];

		// Encode the JWT
		$token = JWT::encode($data, env('JWT_SECRET'), 'HS512');

		// Generate a refresh token
		$rand = crypto_rand_token(255);

		// Save the refresh token
		$remember = $this->rememberLoginRepository->nieuw();
		$remember->lock_ip = false;
		$remember->device_name = 'API 2.0: ' . filter_var(strval($_SERVER['HTTP_USER_AGENT']), FILTER_SANITIZE_STRING);
		$remember->token = hash('sha512', $rand);
		$this->rememberLoginRepository->save($remember);

		return new Passport($user, new PasswordCredentials($credentials['password']), [new JwtTokenBadge($token, $rand)]);
	}

	public function createAuthenticatedToken(PassportInterface $passport, string $firewallName): TokenInterface {
		/** @var JwtTokenBadge $jwtBadge */
		$jwtBadge = $passport->getBadge(JwtTokenBadge::class);

		return new JwtToken($passport->getUser(), $jwtBadge->getToken(), $jwtBadge->getRefreshToken(), $firewallName, $passport->getUser()->getRoles());
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
		return new JsonResponse([
			'token' => $token->getToken(),
			'refreshToken' => $token->getRefreshToken(),
		]);
	}

	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response {
		return new Response("", 401);
	}
}
