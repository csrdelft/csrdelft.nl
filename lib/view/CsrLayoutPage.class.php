<?php

namespace CsrDelft\view;

use CsrDelft\model\DragObjectModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\LidToestemmingModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\ModalForm;
use CsrDelft\view\login\LoginForm;
use CsrDelft\view\menu\MainMenuView;
use CsrDelft\view\toestemming\ToestemmingModalForm;


/**
 * CsrLayoutPage.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De stek layout van 2006
 */
class CsrLayoutPage extends HtmlPage {

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
		parent::__construct($body, $body->getTitel());
		$this->zijbalk = $zijbalk;
		$this->modal = $modal;
		$this->addScript('/dist/js/manifest.js');
		$this->addScript('/dist/js/extern-vendor.js');
		$this->addScript('/dist/js/vendor.js');
		$this->addScript('/dist/js/app.js');
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	/**
	 * @throws \SmartyException
	 */
	public function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$smarty = CsrSmarty::instance();
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('titel', $this->getTitel());
		$smarty->assign('mainmenu', new MainMenuView());
		$smarty->assign('body', $this->getBody());

		if ($this->modal) {
			$smarty->assign('modal', $this->modal);
		} elseif (!LidToestemmingModel::toestemmingGegeven()) {
			$smarty->assign('modal', new ToestemmingModalForm());
		}

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
			if (LidInstellingenModel::get('zijbalk', 'scrollen') != 'met pagina mee') {
				$smarty->assign('scrollfix', DragObjectModel::getCoords('zijbalk', 0, 0)['top']);
			}
		}
		$smarty->assign('zijbalk', $this->zijbalk);

		if (LidInstellingenModel::get('layout', 'minion') == 'ja') {
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
