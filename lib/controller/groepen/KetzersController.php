<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\model\groepen\KetzersModel;
use CsrDelft\view\groepen\formulier\GroepAanmakenForm;
use CsrDelft\view\JsonResponse;

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

	public function __construct($query) {
		parent::__construct($query, KetzersModel::instance());
	}

	public function nieuw($soort = null) {
		$form = new GroepAanmakenForm($this->model, $soort);
		if ($this->getMethod() == 'GET') {
			$this->beheren();
			$form->setDataTableId($this->table->getDataTableId());
			$this->view->modal = $form;
		} elseif ($form->validate()) {
			$values = $form->getValues();
			$redirect = $values['model']::instance()->getUrl() . 'aanmaken/' . $values['soort'];
			$this->view = new JsonResponse($redirect);
		} else {
			$this->view = $form;
		}
	}

}
