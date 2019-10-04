<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\groepen\BesturenModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbBestuur extends BbTagGroep {

	public static function getTagName() {
		return 'bestuur';
	}

	public function getLidNaam() {
		return 'personen';
	}

	public function getModel() {
		return BesturenModel::class;
	}
}
