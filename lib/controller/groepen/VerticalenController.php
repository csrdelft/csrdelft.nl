<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\common\CsrToegangException;
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
	const NAAM = 'verticalen';

	public function __construct() {
		parent::__construct(VerticalenModel::instance());
	}

	public function zoeken($zoekterm = null) {
		if (!$zoekterm && !$this->hasParam('q')) {
			throw new CsrToegangException();
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
