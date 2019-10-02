<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\model\entity\groepen\Kring;
use CsrDelft\model\groepen\KringenModel;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;

/**
 * KringenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor kringen.
 *
 * @property KringenModel $model
 */
class KringenController extends AbstractGroepenController {

	public function __construct($query) {
		parent::__construct($query, KringenModel::instance());
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
		$result = array();
		foreach ($this->model->find('naam LIKE ?', array($zoekterm), null, null, $limit) as $kring) {
			/** @var Kring $kring */
			$result[] = array(
				'url' => $kring->getUrl() . '#' . $kring->id,
				'label' => $kring->familie,
				'icon' => Icon::getTag('Kring'),
				'value' => 'Kring:' . $kring->verticale . '.' . $kring->kring_nummer
			);
		}
		$this->view = new JsonResponse($result);
	}

}
