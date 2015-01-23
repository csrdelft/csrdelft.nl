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

	public function overzicht($soort = null) {
		if ($soort) {
			$groepen = $this->model->find('status = ? AND soort = ?', array(GroepStatus::HT, $soort));
		} else {
			$groepen = $this->model->find('status = ?', array(GroepStatus::HT));
		}
		$body = new GroepenView($this->model, $groepen);
		$this->view = new CsrLayoutPage($body);
	}

}
