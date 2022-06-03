<?php


namespace CsrDelft\controller\api\v3;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\service\BarSysteemService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BarSysteemBeheerController
 * @package CsrDelft\controller\api\v3
 * @Route("/api/v3/barbeheer")
 */
class BarSysteemBeheerController extends AbstractController
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
     * @return JsonResponse
     * @Route("/grootboek", methods={"GET"})
     * @Auth(P_LOGGED_IN)
     * @IsGranted("ROLE_OAUTH2_BAR:BEHEER")
     */
    public function grootboek()
    {
        return $this->json($this->barSysteemService->getGrootboekInvoer());
    }

    /**
     * @return JsonResponse
     * @Route("/grootboeken", methods={"GET"})
     * @Auth(P_LOGGED_IN)
     * @IsGranted("ROLE_OAUTH2_BAR:BEHEER")
     */
    public function grootboeken()
    {
        return $this->json($this->barSysteemService->getGrootboeken());
    }

    /**
     * @return JsonResponse
     * @Route("/tools", methods={"GET"})
     * @Auth(P_LOGGED_IN)
     * @IsGranted("ROLE_OAUTH2_BAR:BEHEER")
     */
    public function tools()
    {
        return $this->json($this->barSysteemService->getToolData());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/addProduct", methods={"POST"})
     * @Auth(P_LOGGED_IN)
     * @IsGranted("ROLE_OAUTH2_BAR:BEHEER")
     */
    public function addProduct(Request $request)
    {
        $name = $request->request->get('name');
        $price = $request->request->get('price');
        $type = $request->request->get('grootboekId');

        return $this->json($this->barSysteemService->addProduct($name, $price, $type));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/updatePrice", methods={"POST"})
     * @Auth(P_LOGGED_IN)
     * @IsGranted("ROLE_OAUTH2_BAR:BEHEER")
     */
    public function updatePrice(Request $request)
    {
        $productId = $request->request->get('productId');
        $price = $request->request->get('price');

        return $this->json($this->barSysteemService->updatePrice($productId, $price));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/updateVisibility", methods={"POST"})
     * @Auth(P_LOGGED_IN)
     * @IsGranted("ROLE_OAUTH2_BAR:BEHEER")
     */
    public function updateVisibility(Request $request)
    {
        $visibility = $request->request->get('visibility');
        $productId = $request->request->get('productId');

        return $this->json($this->barSysteemService->updateVisibility($productId, $visibility));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/addPerson", methods={"POST"})
     * @Auth(P_LOGGED_IN)
     * @IsGranted("ROLE_OAUTH2_BAR:BEHEER")
     */
    public function addPerson(Request $request)
    {
        $name = $request->request->get('name');
        $saldo = $request->request->get('saldo');
        $uid = $request->request->get('uid');

        return $this->json($this->barSysteemService->addPerson($name, $saldo, $uid));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/removePerson", methods={"POST"})
     * @Auth(P_LOGGED_IN)
     * @IsGranted("ROLE_OAUTH2_BAR:BEHEER")
     */
    public function removePerson(Request $request)
    {
        $id = $request->request->get('id');

        return $this->json($this->barSysteemService->removePerson($id));
    }

}
