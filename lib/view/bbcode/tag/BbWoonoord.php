<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\groepen\WoonoordenModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbWoonoord extends BbTagGroep {

	public static function getTagName() {
		return 'woonoord';
	}

	public function getLidNaam() {
		return 'bewoners';
	}

	public function getModel() {
		return WoonoordenModel::class;
	}
}
