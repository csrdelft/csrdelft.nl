<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\view\SmartyTemplateView;

/**
 * Contentclasse voor de boek-bbcode-tag
 */
class BoekBBView extends SmartyTemplateView {

	public function __construct(BoekModel $boek) {
		parent::__construct($boek);
	}

	public function view() {
		$this->smarty->assign('boek', $this->model);
		return $this->smarty->fetch('bibliotheek/boek.bb.tpl');
	}

}
