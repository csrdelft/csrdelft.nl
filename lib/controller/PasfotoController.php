<?php


namespace CsrDelft\controller;


use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\ProfielModel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PasfotoController extends AbstractController {
	/**
	 * @var ProfielModel
	 */
	private $profielModel;

	public function __construct(ProfielModel $profielModel) {
		$this->profielModel = $profielModel;
	}

	public function pasfoto($uid, $vorm = 'civitas') {
		$profiel = $this->profielModel::get($uid);
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
