<?php

/**
 * KetzersController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor ketzers.
 */
class KetzersController extends AbstractGroepenController {

	public function __construct($query) {
		parent::__construct($query, KetzersModel::instance());
	}

	public function nieuw($soort = null) {
		$form = new GroepAanmakenForm($this->model, $soort);
		if (!$this->isPosted()) {
			$this->beheren();
			$form->dataTableId = $this->view->getBody()->getTableId();
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
