<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\view\SmartyTemplateView;
use CsrDelft\view\View;

/**
 * BeheerMaaltijdenView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Toon het maaltijdensysteem menu en een body
 *
 */
class BeheerMaaltijdenView extends SmartyTemplateView {
	public function __construct(View $model, $titel = false) {
		parent::__construct($model, $titel);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->model->view();
	}
}
