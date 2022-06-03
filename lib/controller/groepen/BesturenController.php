<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Bestuur;
use CsrDelft\view\groepen\GroepenView;
use Doctrine\Persistence\ManagerRegistry;

/**
 * BesturenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor besturen.
 */
class BesturenController extends AbstractGroepenController {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Bestuur::class);
	}

	public function overzicht($soort = null) {
		// Zoek ook op ot,ft
		$groepen = $this->repository->findBy([]);
		// controleert rechten bekijken per groep
		$body = new GroepenView($this->repository, $groepen);
		return $this->render('default.html.twig', ['content' => $body]);
	}
}
