<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\repository\groepen\OnderverenigingenRepository;

/**
 * OnderverenigingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor onderverenigingen.
 */
class OnderverenigingenController extends AbstractGroepenController {
	public function __construct(OnderverenigingenRepository $onderverenigingenRepository) {
		parent::__construct($onderverenigingenRepository);
	}
}
