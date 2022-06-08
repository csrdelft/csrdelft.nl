<?php

namespace CsrDelft\controller\api\v3;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token\OAuth2Token;

class ApiInfoController extends AbstractController
{
	/**
	 * @param Security $security
	 * @return JsonResponse
	 * @Route("/api/v3/profiel")
	 * @Auth(P_LOGGED_IN)
	 */
	public function profiel(Security $security): JsonResponse
	{
		$token = $security->getToken();
		if (!$token instanceof OAuth2Token) {
			throw new BadRequestHttpException();
		}

		$scopes = $token
			->getAttribute('server_request')
			->getAttribute('oauth_scopes', []);

		$user = $this->getUser();

		$json = [
			'id' => $user->uuid->toRfc4122(),
			'displayName' => $this->getUser()->profiel->getNaam(),
			'slug' => $this->getUser()->profiel->getNaam('slug'),
			'scopes' => $scopes,
			'admin' => $security->isGranted('ROLE_ADMIN'),
		];

		if ($security->isGranted('ROLE_OAUTH2_PROFIEL:EMAIL')) {
			$json['email'] = $user->email;
		}

		return new JsonResponse($json);
	}
}
