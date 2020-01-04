<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\model\groepen\KetzersModel;
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
 * @property KetzersModel $model
 */
class KetzersController extends AbstractGroepenController {
	public function __construct(KetzersModel $ketzersModel) {
		parent::__construct($ketzersModel);
	}

	public function nieuw(Request $request, $id = null, $soort = null) {
		$form = new GroepAanmakenForm($this->model, $soort);
		if ($request->getMethod() == 'GET') {
			return $this->beheren($request);
		} elseif ($form->validate()) {
			$values = $form->getValues();
			$redirect = $values['model']::instance()->getUrl() . '/aanmaken/' . $values['soort'];
			return new JsonResponse($redirect);
		} else {
			return $form;
		}
	}

}
