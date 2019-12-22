<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\model\groepen\WoonoordenModel;

/**
 * WoonoordenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor woonoorden en huizen.
 */
class WoonoordenController extends AbstractGroepenController {
	public function __construct(WoonoordenModel $woonoordenModel) {
		parent::__construct($woonoordenModel);
	}
}
