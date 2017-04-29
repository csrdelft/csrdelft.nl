<?php

require_once 'view/Zijbalk.static.php';
require_once 'view/SitemapView.class.php';
require_once 'view/InstantSearchForm.class.php';
require_once 'view/CompressedLayout.abstract.php';
require_once 'menu/view/MenuView.class.php';
require_once 'menu/model/MenuModel.class.php';
require_once 'model/DragObjectModel.class.php';

/**
 * CsrLayoutPage.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De stek layout van 2006
 */
class CsrLayoutPage extends CompressedLayout {

	/**
	 * Zijbalk
	 * @var View[]
	 */
	public $zijbalk;
	/**
	 * Modal popup inhoud
	 * @var ModalForm
	 */
	public $modal;

	public function __construct(View $body, array $zijbalk = array(), ModalForm $modal = null) {
		parent::__construct('layout', $body, $body->getTitel());
		$this->zijbalk = $zijbalk;
		$this->modal = $modal;
		$this->addCompressedResources('general');
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	public function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$smarty = CsrSmarty::instance();
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('scripts', $this->getScripts());
		$smarty->assign('titel', $this->getTitel());
		$smarty->assign('mainmenu', new MainMenuView());
		$smarty->assign('body', $this->getBody());
		$smarty->assign('modal', $this->modal);

		$breadcrumbs = $this->getBody()->getBreadcrumbs();
		if (!$breadcrumbs) {
			$breadcrumbs = $this->getBreadcrumbs();
		}
		$smarty->assign('breadcrumbs', $breadcrumbs);

		if ($this->zijbalk !== false) {
			if (!is_array($this->zijbalk)) {
				$this->zijbalk = array();
			}
			$this->zijbalk = Zijbalk::addStandaardZijbalk($this->zijbalk);
			if (LidInstellingen::get('zijbalk', 'scrollen') != 'met pagina mee') {
				$smarty->assign('scrollfix', DragObjectModel::getCoords('zijbalk', 0, 0)['top']);
			}
		}
		$smarty->assign('zijbalk', $this->zijbalk);

		if (LidInstellingen::get('layout', 'minion') == 'ja') {
			$smarty->assign('minioncoords', DragObjectModel::getCoords('minion', 40, 40));
			$smarty->assign('minion', $smarty->fetch('minion.tpl'));
		}

		if (LoginModel::instance()->isPauper()) {
			$smarty->assign('loginform', new LoginForm());
			$smarty->assign('mainmenu', new SitemapView());
			$smarty->display('layout/pauper.tpl');
		} else {
			$smarty->display('layout/pagina.tpl');
		}
	}

}
