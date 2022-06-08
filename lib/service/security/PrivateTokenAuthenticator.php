<?php

namespace CsrDelft\service\security;

use CsrDelft\common\Security\PrivateTokenToken;
use CsrDelft\repository\security\AccountRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Authenticate een private token, voor forum rss en agenda ical.
 *
 * @see PrivateTokenToken
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 2020-08-09
 */
class PrivateTokenAuthenticator extends AbstractAuthenticator implements
	RequestMatcherInterface
{
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;

	public function __construct(AccountRepository $accountRepository)
	{
		$this->accountRepository = $accountRepository;
	}

	public function supports(Request $request): ?bool
	{
		return $request->attributes->has('private_auth_token') &&
			preg_match(
				'/^[a-zA-Z0-9]{150}$/',
				$request->attributes->get('private_auth_token')
			);
	}

	public function authenticate(Request $request): Passport
	{
		$token = $request->attributes->get('private_auth_token');

		$user = $this->accountRepository->findOneBy(['private_token' => $token]);

		if (!$user) {
			throw new AuthenticationException('Geen geldige private_token');
		}

		$badge = new UserBadge($user->getUsername(), function () use ($user) {
			return $user;
		});

		return new SelfValidatingPassport($badge);
	}

	public function createToken(
		Passport $passport,
		string $firewallName
	): TokenInterface {
		return new PrivateTokenToken(
			$passport->getUser(),
			$passport->getUser()->getRoles()
		);
	}

	public function onAuthenticationSuccess(
		Request $request,
		TokenInterface $token,
		string $firewallName
	): ?Response {
		return null;
	}

	public function onAuthenticationFailure(
		Request $request,
		AuthenticationException $exception
	): ?Response {
		return new Response('', 403);
	}

	public function matches(Request $request)
	{
		return $this->supports($request);
	}
}
