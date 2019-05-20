<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\ketzertovenaar\KetzerTovenaarView;

/**
 * KetzerTovenaar.class.php
 *
 * @author J.P.T. Nederveen <ik@tim365.nl>
 */
class KetzerTovenaarController {
	/**
	 * @throws \Exception
	 */
	public function nieuw() {
		$view = new KetzerTovenaarView();
		$view->view();
	}
}
