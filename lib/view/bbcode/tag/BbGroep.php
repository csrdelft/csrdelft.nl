<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\groepen\RechtenGroepenModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbGroep extends BbTagGroep {

	public static function getTagName() {
		return 'groep';
	}

	public function getLidNaam() {
		return 'personen';
	}

	public function getModel() {
		return RechtenGroepenModel::class;
	}
}
