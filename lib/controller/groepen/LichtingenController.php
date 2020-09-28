<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\groepen\LichtingenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * LichtingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor lichtingen.
 *
 * @property LichtingenRepository $repository
 */
class LichtingenController extends AbstractGroepenController {
	public function __construct(ChangeLogRepository $changeLogRepository, LichtingenRepository $lichtingenRepository) {
		parent::__construct($changeLogRepository, $lichtingenRepository);
	}

	public function zoeken(Request $request, $zoekterm = null) {
		if (!$zoekterm && !$request->query->has('q')) {
			throw $this->createAccessDeniedException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}
		$result = array();
		if (is_numeric($zoekterm)) {

			$data = range($this->repository->getJongsteLidjaar(), $this->repository->getOudsteLidjaar());
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
