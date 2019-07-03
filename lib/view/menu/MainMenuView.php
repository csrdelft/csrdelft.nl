<?php
/**
 * MainMenuView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */

namespace CsrDelft\view\menu;

use CsrDelft\model\MenuModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\InstantSearchForm;
use CsrDelft\view\View;

class MainMenuView implements View {

	public function view() {
		view('menu.main', [
			'root' => MenuModel::instance()->getMenu('main'),
			'favorieten' => MenuModel::instance()->getMenu(LoginModel::getUid()),
			'zoekbalk' => new InstantSearchForm(),
		])->view();
	}

	public function getTitel() {
		// TODO: Implement getTitel() method.
	}

	public function getBreadcrumbs() {
		// TODO: Implement getBreadcrumbs() method.
	}

	/**
	 * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
	 */
	public function getModel() {
		// TODO: Implement getModel() method.
	}
}
