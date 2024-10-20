<?php

namespace CsrDelft\controller;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\common\Mail;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\repository\security\OneTimeTokensRepository;
use CsrDelft\service\AccessService;
use CsrDelft\service\AccountService;
use CsrDelft\service\MailService;
use CsrDelft\service\security\WachtwoordResetAuthenticator;
use CsrDelft\view\login\WachtwoordVergetenForm;
use CsrDelft\view\login\WachtwoordWijzigenForm;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/07/2019
 */
class WachtwoordController extends AbstractController
{
	public function __construct(
		private readonly AccountRepository $accountRepository,
		private readonly AccountService $accountService,
		private readonly OneTimeTokensRepository $oneTimeTokensRepository,
		private readonly AccessService $accessService,
		private readonly MailService $mailService
	) {
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/wachtwoord/wijzigen',
			methods: ['GET', 'POST'],
			name: 'wachtwoord_wijzigen'
		)
	]
	#[Route(path: '/wachtwoord/verlopen', methods: ['GET', 'POST'])]
	public function wijzigen(): Response
	{
		$account = $this->getUser();
		// mag inloggen?
		if (
			!$account ||
			!$this->accessService->isUserGranted($account, 'ROLE_LOGGED_IN')
		) {
			throw $this->createAccessDeniedException();
		}
		$form = new WachtwoordWijzigenForm(
			$account,
			$this->generateUrl('wachtwoord_wijzigen')
		);
		if ($form->validate()) {
			// wachtwoord opslaan
			$pass_plain = $form->findByName('wijzigww')->getValue();
			$this->accountService->wijzigWachtwoord($account, $pass_plain);
			$this->addFlash(FlashType::SUCCESS, 'Wachtwoord instellen geslaagd');
		}
		return $this->render('default.html.twig', ['content' => $form]);
	}

	/**
	 * Wordt opgevangen door WachtwoordResetAuthenticator zodra wachtwoord_reset_token in de sessie staat.
	 *
	 * @param Request $request
	 * @return Response
	 * @Auth(P_PUBLIC)
	 * @throws NonUniqueResultException
	 * @see WachtwoordResetAuthenticator
	 *
	 */
	#[Route(path: '/wachtwoord/reset', name: 'wachtwoord_reset')]
	public function reset(Request $request): Response
	{
		$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

		if ($token) {
			$request->getSession()->set('wachtwoord_reset_token', $token);

			return $this->redirectToRoute('wachtwoord_reset');
		}

		$token = $request->getSession()->get('wachtwoord_reset_token');

		if (!$token) {
			throw $this->createNotFoundException('Geen token gevonden');
		}

		$account = $this->oneTimeTokensRepository->verifyToken(
			'/wachtwoord/reset',
			$token
		);
		if ($account == null) {
			throw $this->createAccessDeniedException();
		}

		$form = new WachtwoordWijzigenForm(
			$account,
			$this->generateUrl('wachtwoord_reset'),
			false
		);

		if ($form->isPosted()) {
			$form->validate();
		}

		return $this->render('default.html.twig', ['content' => $form]);
	}

	/**
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Auth(P_PUBLIC)
	 */
	#[Route(path: '/wachtwoord/vergeten', methods: ['GET', 'POST'])]
	#[
		Route(
			path: '/wachtwoord/aanvragen',
			methods: ['GET', 'POST'],
			name: 'wachtwoord_aanvragen'
		)
	]
	public function vergeten(): Response
	{
		$form = new WachtwoordVergetenForm();
		if ($form->isPosted() && $form->validate()) {
			$values = $form->getValues();
			$account = $this->accountRepository->findOneByEmail($values['mail']);

			// mag wachtwoord reset aanvragen?
			// (mag ook als na verify($tokenString) niet ingelogd is met wachtwoord en dus AuthenticationMethod::url_token is)
			if (
				!$account ||
				!$this->accessService->isUserGranted($account, 'ROLE_LOGGED_IN')
			) {
				$this->addFlash(FlashType::ERROR, 'E-mailadres onjuist');

				return $this->render('default.html.twig', ['content' => $form]);
			}
			if (
				$this->oneTimeTokensRepository->hasToken(
					$account->uid,
					'/wachtwoord/reset'
				)
			) {
				$this->oneTimeTokensRepository->discardToken(
					$account->uid,
					'/wachtwoord/reset'
				);
			}

			$token = $this->oneTimeTokensRepository->createToken(
				$account,
				'/wachtwoord/reset'
			);
			// stuur resetmail
			$this->verzendResetMail($account, $token);

			$this->addFlash(FlashType::SUCCESS, 'Wachtwoord reset email verzonden');
		}
		return $this->render('default.html.twig', ['content' => $form]);
	}

	private function verzendResetMail(Account $account, $token)
	{
		$profiel = $account->profiel;

		$url = $this->generateUrl('wachtwoord_reset', ['token' => $token[0]]);
		$bericht = $this->renderView('mail/bericht/wachtwoord_vergeten.mail.twig', [
			'naam' => $profiel->getNaam('civitas'),
			'mogelijkTot' => DateUtil::dateFormatIntl(
				$token[1],
				DateUtil::DATETIME_FORMAT
			),
			'url' => $url,
		]);
		$emailNaam = $profiel->getNaam('volledig', true); // Forceer, want gebruiker is niet ingelogd en krijgt anders 'civitas'
		$mail = new Mail(
			[$account->email => $emailNaam],
			'[C.S.R. webstek] Wachtwoord vergeten',
			$bericht
		);
		$this->mailService->send($mail);
	}
}
