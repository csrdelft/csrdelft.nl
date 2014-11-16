<?php

require_once 'MVC/view/Zijbalk.static.php';
require_once 'MVC/view/CompressedLayout.abstract.php';
require_once 'MVC/view/MenuView.class.php';
require_once 'MVC/model/MenuModel.class.php';
require_once 'MVC/model/DragObjectModel.class.php';

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
	 * modal inhoud
	 * @var View
	 */
	public $modal;

	public function __construct(View $body, array $zijbalk = array(), $modal = null) {
		parent::__construct('layout', $body, $body->getTitel());
		$this->zijbalk = $zijbalk;
		$this->modal = $modal;
		$this->addCompressedResources('general');
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><img src="' . CSR_PICS . '/knopjes/home-16.png" class="module-icon"></a> » ' . $this->getTitel();
	}

	public function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$smarty = CsrSmarty::instance();
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('scripts', $this->getScripts());
		$smarty->assign('titel', $this->getTitel());
		$smarty->assign('modal', $this->modal);
		$smarty->assign('body', $this->getBody());

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

		if (LoginModel::mag('P_LEDEN_MOD')) {
			require_once 'MVC/model/ForumModel.class.php';
			$smarty->assign('forumcount', ForumPostsModel::instance()->getAantalWachtOpGoedkeuring());

			require_once 'savedquery.class.php';
			$smarty->assign('queues', array(
				'mededelingen' => new SavedQuery(62)
			));
		}

		$smarty->assign('modalcoords', DragObjectModel::getCoords('modal', 175, 200));

		if (LidInstellingen::get('algemeen', 'minion') == 'ja') {
			$smarty->assign('minioncoords', DragObjectModel::getCoords('minion', 40, 40));
			$smarty->assign('minion', $smarty->fetch('minion.tpl'));
		}

		//$dataTable = new DataTable('Example', 3, true);
		//$dataTable->setDataSource('example-data-2.json');
		//$smarty->assign('datatable', $dataTable)

		if (LoginModel::instance()->isPauper()) {
			$smarty->assign('menutree', MenuModel::instance()->getMenu('main'));
			$smarty->assign('loginform', new LoginForm());
			$smarty->display('MVC/layout/pauper.tpl');
		} else {
			$smarty->assign('mainmenu', new MainMenuView());
			// uitzondering voor wiki (geen main table)
			if ($this->body instanceof WikiView) {
				$smarty->display('MVC/layout/wiki.tpl');
			} else {
				$smarty->display('MVC/layout/pagina.tpl');
			}
		}
	}

}
