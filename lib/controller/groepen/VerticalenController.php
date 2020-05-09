<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\groepen\Verticale;
use CsrDelft\repository\groepen\VerticalenRepository;
use CsrDelft\view\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * VerticalenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor verticalen.
 */
class VerticalenController extends AbstractGroepenController {
	public function __construct(VerticalenRepository $verticalenRepository) {
		parent::__construct($verticalenRepository);
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
		$result = [];
		foreach ($this->model->find('naam LIKE ?', [$zoekterm], null, null, $limit) as $verticale) {
			/** @var Verticale $verticale */
			$result[] = [
				'url' => $verticale->getUrl() . '#' . $verticale->id,
				'label' => $verticale->naam,
				'value' => 'Verticale:' . $verticale->letter
			];
		}
		return new JsonResponse($result);
	}

}
