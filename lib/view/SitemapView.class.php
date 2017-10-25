<?php

namespace CsrDelft\view;

use CsrDelft\model\entity\MenuItem;
use CsrDelft\model\MenuModel;
use CsrDelft\view\formulier\elementen\CollapsableSubkopje;

/**
 * SitemapView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class SitemapView implements View {

	private $model;
	private $levels;
	private $javascript;

	public function __construct($levels = 3) {
		$this->model = MenuModel::instance()->getMenu('main');
		$this->levels = $levels;
	}

	public function getModel() {
		return $this->model;
	}

	public function getTitel() {
		return null;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function view() {
		echo '<ul>';
		foreach ($this->model->getChildren() as $parent) {
			echo '<li>' . $this->viewTree($parent, 1) . '</li>';
		}
		echo '</ul>';
		echo $this->getScriptTag();
	}

	private function viewTree(MenuItem $item, $level) {
		if ($item->magBekijken()) {
			if ($item->hasChildren() AND $level < $this->levels) {
				$kopje = new CollapsableSubkopje($item->item_id, $item->tekst);
				$kopje->h += ($level - 1) * 2;
				$kopje->view();
				$this->javascript .= $kopje->getJavascript();
				echo '<ul>';
				foreach ($item->getChildren() as $child) {
					echo $this->viewTree($child, $level + 1);
				}
				echo '</ul></div>';
			} else {
				echo '<li><a href="' . $item->link . '">' . $item->tekst . '</a></li>';
			}
		}
	}

	private function getScriptTag() {
		return <<<JS
<script type="text/javascript">
$(document).ready(function () {
	{$this->javascript}
});
</script>
JS;
	}

}
