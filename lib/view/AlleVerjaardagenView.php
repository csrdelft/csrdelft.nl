<?php

namespace CsrDelft\view;

/**
 * Class AlleVerjaardagenView
 *
 * Laat alle verjaardagen zien
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 */
class AlleVerjaardagenView extends SmartyTemplateView {
	/**
	 * AlleVerjaardagenView constructor.
	 *
	 * @param $model
	 */
	public function __construct($model) {
		parent::__construct($model);
	}

	public function getTitel() {
		return "Verjaardagen";
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	function view() {
		$nu = time();
		$this->smarty->assign('dezemaand', date('n', $nu));
		$this->smarty->assign('dezedag', date('j', $nu));
		$this->smarty->assign('verjaardagen', $this->model);
		$this->smarty->display('verjaardagen/alleverjaardagen.tpl');
	}
}
