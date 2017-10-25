<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\model\bibliotheek\BiebBoek;

/**
 * Contentclasse voor de boek-bbcode-tag
 */
class BoekBBView extends AbstractBibliotheekView {

	public function __construct(BiebBoek $boek) {
		parent::__construct($boek);
	}

	public function view() {
		$this->smarty->assign('boek', $this->model);
		return $this->smarty->fetch('bibliotheek/boek.bb.tpl');
	}

}
