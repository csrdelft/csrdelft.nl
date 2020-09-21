<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Verticale;
use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\groepen\VerticalenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * VerticalenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor verticalen.
 */
class VerticalenController extends AbstractGroepenController {
	public function __construct(ChangeLogRepository $changeLogRepository, VerticalenRepository $verticalenRepository) {
		parent::__construct($changeLogRepository, $verticalenRepository);
	}

	public function zoeken(Request $request, $zoekterm = null) {
		if (!$zoekterm && !$request->query->has('q')) {
			throw $this->createAccessDeniedException();
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
		$verticales = $this->repository->createQueryBuilder('v')
			->where('v.naam LIKE :zoekterm')
			->setParameter('zoekterm', $zoekterm)
			->setMaxResults($limit)
			->getQuery()->getResult();

		foreach ($verticales as $verticale) {
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
