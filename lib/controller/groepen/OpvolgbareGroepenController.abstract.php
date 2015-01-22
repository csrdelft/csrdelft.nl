<?php

require_once 'controller/groepen/GroepenController.class.php';

/**
 * OpvolgbareGroepenController.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor opvolgbare groepen.
 */
abstract class OpvolgbareGroepenController extends GroepenController {

	public function __construct($query, OpvolgbareGroepenModel $model) {
		parent::__construct($query, $model);
	}

	public function overzicht($status = null) {
		if (in_array($status, GroepStatus::getTypeOptions())) {
			$groepen = $this->model->find('status = ?', array($status));
		} else {
			$groepen = $this->model->find('status = ?', array(GroepStatus::HT));
		}
		$body = new GroepenView($this->model, $groepen);
		$this->view = new CsrLayoutPage($body);
	}

}
