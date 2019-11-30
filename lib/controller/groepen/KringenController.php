<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\groepen\Kring;
use CsrDelft\model\groepen\KringenModel;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
	public function __construct(KringenModel $kringenModel) {
		parent::__construct($kringenModel);
	}

	public function zoeken(Request $request, $zoekterm = null) {
		if (!$zoekterm && !$request->query->has('q')) {
			throw new CsrToegangException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}
		$zoekterm = '%' . $zoekterm . '%';
		$limit = 5;
		if ($request->query->has('limit')) {
			$limit = $request->query->getInt('limit');
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
		return new JsonResponse($result);
	}

}
