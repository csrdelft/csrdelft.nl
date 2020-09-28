<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\repository\groepen\BesturenRepository;
use CsrDelft\view\groepen\GroepenView;

/**
 * BesturenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor besturen.
 */
class BesturenController extends AbstractGroepenController {
	public function __construct(ChangeLogRepository $changeLogRepository, BesturenRepository $besturenRepository) {
		parent::__construct($changeLogRepository, $besturenRepository);
	}

	public function overzicht($soort = null) {
		$groepen = $this->repository->findBy([]);
		$body = new GroepenView($this->repository, $groepen, $soort); // controleert rechten bekijken per groep
		return $this->render('default.html.twig', ['content' => $body]);
	}
}
