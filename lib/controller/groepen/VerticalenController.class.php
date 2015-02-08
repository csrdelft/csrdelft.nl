<?php

/**
 * VerticalenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor verticalen.
 */
class VerticalenController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, VerticalenModel::instance());
	}

	public function zoeken() {
		if (!$this->hasParam('q')) {
			$this->geentoegang();
		}
		$zoekterm = '%' . $this->getParam('q') . '%';
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int) $this->getParam('limit');
		}
		$result = array();
		foreach ($this->model->find('naam LIKE ? OR letter LIKE ?', array($zoekterm, $zoekterm), null, null, $limit) as $verticale) {
			$result[] = array(
				'url'	 => $verticale->getUrl() . '#' . $verticale->id,
				'value'	 => 'Verticale:' . $verticale->letter
			);
		}
		$this->view = new JsonResponse($result);
	}

}
