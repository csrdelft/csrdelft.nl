<?php

namespace CsrDelft\controller;

use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\profiel\Profiel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
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
	public function pasfoto(Profiel $profiel, string $vorm): BinaryFileResponse|RedirectResponse
	{
		if (
			InstellingUtil::is_zichtbaar(
				$profiel,
				'profielfoto',
				'intern',
				P_LEDEN_MOD
			) &&
			($path = $profiel->getPasfotoInternalPath($vorm)) != null
		) {
			$resp = new BinaryFileResponse(file: $path, status: 200, autoEtag: true);
		} else {
			$resp = $this->redirect('/images/geen-foto.jpg', status: 301);
		}
		$resp->setPrivate();
		$resp->setClientTtl(24*3600);
		return $resp;
	}
}
