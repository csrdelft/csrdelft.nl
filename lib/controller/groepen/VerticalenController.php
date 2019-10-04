<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\model\entity\groepen\Verticale;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\view\JsonResponse;

/**
 * VerticalenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor verticalen.
 */
class VerticalenController extends AbstractGroepenController {

	public function __construct($query) {
		parent::__construct($query, VerticalenModel::instance());
	}

	public function zoeken($zoekterm = null) {
		if (!$zoekterm && !$this->hasParam('q')) {
			$this->exit_http(403);
		}
		if (!$zoekterm) {
			$zoekterm = $this->getParam('q');
		}
		$zoekterm = '%' . $zoekterm . '%';
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int)$this->getParam('limit');
		}
		$result = [];
		foreach ($this->model->find('naam LIKE ?', [$zoekterm], null, null, $limit) as $verticale) {
			/** @var Verticale $verticale */
			$result[] = [
				'url' => $verticale->getUrl() . '#' . $verticale->id,
				'label' => $verticale->naam,
				'value' => 'Verticale:' . $verticale->letter
			];
		}
		$this->view = new JsonResponse($result);
	}

}
