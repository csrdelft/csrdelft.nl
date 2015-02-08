<?php

/**
 * LichtingenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor lichtingen.
 */
class LichtingenController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, LichtingenModel::instance());
	}

	public function zoeken() {
		if (!$this->hasParam('q')) {
			$this->geentoegang();
		}
		$zoekterm = $this->getParam('q');
		$data = range($this->model->getJongste(), $this->model->getOudste());
		$found = preg_grep('/' . (int) $zoekterm . '/', $data);
		$result = array();
		foreach ($found as $lidjaar) {
			$result[] = array(
				'url'	 => '/groepen/lichtingen/' . $lidjaar . '#' . $lidjaar->id,
				'value'	 => 'Lichting:' . $lidjaar
			);
		}
		$this->view = new JsonResponse($result);
	}

}
