<?php

namespace CsrDelft\controller;

use CsrDelft\view\View;

/**
 * KetzerTovenaar.class.php
 *
 * @author J.P.T. Nederveen <ik@tim365.nl>
 */
class KetzerTovenaarController {
	/**
	 * @return View
	 */
	public function nieuw() {
		return view('ketzertovenaar.ketzertovenaar');
	}
}
