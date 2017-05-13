<?php
/**
 * KomendeVerjaardagenView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */

namespace CsrDelft\view;

/**
 * Class KomendeVerjaardagenView
 *
 * Laat komende verjaardagen zien
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 */
class KomendeVerjaardagenView extends SmartyTemplateView {
	private $toonpasfotos;

	public function __construct($model, $toonpasfotos) {
		parent::__construct($model);
		$this->toonpasfotos = $toonpasfotos;
	}

	function view() {
		$this->smarty->assign('verjaardagen', $this->model);
		$this->smarty->assign('toonpasfotos', $this->toonpasfotos);
		$this->smarty->display('verjaardagen/komendeverjaardagen.tpl');
	}
}