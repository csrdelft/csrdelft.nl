<?php

/**
 * MededelingView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MededelingView extends TemplateView {

	public function __construct(Mededeling $model) {
		parent::__construct($model);
	}

	public function getTitel() {
		return 'Mededeling';
	}

	public function view() {
		if (is_int($this->model)) {
			echo '<div id="menu-item-' . $this->menus . '" class="remove"></div>';
		} else {
			$this->assign('item', $this->menus);
			$this->display('menu/beheer/menu_item.tpl');
		}
	}

}

?>