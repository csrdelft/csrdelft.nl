<?php

/**
 * KringenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor kringen.
 */
class KringenController extends GroepenController {

	public function __construct($query) {
		parent::__construct($query, KringenModel::instance());
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
		foreach ($this->model->find('naam LIKE ?', array($zoekterm), null, null, $limit) as $kring) {
			$result[] = array(
				'url'	 => $kring->getUrl() . '#' . $kring->id,
				'label'	 => $kring->familie,
				'value'	 => 'Kring:' . $kring->verticale . '.' . $kring->kring_nummer
			);
		}
		$this->view = new JsonResponse($result);
	}

}
