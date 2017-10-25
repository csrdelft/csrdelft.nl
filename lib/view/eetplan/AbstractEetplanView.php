<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\view\SmartyTemplateView;


/**
 * AbstractEetplanView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Weergeven van eetplan.
 */
abstract class AbstractEetplanView extends SmartyTemplateView {

	protected $lichting;

	public function __construct($model, $lichting) {
		parent::__construct($model);
		$this->lichting = $lichting;
	}

	public function getTitel() {
		return 'Eetplan';
	}

	public function getBreadcrumbs() {
		return '<a href="/agenda" title="Agenda"><span class="fa fa-calendar module-icon"></span></a> » <a href="/eetplan">Eetplan</a>';
	}

}
