<?php

namespace CsrDelft\service\security;

use CsrDelft\entity\security\enum\RemoteLoginStatus;
use CsrDelft\repository\security\RemoteLoginRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Uid\Uuid;

/**
 * Class RemoteLoginAuthenticator
 *
 * Kan een sessie maken voor een remote login
 *
 * @package CsrDelft\service\security
 */
class RemoteLoginAuthenticator extends AbstractLoginFormAuthenticator
{
	public function __construct(
		private readonly HttpUtils $httpUtils,
		private readonly RemoteLoginRepository $remoteLoginRepository,
		private readonly AuthenticationSuccessHandlerInterface $successHandler,
		private readonly AuthenticationFailureHandlerInterface $failureHandler
	) {
	}

	public function authenticate(Request $request): Passport
	{
		$uuid = $request->request->get('uuid');

		if (!$uuid) {
			throw new AuthenticationException();
		}

		$remoteLogin = $this->remoteLoginRepository->findOneBy([
			'uuid' => Uuid::fromString($uuid),
		]);

		if (!$remoteLogin) {
			throw new AuthenticationException();
		}

		if (!RemoteLoginStatus::isACCEPTED($remoteLogin->status)) {
			throw new AuthenticationException();
		}

		$user = $remoteLogin->account;

		$badge = new UserBadge($user->getUsername(), fn() => $user);

		return new SelfValidatingPassport($badge);
	}

	public function onAuthenticationSuccess(
		Request $request,
		TokenInterface $token,
		string $firewallName
	): ?Response {
		// Maak deze sessie megakort, wordt alleen gebruikt om een authorize uit te voeren.
		$request->getSession()->migrate(false, 60 * 5);

		return $this->successHandler->onAuthenticationSuccess($request, $token);
	}

	public function onAuthenticationFailure(
		Request $request,
		AuthenticationException $exception
	): Response {
		return $this->failureHandler->onAuthenticationFailure($request, $exception);
	}

	public function supports(Request $request): bool
	{
		return $request->isMethod('POST') &&
			$this->httpUtils->checkRequestPath(
				$request,
				'csrdelft_security_remotelogin_remoteloginfinal'
			);
	}

	protected function getLoginUrl(Request $request): string
	{
		return $this->httpUtils->generateUri(
			$request,
			'csrdelft_security_remotelogin_remotelogin'
		);
	}
}
