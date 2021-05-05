<?php


namespace CsrDelft\controller\security;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\security\enum\RemoteLoginStatus;
use CsrDelft\repository\security\RemoteLoginRepository;
use CsrDelft\service\security\RemoteLoginAuthenticator;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
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
	 * @Route("/remote_login")
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
	 * Geeft de huidige status voor een remote_login sessie weer.
	 *
	 * @param Request $request
	 * @return Response
	 * @Route("/remote_login_status", methods={"POST"})
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
	 * @param Request $request
	 * @return Response
	 * @Route("/remote_login_authorize", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function remoteLoginAuthorize(Request $request): Response
	{
		$uuid = $request->query->get('uuid');

		$remoteLogin = $this->remoteLoginRepository->findOneBy(['uuid' => Uuid::fromString($uuid)]);

		if (!$remoteLogin) {
			throw $this->createNotFoundException();
		}

		$remoteLogin->status = RemoteLoginStatus::ACTIVE();
		$remoteLogin->expires = date_create_immutable()->add(new \DateInterval('PT3M'));

		$this->getDoctrine()->getManager()->flush();

		return $this->render('security/remote_login_authorize.html.twig', ['uuid' => $uuid]);
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/remote_login_authorize", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function remoteLoginAuthorizePost(Request $request): Response
	{
		$uuid = $request->request->get('uuid');

		$remoteLogin = $this->remoteLoginRepository->findOneBy(['uuid' => Uuid::fromString($uuid)]);

		if (!$remoteLogin) {
			throw $this->createNotFoundException();
		}

		if ($request->request->has('cancel')) {
			$remoteLogin->status = RemoteLoginStatus::REJECTED();
		} else {
			$remoteLogin->status = RemoteLoginStatus::ACCEPTED();
			$remoteLogin->account = $this->getUser();
		}

		$this->getDoctrine()->getManager()->flush();

		return $this->redirectToRoute('csrdelft_security_remotelogin_remoteloginauthorizesuccess');
	}

	/**
	 * @return Response
	 * @Route("/remote_login_success")
	 * @Auth(P_LOGGED_IN)
	 */
	public function remoteLoginAuthorizeSuccess(): Response
	{
		return $this->render('security/remote_login_authorized.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/remote_login_final", methods={"POST"})
	 * @Auth(P_PUBLIC)
	 * @see RemoteLoginAuthenticator
	 */
	public function remoteLoginFinal(): Response {
		throw new \LogicException("Moet opgevangen worden door RemoteLoginAuthenticator");
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/remote_login_qr", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function remoteLoginQr(Request $request): Response
	{
		$data = $request->query->get('uuid');

		$url = $this->generateUrl(
			'csrdelft_security_remotelogin_remoteloginauthorize',
			['uuid' => $data],
			UrlGeneratorInterface::ABSOLUTE_URL
		);
		$result = Builder::create()
			->writer(new PngWriter())
			->writerOptions([])
			->data($url)
			->encoding(new Encoding('UTF-8'))
			->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
			->roundBlockSizeMode(new RoundBlockSizeModeMargin())
			->build();

		return new Response($result->getString(), 200, ['Content-Type' => $result->getMimeType()]);
	}

}
