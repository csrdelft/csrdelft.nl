<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\view\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * LichtingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor lichtingen.
 *
 * @property LichtingenModel $model
 */
class LichtingenController extends AbstractGroepenController {
	public function __construct(LichtingenModel $lichtingenModel) {
		parent::__construct($lichtingenModel);
	}

	public function zoeken(Request $request, $zoekterm = null) {
		if (!$zoekterm && !$request->query->has('q')) {
			throw new CsrToegangException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}
		$result = array();
		if (is_numeric($zoekterm)) {

			$data = range($this->model->getJongsteLidjaar(), $this->model->getOudsteLidjaar());
			$found = preg_grep('/' . (int)$zoekterm . '/', $data);

			foreach ($found as $lidjaar) {
				$result[] = array(
					'url' => '/groepen/lichtingen/' . $lidjaar . '#' . $lidjaar,
					'label' => 'Groepen',
					'value' => 'Lichting:' . $lidjaar
				);
			}
		}
		return new JsonResponse($result);
	}

}
