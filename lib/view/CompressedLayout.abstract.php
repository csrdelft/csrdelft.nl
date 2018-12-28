<?php

namespace CsrDelft\view;

use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\security\LoginModel;

/**
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
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

		foreach (static::getUserModules() as $module) {
		    parent::addStylesheet($module . '.css');
		}
	}

	/**
	 * Geeft een array met gevraagde modules, afhankelijk van lidinstellingen
	 * De modules zijn terug te vinden in /resources/assets/sass
	 *
	 * @return array
	 */
	public static function getUserModules() {
		$modules = [];

		if (!LoginModel::mag('P_LOGGED_IN')) {
			return [];
		}

		//voeg modules toe afhankelijk van instelling
		$modules[] = 'common';
		$modules[] = 'thema-' . LidInstellingenModel::get('layout', 'opmaak');

		// de algemene module gevraagd, ook worden modules gekoppeld aan instellingen opgezocht

		if (LidInstellingenModel::get('layout', 'toegankelijk') == 'bredere letters') {
			$modules[] = 'bredeletters';
		}
		if (LidInstellingenModel::get('layout', 'fx') == 'sneeuw') {
			$modules[] = 'effect-snow';
		} elseif (LidInstellingenModel::get('layout', 'fx') == 'space') {
			$modules[] = 'effect-space';
		} elseif (LidInstellingenModel::get('layout', 'fx') == 'civisaldo') {
			$modules[] = 'effect-civisaldo';
		}

		if (LidInstellingenModel::get('layout', 'minion') == 'ja') {
			$modules[] = 'effect-minion';
		}
		if (LidInstellingenModel::get('layout', 'fx') == 'onontdekt') {
			$modules[] = 'effect-onontdekt';
		}

		return $modules;
	}
}
