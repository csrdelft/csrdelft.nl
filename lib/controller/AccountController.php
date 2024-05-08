<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccessService;
use CsrDelft\service\AccountService;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\login\AccountForm;
use CsrDelft\view\login\UpdateLoginForm;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/07/2019
 */
class AccountController extends AbstractController
{
	public function __construct(
		private readonly CacheInterface $cache,
		private readonly AccessService $accessService,
		private readonly AccountRepository $accountRepository,
		private readonly AccountService $accountService,
		private readonly CmsPaginaRepository $cmsPaginaRepository,
		private readonly LoginService $loginService
	) {
	}

	/**
	 * @param null $uid
	 * @return RedirectResponse
	 * @Auth(P_ADMIN)
	 */
	#[
		Route(
			path: '/account/{uid}/aanmaken',
			methods: ['GET', 'POST'],
			requirements: ['uid' => '.{4}']
		)
	]
	public function aanmaken($uid = null): RedirectResponse
	{
		if ($uid == null) {
			$account = $this->getUser();
		} else {
			$account = $this->accountRepository->find($uid);
		}

		if ($account) {
			$this->addFlash(FlashType::INFO, 'Account bestaat al');
		} else {
			$account = $this->accountService->maakAccount($uid);
			if ($account) {
				$this->addFlash('Account succesvol aangemaakt', FlashType::SUCCESS);
			} else {
				throw new CsrGebruikerException('Account aanmaken gefaald');
			}
		}
		return $this->redirectToRoute('csrdelft_account_bewerken', ['uid' => $uid]);
	}

	/**
	 * @param Request $request
	 * @param Security $security
	 * @param null $uid
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/account/{uid}/bewerken',
			methods: ['GET', 'POST'],
			requirements: ['uid' => '.{4}']
		)
	]
	#[Route(path: '/account/bewerken', methods: ['GET', 'POST'])]
	public function bewerken(Request $request, $uid = null): Response
	{
		$eigenAccount = $this->getUser();
		if ($uid == null) {
			$uid = $this->getUid();
		}
		if ($uid !== $this->getUid() && !$this->mag(P_ADMIN)) {
			throw $this->createAccessDeniedException();
		}
		$account = $this->accountRepository->find($uid);
		if (!$account) {
			$this->addFlash(FlashType::ERROR, 'Account bestaat niet');
			throw $this->createAccessDeniedException();
		}
		// Het is alleen toegestaan om een account te bewerken als er recent met een wachtwoord is ingelogd.
		// En dus niet met een rememberme token, want dit geeft minder garantie dat de eigenaar van het account
		// de actie uitvoert.
		// Als dit niet het geval is moet de gebruiker zijn wachtwoord geven om de huidige sessie te converteren
		// naar een 'recent_password_login' sessie. Deze sessie blijft gelden totdat de sessie verloopt en de
		// rememberme token wordt gebruikt om een nieuwe sessie aan te vragen.
		if (
			$this->loginService->getAuthenticationMethod() !==
			AuthenticationMethod::recent_password_login
		) {
			$action = $this->generateUrl('csrdelft_account_bewerken', [
				'uid' => $uid,
			]);
			$form = new UpdateLoginForm($action);

			// Reset loginmoment naar nu als de gebruiker zijn wachtwoord geeft.
			if (
				$form->validate() &&
				$this->accountService->controleerWachtwoord(
					$eigenAccount,
					$form->getValues()['pass']
				)
			) {
				$this->loginService->setRecentLoginToken();
			} else {
				$this->addFlash(
					FlashType::WARNING,
					'U bent niet recent ingelogd, vul daarom uw wachtwoord in om uw account te wijzigen.'
				);
				return $this->render('default.html.twig', [
					'content' => new UpdateLoginForm($action),
				]);
			}
		}
		if (!$this->accessService->isUserGranted($account, 'ROLE_LOGGED_IN')) {
			$this->addFlash(FlashType::WARNING, 'Account mag niet inloggen');
		}
		$form = $this->createFormulier(AccountForm::class, $account, [
			'action' => $this->generateUrl('csrdelft_account_bewerken', [
				'uid' => $account->uid,
			]),
		]);
		$role = $account->perm_role;
		$form->handleRequest($request);
		if ($form->validate()) {
			if ($account->username == '') {
				$account->username = $account->uid;
			}
			// username, email & wachtwoord opslaan
			$this->accountService->wijzigWachtwoord($account, $account->pass_plain);
			$this->addFlash(FlashType::INFO, 'Inloggegevens wijzigen geslaagd');
			if ($account->perm_role != $role) {
				// Flush alle caches
				// Dit zorgt er voor dat alle rechten en menus opnieuw worden berekend.
				// Op dit moment is het niet mogelijk om gedeeltes van de cache weggooien.
				$this->cache->clear();
			}
		}
		$account->eraseCredentials();
		return $this->render('default.html.twig', [
			'content' => $form->createView(),
		]);
	}

	/**
	 * @return Response
	 * @Auth(P_PUBLIC)
	 */
	#[
		Route(
			path: '/account/{uid}/aanvragen',
			methods: ['GET', 'POST'],
			requirements: ['uid' => '.{4}']
		)
	]
	public function aanvragen(): Response
	{
		return $this->render('default.html.twig', [
			'content' => $this->cmsPaginaRepository->find('accountaanvragen'),
		]);
	}

	/**
	 * @param null $uid
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/account/{uid}/verwijderen',
			methods: ['POST'],
			requirements: ['uid' => '.{4}']
		)
	]
	public function verwijderen($uid = null): JsonResponse
	{
		if ($uid == null) {
			$uid = $this->getUid();
		}
		if ($uid !== $this->getUid() && !$this->mag(P_ADMIN)) {
			throw $this->createAccessDeniedException();
		}
		$account = $this->accountRepository->find($uid);
		if (!$account) {
			$this->addFlash(FlashType::ERROR, 'Account bestaat niet');
		} else {
			try {
				$this->accountRepository->delete($account);
				$this->addFlash(FlashType::SUCCESS, 'Account succesvol verwijderd');
			} catch (Exception) {
				$this->addFlash(FlashType::ERROR, 'Account verwijderen mislukt');
			}
		}
		return new JsonResponse('/profiel/' . $uid); // redirect
	}
}
