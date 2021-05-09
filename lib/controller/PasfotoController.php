<?php


namespace CsrDelft\controller;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\repository\ProfielRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class PasfotoController extends AbstractController
{
	/**
	 * @param Profiel $profiel
	 * @param string $vorm
	 * @return BinaryFileResponse|RedirectResponse
	 * @Route("/profiel/pasfoto/{uid}.jpg", methods={"GET"}, requirements={"uid": ".{4}"}, defaults={"vorm": "civitas"})
	 * @Route("/profiel/pasfoto/{uid}.{vorm}.jpg", methods={"GET"}, requirements={"uid": ".{4}"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function pasfoto(Profiel $profiel, $vorm = 'civitas')
	{
		if (
			$profiel
			&& is_zichtbaar($profiel, 'profielfoto', 'intern')
			&& ($path = $profiel->getPasfotoInternalPath(false, $vorm)) != null
		) {
			$image = new Afbeelding($path);
			return new BinaryFileResponse($image->getFullPath(), 200, [], false);
		}

		return $this->csrRedirect('/images/geen-foto.jpg');
	}
}
