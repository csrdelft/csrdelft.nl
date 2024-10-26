<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\Afbeelding;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use const P_LEDEN_MOD;

class PasfotoController extends AbstractController
{
	/**
	 * @param Profiel $profiel
	 * @param string $vorm
	 * @return BinaryFileResponse|RedirectResponse
	 * @Auth(P_LEDEN_READ)
	 */
	#[
		Route(
			path: '/profiel/pasfoto/{uid}.jpg',
			methods: ['GET'],
			requirements: ['uid' => '.{4}'],
			defaults: ['vorm' => 'civitas']
		)
	]
	#[
		Route(
			path: '/profiel/pasfoto/{uid}.{vorm}.jpg',
			methods: ['GET'],
			requirements: ['uid' => '.{4}']
		)
	]
	public function pasfoto(Request $request, Profiel $profiel, $vorm = 'civitas')
	{
		if (
			$profiel &&
			InstellingUtil::is_zichtbaar(
				$profiel,
				'profielfoto',
				'intern',
				P_LEDEN_MOD
			) &&
			($path = $profiel->getPasfotoInternalPath($vorm)) != null
		) {
			$image = new Afbeelding($path);
			return new BinaryFileResponse($image->getFullPath(), 200, [], false);
		}

		return $this->redirect(
			$request->getSchemeAndHttpHost() . '/images/geen-foto.jpg'
		);
	}
}
