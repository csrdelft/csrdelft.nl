<?php

/**
 * SitemapView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class SitemapView implements View {

	public $levels = 2;
	private $javascript;

	public function getModel() {
		return MenuModel::instance()->getMenu('main');
	}

	public function getTitel() {
		return null;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function view() {
		foreach ($this->getModel()->getChildren() as $parent) {
			echo $this->viewTree($parent, 1);
		}
		echo $this->getScriptTag();
	}

	private function viewTree(MenuItem $item, $level) {
		if ($item->magBekijken()) {
			if ($item->hasChildren() AND $level < $this->levels) {
				$kopje = new CollapsableSubkopje($item->item_id, $item->tekst, true, true);
				$kopje->h += $level - 1;
				$kopje->view();
				$this->javascript .= $kopje->getJavascript();
				foreach ($item->getChildren() as $child) {
					echo $this->viewTree($child, $level++);
				}
				echo '</div>';
			} else {
				echo '<a href="' . $item->link . '">' . $item->tekst . '</a>';
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
