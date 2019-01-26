<?php

namespace CsrDelft\view\ketzertovenaar;

use CsrDelft\view\SmartyTemplateView;

class KetzerTovenaarView extends SmartyTemplateView {

	/**
	 * KetzerTovenaarView constructor.
	 */
	public function __construct() {
		parent::__construct(null, 'Ketzertovenaar');
	}

	/**
	 * @throws \Exception
	 */
	public function view() {
		display('ketzertovenaar.ketzertovenaar');
	}

	public function getBreadcrumbs() {
		return null;
	}
}
