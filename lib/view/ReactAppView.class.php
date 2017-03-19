<?php

/**
 * ReactAppView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ReactAppView extends CompressedLayout {

	protected $app;

	public function __construct($app, View $body, $titel) {
		parent::__construct('layout', $body, $titel);
		$this->app = $app;
		$this->addCompressedResources('react');
		$this->addScript('https://unpkg.com/babel-core@5.8.38/browser.min.js', true);
	}

	public function getApp() {
		return $this->app;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function view() {
		$smarty = CsrSmarty::instance();
		$smarty->assign('titel', $this->getTitel());
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('scripts', $this->getScripts());
		?><!DOCTYPE html>
		<html>
			<head>
				<?= $smarty->fetch('html_head.tpl') ?>
			</head>
			<body>
				<?= $this->getBody()->view() ?>
				<script type="text/babel" src="/<?= $this->getLayout() ?>/jsx/<?= $this->getApp() ?>.js"></script>
			</body>
		</html>
		<?php
	}

}
