<?php


namespace CsrDelft\controller\api\v3;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ApiInfoController extends AbstractController
{
	/**
	 * @param Security $security
	 * @return JsonResponse
	 * @Route("/api/v3/profiel")
	 * @Auth(P_LOGGED_IN)
	 */
	public function profiel(Security $security) {
		if ($security->isGranted('ROLE_OAUTH2_PROFIEL:EMAIL')) {
			return new JsonResponse([
				'id' => $this->getUser()->getUsername(),
				'displayName' => $this->getUser()->profiel->getNaam(),
				'email' => $this->getUser()->email,
			]);
		} else {
			return new JsonResponse([
				'id' => $this->getUser()->getUsername(),
				'displayName' => $this->getUser()->profiel->getNaam(),
			]);
		}
	}

}
