<?php

namespace CsrDelft\view;

use CsrDelft\service\security\LoginService;

/**
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
abstract class CompressedLayout {

	/**
	 * Geeft een array met gevraagde modules, afhankelijk van lidinstellingen
	 * De modules zijn terug te vinden in /resources/assets/sass
	 *
	 * @return array
	 */
	public static function getUserModules() {
		$modules = [];

		if (!LoginService::mag(P_LOGGED_IN)) {
			return [];
		}

		//voeg modules toe afhankelijk van instelling
		$modules[] = 'thema-' . lid_instelling('layout', 'opmaak');

		// de algemene module gevraagd, ook worden modules gekoppeld aan instellingen opgezocht

		if (lid_instelling('layout', 'toegankelijk') == 'bredere letters') {
			$modules[] = 'bredeletters';
		}
		if (lid_instelling('layout', 'fx') == 'civisaldo') {
			$modules[] = 'effect-civisaldo';
		}

		return $modules;
	}
}
