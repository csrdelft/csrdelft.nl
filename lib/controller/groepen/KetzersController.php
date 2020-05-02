<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\repository\groepen\KetzersRepository;
use CsrDelft\view\groepen\formulier\GroepAanmakenForm;
use CsrDelft\view\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * KetzersController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor ketzers.
 *
 * @property KetzersRepository $model
 */
class KetzersController extends AbstractGroepenController {
	public function __construct(KetzersRepository $ketzersRepository, $entity = Ketzer::class) {
		parent::__construct($ketzersRepository, $entity);
	}

	public function nieuw(Request $request, $id = null, $soort = null) {
		$form = new GroepAanmakenForm($this->model, $soort);
		if ($request->getMethod() == 'GET') {
			return $this->beheren($request);
		} elseif ($form->validate()) {
			$values = $form->getValues();
			$redirect = ContainerFacade::getContainer()->get($values['model'])->getUrl() . '/aanmaken/' . $values['soort'];
			return new JsonResponse($redirect);
		} else {
			return $form;
		}
	}

}
