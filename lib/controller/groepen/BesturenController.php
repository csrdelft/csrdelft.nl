<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\repository\groepen\BesturenRepository;

/**
 * BesturenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor besturen.
 */
class BesturenController extends AbstractGroepenController {
	public function __construct(BesturenRepository $besturenModel) {
		parent::__construct($besturenModel);
	}
}
