<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use CsrDelft\view\login\LoginForm;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * LoginController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de agenda.
 */
class LoginController extends AbstractController
{
	use TargetPathTrait;

	public function __construct(
		private LoginService $loginService,
		private SuService $suService,
		private RememberLoginRepository $rememberLoginRepository
	) {
	}

	/**
	 * @param Request $request
	 * @param AuthenticationUtils $authenticationUtils
	 * @return Response
	 * @Auth(P_PUBLIC)
	 */
	#[Route(path: '/login', methods: ['GET'])]
	#[Route(path: '/{_locale<%app.supported_locales%>}/login', methods: ['GET'])]
	public function loginForm(
		Request $request,
		AuthenticationUtils $authenticationUtils
	): Response {
		if ($this->getUser()) {
			return $this->redirectToRoute('default');
		}

		$targetPath = $request->query->get('_target_path');
		if ($targetPath) {
			$this->saveTargetPath($request->getSession(), 'main', $targetPath);
		}

		if (
			str_contains(
				(string) $this->getTargetPath($request->getSession(), 'main'),
				'remote-login=true'
			)
		) {
			return $this->redirectToRoute(
				'csrdelft_security_remotelogin_remotelogin'
			);
		}

		$error = $authenticationUtils->getLastAuthenticationError();
		$userName = $authenticationUtils->getLastUsername();

		$loginForm = $this->createFormulier(LoginForm::class, null, [
			'lastUserName' => $userName,
			'lastError' => $error,
		]);

		$response = $this->render('extern/login.html.twig', [
			'loginForm' => $loginForm->createView(),
		]);

		// Als er geredirect wordt, stuur dan een forbidden status
		if ($targetPath) {
			$response->setStatusCode(Response::HTTP_FORBIDDEN);
		}

		return $response;
	}

	/**
	 * @Auth(P_PUBLIC)
	 */
	#[Route(path: '/login_check', name: 'app_login_check', methods: ['POST'])]
	#[
		Route(
			path: '/{_locale<%app.supported_locales%>}/login_check',
			name: 'app_login_check',
			methods: ['POST']
		)
	]
	public function login_check(): never
	{
		throw new LogicException(
			'Deze route wordt opgevangen door de firewall, zie security.firewalls.main.form_login.check_path in config/packages/security.yaml'
		);
	}

	/**
	 * @Auth(P_PUBLIC)
	 */
	#[Route(path: '/logout', name: 'app_logout')]
	public function logout(): never
	{
		throw new LogicException(
			'Deze route wordt opgevangen door de firewall, zie security.firewalls.main.logout.path config/packages/security.yaml'
		);
	}
}
