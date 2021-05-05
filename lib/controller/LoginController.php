<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\repository\security\RemoteLoginRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use CsrDelft\view\login\LoginForm;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * LoginController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de agenda.
 */
class LoginController extends AbstractController {
	use TargetPathTrait;

	/**
	 * @var LoginService
	 */
	private $loginService;
	/**
	 * @var RememberLoginRepository
	 */
	private $rememberLoginRepository;
	/**
	 * @var SuService
	 */
	private $suService;

	public function __construct(LoginService $loginService, SuService $suService, RememberLoginRepository $rememberLoginRepository) {
		$this->rememberLoginRepository = $rememberLoginRepository;
		$this->loginService = $loginService;
		$this->suService = $suService;
	}

	/**
	 * @param Request $request
	 * @param AuthenticationUtils $authenticationUtils
	 * @return Response
	 * @Route("/login", methods={"GET"})
	 * @Route("/{_locale<%app.supported_locales%>}/login", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function loginForm(Request $request, AuthenticationUtils $authenticationUtils): Response
	{
		if ($this->getUser()) {
			return $this->redirectToRoute('default');
		}

		$targetPath = $request->query->get('_target_path');
		if ($targetPath) {
			$this->saveTargetPath($request->getSession(), 'main', $targetPath);
		}

		$error = $authenticationUtils->getLastAuthenticationError();
		$userName = $authenticationUtils->getLastUsername();

		$loginForm = $this->createFormulier(LoginForm::class, null, ['lastUserName' => $userName, 'lastError' => $error]);

		$response = $this->render('extern/login.html.twig', ['loginForm' => $loginForm->createView()]);

		// Als er geredirect wordt, stuur dan een forbidden status
		if ($targetPath) {
			$response->setStatusCode(Response::HTTP_FORBIDDEN);
		}

		return $response;
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/remote_login")
	 * @Auth(P_PUBLIC)
	 */
	public function remoteLogin(Request $request, RemoteLoginRepository $remoteLoginRepository) : Response {
		$remoteLogin = $remoteLoginRepository->nieuw();

		$remoteLoginRepository->save($remoteLogin);

		$request->getSession()->set('remote_login', $remoteLogin->id);

		return $this->render('extern/remote_login.html.twig', [
			'uuid' => $remoteLogin->uuid,
		]);
	}

	/**
	 * Geeft de huidige status voor een remote_login sessie weer.
	 *
	 * @param Request $request
	 * @return Response
	 * @Route("/remote_login_status", methods={"POST"})
	 * @Auth(P_PUBLIC)
	 */
	public function remoteLoginStatus(Request $request, RemoteLoginRepository $remoteLoginRepository): Response {
		$id = $request->getSession()->get('remote_login');

		if (!$id) {
			throw $this->createNotFoundException();
		}

		$remoteLogin = $remoteLoginRepository->find($id);


		return $this->json($remoteLogin);
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 * @Route("/remote_login_authorize")
	 * @Auth(P_LOGGED_IN)
	 */
	public function remoteLoginAuthorize() {
		return $this->json([]);
	}

	/**
	 * @param Request $request
	 * @Route("/remote_login_qr", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function remoteLoginQr(Request $request) {
		$data = $request->query->get('uuid');
		$result = Builder::create()
			->writer(new PngWriter())
			->writerOptions([])
			->data($this->generateUrl('csrdelft_login_remoteloginauthorize', ['uuid' => $data], UrlGeneratorInterface::ABSOLUTE_URL))
			->encoding(new Encoding('UTF-8'))
			->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
//			->size(300)
//			->margin(10)
			->roundBlockSizeMode(new RoundBlockSizeModeMargin())
//			->logoPath(__DIR__.'/assets/symfony.png')
//			->labelText('This is the label')
//			->labelFont(new NotoSans(20))
//			->labelAlignment(new LabelAlignmentCenter())
			->build();

		return new Response($result->getString(), 200, ['Content-Type' => $result->getMimeType()]);
	}

	/**
	 * @Route("/login_check", name="app_login_check", methods={"POST"})
	 * @Route("/{_locale<%app.supported_locales%>}/login_check", name="app_login_check", methods={"POST"})
	 * @Auth(P_PUBLIC)
	 */
	public function login_check() {
		throw new LogicException('Deze route wordt opgevangen door de firewall, zie security.firewalls.main.form_login.check_path in config/packages/security.yaml');
	}

	/**
	 * @Route("/logout", name="app_logout")
	 * @Auth(P_PUBLIC)
	 */
	public function logout() {
		throw new LogicException('Deze route wordt opgevangen door de firewall, zie security.firewalls.main.logout.path config/packages/security.yaml');
	}
}
