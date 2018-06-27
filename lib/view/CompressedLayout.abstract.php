<?php

namespace CsrDelft\view;

use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\security\LoginModel;

/**
 * CompressedLayout.abstract.php
 *
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Gebruikt .ini files voor stylesheets en scripts per module en layout.
 *
 * @see htdocs/tools/css.php
 * @see htdocs/tools/js.php
 */
abstract class CompressedLayout extends HtmlPage {

	/**
	 * CompressedLayout constructor.
	 *
	 * @param View $body
	 * @param string $titel
	 */
	public function __construct(View $body, $titel) {
		parent::__construct($body, $titel);

		foreach ($this->getUserModules() as $module) {
		    parent::addStylesheet('/dist/css/' . $module . '.css');
        }
	}

	/**
	 * Add compressed css en js to page for module.
	 *
	 * @param string $module
	 */
	public function addCompressedResources($module) {
		if (!LoginModel::mag('P_LOGGED_IN')) {
			return;
		}
		$sheet = sprintf('/dist/css/module/%s.css', $module);
		parent::addStylesheet($sheet, false);
	}

	/**
	 * Geeft een array met gevraagde modules, afhankelijk van lidinstellingen
	 * De modules zijn terug te vinden in /resources/assets/sass
	 *
	 * @return array
	 */
	private function getUserModules() {
		$modules = array();

		if (!LoginModel::mag('P_LOGGED_IN')) {
			return [];
		}

		// de algemene module gevraagd, ook worden modules gekoppeld aan instellingen opgezocht
		$modules[] = 'general';
		$modules[] = 'module/formulier';
		$modules[] = 'module/datatable';

		//voeg modules toe afhankelijk van instelling
		$modules[] = 'opmaak/' . LidInstellingenModel::get('layout', 'opmaak');

		if (LidInstellingenModel::get('layout', 'toegankelijk') == 'bredere letters') {
			$modules[] = 'bredeletters';
		}
		if (LidInstellingenModel::get('layout', 'fx') == 'sneeuw') {
			$modules[] = 'effect/snow';
		} elseif (LidInstellingenModel::get('layout', 'fx') == 'space') {
			$modules[] = 'effect/space';
		} elseif (LidInstellingenModel::get('layout', 'fx') == 'civisaldo') {
			$modules[] = 'effect/civisaldo';
		}

		if (LidInstellingenModel::get('layout', 'minion') == 'ja') {
			$modules[] = 'effect/minion';
		}
		if (LidInstellingenModel::get('layout', 'fx') == 'onontdekt') {
			$modules[] = 'effect/onontdekt';
		}

		return $modules;
	}
}
