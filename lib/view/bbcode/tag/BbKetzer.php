<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\groepen\KetzersModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbKetzer extends BbTagGroep {

	public function getTagName() {
		return 'ketzer';
	}

	public function getModel() {
		return KetzersModel::class;
	}

	public function getLidNaam() {
		return 'aanmeldingen';
	}
}
