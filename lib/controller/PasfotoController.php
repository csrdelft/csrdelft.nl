<?php


namespace CsrDelft\controller;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\repository\ProfielRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;

class PasfotoController extends AbstractController {
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(ProfielRepository $profielRepository) {
		$this->profielRepository = $profielRepository;
	}

	/**
	 * @param $uid
	 * @param string $vorm
	 * @return BinaryFileResponse|\Symfony\Component\HttpFoundation\RedirectResponse
	 * @Route("/profiel/pasfoto/{uid}.jpg", methods={"GET"}, requirements={"uid": ".{4}"}, defaults={"vorm": "civitas"})
	 * @Route("/profiel/pasfoto/{uid}.{vorm}.jpg", methods={"GET"}, requirements={"uid": ".{4}"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function pasfoto($uid, $vorm = 'civitas') {
		$profiel = $this->profielRepository::get($uid);
		if (!$profiel) {
			return $this->csrRedirect('/images/geen-foto.jpg');
		}
		if (!is_zichtbaar($profiel, 'profielfoto', 'intern')) {
			return $this->csrRedirect('/images/geen-foto.jpg');
		}
		$path = $profiel->getPasfotoInternalPath(false, $vorm);
		if ($path === null) {
			return $this->csrRedirect('/images/geen-foto.jpg');
		}
		$image = new Afbeelding($path);
		return new BinaryFileResponse($image->getFullPath(), 200, [], false);
	}
}
