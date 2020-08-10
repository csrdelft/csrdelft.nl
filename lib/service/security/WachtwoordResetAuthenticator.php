<?php


namespace CsrDelft\service\security;


use CsrDelft\common\Mail;
use CsrDelft\controller\WachtwoordController;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\repository\security\OneTimeTokensRepository;
use CsrDelft\view\login\WachtwoordWijzigenForm;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @see WachtwoordController::reset()
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 2020-08-09
 */
class WachtwoordResetAuthenticator extends AbstractAuthenticator {
	/**
	 * @var OneTimeTokensRepository
	 */
	private $oneTimeTokensRepository;
	/**
	 * @var HttpUtils
	 */
	private $httpUtils;
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;

	public function __construct(HttpUtils  $httpUtils, OneTimeTokensRepository $oneTimeTokensRepository, AccountRepository $accountRepository) {
		$this->oneTimeTokensRepository = $oneTimeTokensRepository;
		$this->httpUtils = $httpUtils;
		$this->accountRepository = $accountRepository;
	}

	public function supports(Request $request): ?bool {
		return $request->getSession()->has('wachtwoord_reset_token');
	}

	public function authenticate(Request $request): PassportInterface {
		$token = $request->getSession()->get('wachtwoord_reset_token');

		$user = $this->oneTimeTokensRepository->verifyToken('/wachtwoord/reset', $token);

		if (!$user) {
			$request->getSession()->remove('wachtwoord_reset_token');
			throw new AuthenticationException();
		}

		$form = new WachtwoordWijzigenForm($user, $this->httpUtils->generateUri($request,'wachtwoord_reset'), false);

		if ($form->validate()) {
			// wachtwoord opslaan
			$pass_plain = $form->findByName('wijzigww')->getValue();
			if ($this->accountRepository->wijzigWachtwoord($user, $pass_plain)) {
				setMelding('Wachtwoord instellen geslaagd', 1);
			}

			// token verbruikt
			// (pas na wachtwoord opslaan om meedere pogingen toe te staan als wachtwoord niet aan eisen voldoet)
			$this->oneTimeTokensRepository->discardToken($user->uid, '/wachtwoord/reset');

			// stuur bevestigingsmail
			$profiel = $user->profiel;
			$bericht = "Geachte " . $profiel->getNaam('civitas') .
				",\n\nU heeft recent uw wachtwoord opnieuw ingesteld. Als u dit niet zelf gedaan heeft dan moet u nu direct uw wachtwoord wijzigen en de PubCie op de hoogte stellen.\n\nMet amicale groet,\nUw PubCie";
			$emailNaam = $profiel->getNaam('volledig');
			$mail = new Mail(array($user->email => $emailNaam), '[C.S.R. webstek] Nieuw wachtwoord ingesteld', $bericht);
			$mail->send();

			return new SelfValidatingPassport($user);
		}

		throw new AuthenticationException();
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
		return new RedirectResponse($this->httpUtils->generateUri($request, 'default'));
	}

	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response {
		return null;
	}
}
