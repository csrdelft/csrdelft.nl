<?php

/**
 * LichtingenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor lichtingen.
 */
class LichtingenController extends AbstractGroepenController {

	public function __construct($query) {
		parent::__construct($query, LichtingenModel::instance());
	}

	public function zoeken() {
		if (!$this->hasParam('q')) {
			return $this->geentoegang();
		}
		$zoekterm = $this->getParam('q');
		$result = array();
		if (is_numeric($zoekterm)) {

			$data = range($this->model->getJongsteLidjaar(), $this->model->getOudsteLidjaar());
			$found = preg_grep('/' . (int) $zoekterm . '/', $data);

			foreach ($found as $lidjaar) {
				$result[] = array(
					'url'	 => '/groepen/lichtingen/' . $lidjaar . '#' . $lidjaar,
					'label'	 => 'Groepen',
					'value'	 => 'Lichting:' . $lidjaar
				);
			}
		}
		$this->view = new JsonResponse($result);
	}

}
