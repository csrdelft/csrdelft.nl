<?php


namespace CsrDelft\service\security;


use CsrDelft\common\Security\PrivateTokenToken;
use CsrDelft\repository\security\AccountRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;

/**
 * Authenticate een private token, voor forum rss en agenda ical.
 *
 * @see PrivateTokenToken
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 2020-08-09
 */
class PrivateTokenAuthenticator extends AbstractAuthenticator {
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;

	public function __construct(AccountRepository $accountRepository) {
		$this->accountRepository = $accountRepository;
	}

	public function supports(Request $request): ?bool {
		return $request->query->has('private_token')
			&& preg_match('/^[a-zA-Z0-9]{150}$/', $request->query->get('private_token'));
	}

	public function authenticate(Request $request): PassportInterface {
		$token = $request->query->get('private_token');

		$user = $this->accountRepository->findOneBy(['private_token' => $token]);

		return new SelfValidatingPassport($user);

	}

	public function createAuthenticatedToken(PassportInterface $passport, string $firewallName): TokenInterface {
		if (!$passport instanceof UserPassportInterface) {
			throw new LogicException("Gegeven Passport bevat geen user.");
		}

		return new PrivateTokenToken($passport->getUser());
	}


	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
		return null;
	}

	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response {
		return new Response("", 403);
	}
}
