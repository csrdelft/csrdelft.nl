<?php

namespace CsrDelft\controller\groepen;

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
	public function __construct(BesturenRepository $besturenRepository) {
		parent::__construct($besturenRepository);
	}

	public function overzicht($soort = null) {
		$groepen = $this->model->findBy([]);
		$body = new GroepenView($this->model, $groepen, $soort); // controleert rechten bekijken per groep
		return view('default', ['content' => $body]);
	}
}
