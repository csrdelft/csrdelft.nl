<?php


namespace CsrDelft\controller\api\v3;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\service\BarSysteemService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BarSysteemController
 * @package CsrDelft\controller\api\v3
 * @Route("/api/v3/bar")
 */
class BarSysteemController extends AbstractController
{
	/**
	 * @var BarSysteemService
	 */
	private $barSysteemService;

	public function __construct(BarSysteemService $barSysteemService)
	{
		$this->barSysteemService = $barSysteemService;
	}

	/**
	 * @Route("/updatePerson", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	public function updatePerson(Request $request)
	{
		$id = $request->request->get('id');
		$name = $request->request->get('name');

		return $this->json($this->barSysteemService->updatePerson($id, $name));
	}

	/**
	 * @return JsonResponse
	 * @Route("/personen", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	public function personen()
	{
		return $this->json($this->barSysteemService->getPersonen());
	}

	/**
	 * @return JsonResponse
	 * @Route("/producten", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	public function producten()
	{
		return $this->json($this->barSysteemService->getProducten());
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/bestelling", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	public function bestelling(Request $request)
	{
		$bestelling = $request->request->get("bestelling");
		$data = json_decode($bestelling);
		if (property_exists($data, "oudeBestelling")) {
			$this->barSysteemService->log('update', $_POST);
			return $this->json($this->barSysteemService->updateBestelling($data));
		} else {
			$this->barSysteemService->log('insert', $_POST);
			return $this->json($this->barSysteemService->verwerkBestelling($data));
		}
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/saldo", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	public function saldo(Request $request)
	{
		$soccieSaldoId = $request->request->get('saldoSocCieId');
		return $this->json($this->barSysteemService->getSaldo($soccieSaldoId));
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/verwijderBestelling", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	public function verwijderBestelling(Request $request)
	{
		$this->barSysteemService->log('remove', $_POST);

		$bestelling = (object)$request->request->get('verwijderBestelling');

		return $this->json($this->barSysteemService->verwijderBestelling($bestelling));
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/undoVerwijderBestelling", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	public function undoVerwijderBestelling(Request $request)
	{
		$this->barSysteemService->log('remove', $_POST);
		$data = (object)$request->request->get("undoVerwijderBestelling");
		return $this->json($this->barSysteemService->undoVerwijderBestelling($data));
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/laadLaatste", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 * @IsGranted("ROLE_OAUTH2_BAR:NORMAAL")
	 */
	public function laadLaatste(Request $request)
	{
		$persoon = $request->request->get("aantal");
		$begin = date_create_immutable($request->request->get("begin"));
		$eind = date_create_immutable($request->request->get("eind"));

		if (!$begin || !$eind) {
			throw new BadRequestHttpException("Begin en eind moeten een datum bevatten");
		}

		$productType = $request->request->get("productType", []);
		return $this->json($this->barSysteemService->getBestellingLaatste($persoon, $begin, $eind, $productType));
	}

}
