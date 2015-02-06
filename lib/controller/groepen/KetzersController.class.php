<?php

/**
 * KetzersController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor ketzers.
 */
class KetzersController extends GroepenController {

	public function __construct($query, KetzersModel $model = null) {
		parent::__construct($query, $model);
		if ($model === null) {
			$this->model = KetzersModel::instance();
		}
	}

	public function nieuw($soort = null) {
		$form = new GroepAanmakenForm($this->model, $soort);
		if (!$this->isPosted()) {
			$this->beheren();
			$form->tableId = $this->view->getBody()->getTableId();
			$this->view->modal = $form;
		} elseif ($form->validate()) {
			$values = $form->getValues();
			$model = $values['model']::instance();
			$this->view = new GroepForm($model->nieuw($values['soort']), $model->getUrl() . 'aanmaken'); // checks rechten aanmaken
		} else {
			$this->view = $form;
		}
	}

}
