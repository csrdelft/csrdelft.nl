<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\model\groepen\BesturenModel;

/**
 * BesturenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor besturen.
 */
class BesturenController extends AbstractGroepenController {
	const NAAM = 'besturen';

	public function __construct() {
		parent::__construct(BesturenModel::instance());
	}
}
