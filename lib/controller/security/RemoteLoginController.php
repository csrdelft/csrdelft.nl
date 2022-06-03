<?php


namespace CsrDelft\controller\security;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\security\enum\RemoteLoginStatus;
use CsrDelft\repository\security\RemoteLoginRepository;
use CsrDelft\service\security\RemoteLoginAuthenticator;
use DateInterval;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Writer\SvgWriter;
use LogicException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class RemoteLoginController extends AbstractController
{
	/**
	 * @var RemoteLoginRepository
	 */
	private $remoteLoginRepository;

	public function __construct(RemoteLoginRepository $remoteLoginRepository)
	{
		$this->remoteLoginRepository = $remoteLoginRepository;
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/remote-login")
	 * @Auth(P_PUBLIC)
	 */
	public function remoteLogin(Request $request): Response
	{
		$remoteLogin = $this->remoteLoginRepository->nieuw();

		$this->remoteLoginRepository->save($remoteLogin);

		$request->getSession()->set('remote_login', $remoteLogin->id);

		return $this->render('extern/remote_login.html.twig', [
			'uuid' => $remoteLogin->uuid,
		]);
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/remote-login-refresh", methods={"POST"})
	 * @Auth(P_PUBLIC)
	 */
	public function remoteLoginRefresh(Request $request): Response
	{
		$id = $request->getSession()->get('remote_login');

		if (!$id) {
			throw $this->createAccessDeniedException();
		}

		$remoteLogin = $this->remoteLoginRepository->find($id);

		if (!$remoteLogin) {
			throw $this->createAccessDeniedException();
		}

		$this->remoteLoginRepository->refresh($remoteLogin);

		$this->remoteLoginRepository->save($remoteLogin);

		$request->getSession()->set('remote_login', $remoteLogin->id);

		return $this->json($remoteLogin, 200, [], ['groups' => ['json']]);
	}

	/**
	 * Geeft de huidige status voor een remote_login sessie weer.
	 *
	 * @param Request $request
	 * @return Response
	 * @Route("/remote-login-status", methods={"POST"})
	 * @Auth(P_PUBLIC)
	 */
	public function remoteLoginStatus(Request $request): Response
	{
		$id = $request->getSession()->get('remote_login');

		if (!$id) {
			throw $this->createNotFoundException();
		}

		$remoteLogin = $this->remoteLoginRepository->find($id);

		if ($remoteLogin->expires < date_create_immutable()) {
			$remoteLogin->status = RemoteLoginStatus::EXPIRED();
		}

		$this->getDoctrine()->getManager()->flush();

		return $this->json($remoteLogin, 200, [], ['groups' => ['json']]);
	}

	/**
	 * @param $uuid
	 * @return Response
	 * @Route("/rla/{uuid}", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function remoteLoginAuthorizeRedirect($uuid) : Response
	{
		return new RedirectResponse(
			$this->generateUrl('csrdelft_security_remotelogin_remoteloginauthorize', ['uuid' => $uuid]));
	}

	/**
	 * @param $uuid
	 * @return Response
	 * @Route("/remote-login-authorize/{uuid}", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function remoteLoginAuthorize($uuid): Response
	{
		$remoteLogin = $this->remoteLoginRepository->findOneBy(['uuid' => Uuid::fromString($uuid)]);

		if (!$remoteLogin) {
			throw $this->createNotFoundException();
		}

		if (RemoteLoginStatus::isEXPIRED($remoteLogin->status)) {
			throw new CsrGebruikerException("Deze link is verlopen! Probeer een nieuwe link");
		}

		$remoteLogin->status = RemoteLoginStatus::ACTIVE();
		$remoteLogin->expires = date_create_immutable()->add(new DateInterval('PT3M'));

		$this->getDoctrine()->getManager()->flush();

		return $this->render('security/remote_login_authorize.html.twig', ['uuid' => $uuid]);
	}

	/**
	 * @param Request $request
	 * @param $uuid
	 * @return Response
	 * @Route("/remote-login-authorize/{uuid}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function remoteLoginAuthorizePost(Request $request, $uuid): Response
	{
		$remoteLogin = $this->remoteLoginRepository->findOneBy(['uuid' => Uuid::fromString($uuid)]);

		if (!$remoteLogin) {
			throw $this->createNotFoundException();
		}

		if ($request->request->has('cancel')) {
			$remoteLogin->status = RemoteLoginStatus::REJECTED();
			$this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('default');
		} else {
			$remoteLogin->status = RemoteLoginStatus::ACCEPTED();
			$remoteLogin->account = $this->getUser();
			$this->getDoctrine()->getManager()->flush();
		}


		return $this->redirectToRoute('csrdelft_security_remotelogin_remoteloginauthorizesuccess');
	}

	/**
	 * @return Response
	 * @Route("/remote-login-success")
	 * @Auth(P_LOGGED_IN)
	 */
	public function remoteLoginAuthorizeSuccess(): Response
	{
		return $this->render('security/remote_login_authorized.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/remote-login-final", methods={"POST"})
	 * @Auth(P_PUBLIC)
	 * @see RemoteLoginAuthenticator
	 */
	public function remoteLoginFinal(): Response {
		throw new LogicException("Moet opgevangen worden door RemoteLoginAuthenticator");
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/remote-login-qr", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function remoteLoginQr(Request $request): Response
	{
		$data = $request->query->get('uuid');

		$url = $this->generateUrl(
			'csrdelft_security_remotelogin_remoteloginauthorizeredirect',
			['uuid' => $data],
			UrlGeneratorInterface::ABSOLUTE_URL
		);
		$result = Builder::create()
			->writer(new SvgWriter())
			->writerOptions([])
			->data($url)
			->encoding(new Encoding('UTF-8'))
			->errorCorrectionLevel(new ErrorCorrectionLevelLow())
			->build();

		return new Response($result->getString(), 200, ['Content-Type' => $result->getMimeType()]);
	}

}
