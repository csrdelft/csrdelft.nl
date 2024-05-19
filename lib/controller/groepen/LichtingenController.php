<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Lichting;
use CsrDelft\repository\groepen\LichtingenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * LichtingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor lichtingen.
 *
 * @property LichtingenRepository $repository
 */
class LichtingenController extends AbstractGroepenController
{
	public function getGroepType(): string
	{
		return Lichting::class;
	}

	public function zoeken(Request $request, $zoekterm = null): JsonResponse
	{
		if (!$zoekterm && !$request->query->has('q')) {
			throw $this->createAccessDeniedException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}
		$result = [];
		if (is_numeric($zoekterm)) {
			$data = range(
				$this->repository->getJongsteLidjaar(),
				$this->repository->getOudsteLidjaar()
			);
			$found = preg_grep('/' . (int) $zoekterm . '/', $data);

			foreach ($found as $lidjaar) {
				$result[] = [
					'url' => '/groepen/lichtingen/' . $lidjaar . '#' . $lidjaar,
					'label' => 'Groepen',
					'value' => 'Lichting:' . $lidjaar,
				];
			}
		}
		return new JsonResponse($result);
	}

	public function beheren(Request $request, $soort = null): Response
	{
		throw $this->createNotFoundException('Kan geen lichtingen beheren');
	}
}
