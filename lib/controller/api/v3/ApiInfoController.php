<?php


namespace CsrDelft\controller\api\v3;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiInfoController extends AbstractController
{
	/**
	 * @return JsonResponse
	 * @Route("/api/v3/profiel")
	 * @Auth(P_LOGGED_IN)
	 */
	public function profiel() {
		return new JsonResponse([
			'id' => $this->getUser()->getUsername(),
			'displayName' => $this->getUser()->profiel->getNaam('volledig'),
			'email' => $this->getUser()->email,
		]);
	}

}
