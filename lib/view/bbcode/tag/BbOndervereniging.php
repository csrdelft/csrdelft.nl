<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\groepen\OnderverenigingenModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbOndervereniging extends BbTagGroep {

	public static function getTagName() {
		return 'ondervereniging';
	}

	public function getLidNaam() {
		return 'leden';
	}

	public function getModel() {
		return OnderverenigingenModel::class;
	}
}
