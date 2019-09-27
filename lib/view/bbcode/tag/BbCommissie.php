<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\groepen\CommissiesModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbCommissie extends BbTagGroep {

	public static function getTagName() {
		return 'commissie';
	}

	public function getLidNaam() {
		return 'leden';
	}

	public function getModel() {
		return CommissiesModel::class;
	}
}
