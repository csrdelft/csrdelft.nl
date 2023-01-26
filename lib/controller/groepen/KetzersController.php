<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\repository\ChangeLogRepository;
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

	public function __construct(
		ManagerRegistry $registry,
		$groepType = Ketzer::class
	) {
		parent::__construct($registry, $groepType);
		$this->registry = $registry;
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
