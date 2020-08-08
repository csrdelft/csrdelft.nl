<?php

namespace CsrDelft\service\security;

use CsrDelft\common\Security\JwtToken;
use Exception;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class ApiAuthenticator extends AbstractAuthenticator {
	private $userProvider;
	/**
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;

	public function __construct(UserProviderInterface $userProvider, TokenStorageInterface $tokenStorage) {
		$this->userProvider = $userProvider;
		$this->tokenStorage = $tokenStorage;
	}

	public function supports(Request $request): ?bool {
		if (null !== $this->tokenStorage->getToken()) {
			return false;
		}

		if (!$request->server->has('HTTP_X_CSR_AUTHORIZATION')) {
			return false;
		}

		return null;
	}

	public function authenticate(Request $request): PassportInterface {
		$authHeader = $request->server->get('HTTP_X_CSR_AUTHORIZATION');

		$jwt = substr($authHeader, 7);

		if (!$jwt) {
			throw new AuthenticationException(400);
		}

		try {
			$token = JWT::decode($jwt, env('JWT_SECRET'), array('HS512'));
		} catch (Exception $e) {
			throw new AuthenticationException('', 401);
		}

		$user = $this->userProvider->loadUserByUsername($token->data->userId);

		if (!$user instanceof UserInterface) {
			throw new AuthenticationServiceException('The user provider must return a UserInterface object.');
		}

		return new SelfValidatingPassport($user);
	}

	public function createAuthenticatedToken(PassportInterface $passport, string $firewallName): TokenInterface {
		return new PostAuthenticationToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
		return null;
	}

	public function start(Request $request, AuthenticationException $authException = null) {
		return new Response("", 401);
	}

	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response {
		return new Response("", 401);
	}
}
