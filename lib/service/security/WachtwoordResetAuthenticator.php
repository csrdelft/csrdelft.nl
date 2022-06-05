<?php

namespace CsrDelft\service\security;

use CsrDelft\common\Mail;
use CsrDelft\controller\WachtwoordController;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\repository\security\OneTimeTokensRepository;
use CsrDelft\service\AccountService;
use CsrDelft\service\MailService;
use CsrDelft\view\login\WachtwoordWijzigenForm;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\HttpUtils;
use Twig\Environment;

/**
 * @see WachtwoordController::reset()
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 2020-08-09
 */
class WachtwoordResetAuthenticator extends AbstractAuthenticator
{
	/**
	 * @var OneTimeTokensRepository
	 */
	private $oneTimeTokensRepository;
	/**
	 * @var HttpUtils
	 */
	private $httpUtils;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var MailService
	 */
	private $mailService;
	/**
	 * @var AccountService
	 */
	private $accountService;

	public function __construct(
		HttpUtils $httpUtils,
		Environment $twig,
		OneTimeTokensRepository $oneTimeTokensRepository,
		AccountService $accountService,
		MailService $mailService
	) {
		$this->oneTimeTokensRepository = $oneTimeTokensRepository;
		$this->httpUtils = $httpUtils;
		$this->twig = $twig;
		$this->mailService = $mailService;
		$this->accountService = $accountService;
	}

	public function supports(Request $request): ?bool
	{
		return $request->getSession()->has('wachtwoord_reset_token');
	}

	public function authenticate(Request $request): PassportInterface
	{
		$token = $request->getSession()->get('wachtwoord_reset_token');

		$user = $this->oneTimeTokensRepository->verifyToken(
			'/wachtwoord/reset',
			$token
		);

		if (!$user) {
			$request->getSession()->remove('wachtwoord_reset_token');
			throw new AuthenticationException();
		}

		$form = new WachtwoordWijzigenForm(
			$user,
			$this->httpUtils->generateUri($request, 'wachtwoord_reset'),
			false
		);

		if ($form->validate()) {
			// wachtwoord opslaan
			$pass_plain = $form->findByName('wijzigww')->getValue();
			if ($this->accountService->wijzigWachtwoord($user, $pass_plain)) {
				setMelding('Wachtwoord instellen geslaagd', 1);
			}

			// token verbruikt
			// (pas na wachtwoord opslaan om meedere pogingen toe te staan als wachtwoord niet aan eisen voldoet)
			$this->oneTimeTokensRepository->discardToken(
				$user->uid,
				'/wachtwoord/reset'
			);

			// stuur bevestigingsmail
			$profiel = $user->profiel;
			$bericht = $this->twig->render(
				'mail/bericht/wachtwoordresetsucces.mail.twig',
				[
					'naam' => $profiel->getNaam('civitas'),
				]
			);
			$mail = new Mail(
				[$user->email => $profiel->getNaam()],
				'[C.S.R. webstek] Nieuw wachtwoord ingesteld',
				$bericht
			);
			$this->mailService->send($mail);

			$badge = new UserBadge($user->getUsername(), function () use ($user) {
				return $user;
			});

			return new SelfValidatingPassport($badge);
		}

		throw new AuthenticationException();
	}

	public function onAuthenticationSuccess(
		Request $request,
		TokenInterface $token,
		string $firewallName
	): ?Response {
		return new RedirectResponse(
			$this->httpUtils->generateUri($request, 'default')
		);
	}

	public function onAuthenticationFailure(
		Request $request,
		AuthenticationException $exception
	): ?Response {
		return null;
	}
}
