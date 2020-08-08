<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\common\Mail;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\repository\security\OneTimeTokensRepository;
use CsrDelft\service\AccessService;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\login\WachtwoordVergetenForm;
use CsrDelft\view\login\WachtwoordWijzigenForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/07/2019
 */
class WachtwoordController extends AbstractController {
	/**
	 * @var LoginService
	 */
	private $loginService;
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var OneTimeTokensRepository
	 */
	private $oneTimeTokensRepository;

	public function __construct(LoginService $loginService, AccountRepository $accountRepository, OneTimeTokensRepository $oneTimeTokensRepository) {
		$this->loginService = $loginService;
		$this->accountRepository = $accountRepository;
		$this->oneTimeTokensRepository = $oneTimeTokensRepository;
	}

	/**
	 * @return TemplateView
	 * @Route("/wachtwoord/wijzigen", methods={"GET", "POST"}, name="wachtwoord_wijzigen")
	 * @Route("/wachtwoord/verlopen", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function wijzigen() {
		$account = LoginService::getAccount();
		// mag inloggen?
		if (!$account || !AccessService::mag($account, P_LOGGED_IN)) {
			throw new CsrToegangException();
		}
		$form = new WachtwoordWijzigenForm($account, $this->generateUrl('wachtwoord_wijzigen'));
		if ($form->validate()) {
			// wachtwoord opslaan
			$pass_plain = $form->findByName('wijzigww')->getValue();
			$this->accountRepository->wijzigWachtwoord($account, $pass_plain);
			setMelding('Wachtwoord instellen geslaagd', 1);
		}
		return view('default', ['content' => $form]);
	}

	/**
	 * @param Request $request
	 * @return TemplateView|RedirectResponse
	 * @throws NonUniqueResultException
	 * @throws ORMException
	 * @Route("/wachtwoord/reset", name="wachtwoord_reset")
	 * @Auth(P_PUBLIC)
	 */
	public function reset(Request $request) {
		$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

		if ($token) {
			$this->getSessionService()->set('wachtwoord_reset_token', $token);

			return $this->redirectToRoute('wachtwoord_reset');
		}

		$token = $this->getSessionService()->get('wachtwoord_reset_token');

		if (!$token) {
			throw $this->createNotFoundException('Geen token gevonden');
		}

		$account = $this->oneTimeTokensRepository->verifyToken('/wachtwoord/reset', $token);
		if ($account == null) {
			throw new CsrToegangException();
		}

		$form = new WachtwoordWijzigenForm($account, $this->generateUrl('wachtwoord_reset'), false);
		if ($form->validate()) {
			// wachtwoord opslaan
			$pass_plain = $form->findByName('wijzigww')->getValue();
			if ($this->accountRepository->wijzigWachtwoord($account, $pass_plain)) {
				setMelding('Wachtwoord instellen geslaagd', 1);
			}
			// token verbruikt
			// (pas na wachtwoord opslaan om meedere pogingen toe te staan als wachtwoord niet aan eisen voldoet)
			$this->oneTimeTokensRepository->discardToken($account->uid, '/wachtwoord/reset');
			// inloggen alsof gebruiker wachtwoord heeft ingevoerd
			$this->loginService->login($request, $account);

			// stuur bevestigingsmail
			$profiel = $account->profiel;
			$bericht = "Geachte " . $profiel->getNaam('civitas') .
				",\n\nU heeft recent uw wachtwoord opnieuw ingesteld. Als u dit niet zelf gedaan heeft dan moet u nu direct uw wachtwoord wijzigen en de PubCie op de hoogte stellen.\n\nMet amicale groet,\nUw PubCie";
			$emailNaam = $profiel->getNaam('volledig');
			$mail = new Mail(array($account->email => $emailNaam), '[C.S.R. webstek] Nieuw wachtwoord ingesteld', $bericht);
			$mail->send();
			return $this->redirectToRoute('default');
		}
		return view('default', ['content' => $form]);
	}

	/**
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/wachtwoord/vergeten", methods={"GET", "POST"})
	 * @Auth(P_PUBLIC)
	 */
	public function vergeten() {
		$form = new WachtwoordVergetenForm();
		if ($form->isPosted() && $form->validate()) {

			$values = $form->getValues();
			$account = $this->accountRepository->findOneByEmail($values['mail']);

			// mag wachtwoord reset aanvragen?
			// (mag ook als na verify($tokenString) niet ingelogd is met wachtwoord en dus AuthenticationMethod::url_token is)
			if (!$account || !AccessService::mag($account, P_LOGGED_IN, AuthenticationMethod::getEnumValues())) {
				setMelding('E-mailadres onjuist', -1);

				return view('default', ['content' => $form]);
			}
			if ($this->oneTimeTokensRepository->hasToken($account->uid, '/wachtwoord/reset')) {
				$this->oneTimeTokensRepository->discardToken($account->uid, '/wachtwoord/reset');
			}

			$token = $this->oneTimeTokensRepository->createToken($account->uid, '/wachtwoord/reset');
			// stuur resetmail
			$this->verzendResetMail($account, $token);

			setMelding('Wachtwoord reset email verzonden', 1);
		}
		return view('default', ['content' => $form]);
	}

	private function verzendResetMail(Account $account, $token) {
		$profiel = $account->profiel;

		$url = CSR_ROOT . "/wachtwoord/reset?token=" . rawurlencode($token[0]);
		$bericht = "Geachte " . $profiel->getNaam('civitas') .
			",\n\nU heeft verzocht om uw wachtwoord opnieuw in te stellen. Dit is mogelijk met de onderstaande link tot " . date_format_intl($token[1], DATETIME_FORMAT) .
			".\n\n[url=" . $url .
			"]Wachtwoord instellen[/url].\n\nAls dit niet uw eigen verzoek is kunt u dit bericht negeren.\n\nMet amicale groet,\nUw PubCie";
		$emailNaam = $profiel->getNaam('volledig', true); // Forceer, want gebruiker is niet ingelogd en krijgt anders 'civitas'
		$mail = new Mail(array($account->email => $emailNaam), '[C.S.R. webstek] Wachtwoord vergeten', $bericht);
		$mail->send();
	}

	private function getSessionService(): SessionInterface {
		/** @var Request $request */
		$request = $this->container->get('request_stack')->getCurrentRequest();

		return $request->getSession();
	}
}
