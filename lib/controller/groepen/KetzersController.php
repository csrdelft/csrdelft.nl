<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\repository\groepen\KetzersRepository;
use CsrDelft\view\groepen\formulier\GroepAanmakenForm;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * KetzersController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor ketzers.
 *
 * @property KetzersRepository $repository
 */
class KetzersController extends AbstractGroepenController
{
	/**
	 * @var ManagerRegistry
	 */
	private $registry;

	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry);
		$this->registry = $registry;
	}

	public function getGroepType(): string
	{
		return Ketzer::class;
	}

	public function nieuw(Request $request, $id = null, $soort = null)
	{
		$form = new GroepAanmakenForm($this->registry, $this->repository, $soort);
		if ($request->getMethod() == 'GET') {
			return $this->beheren($request);
		} elseif ($form->validate()) {
			$values = $form->getValues();
			$redirect =
				$this->registry->getRepository($values['model'])->getUrl() .
				'/aanmaken/' .
				$values['soort'];
			return new JsonResponse($redirect);
		} else {
			return $form;
		}
	}
}
