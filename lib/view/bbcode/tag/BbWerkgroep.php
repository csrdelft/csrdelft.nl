<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\groepen\WerkgroepenModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbWerkgroep extends BbTagGroep {

	public static function getTagName() {
		return 'werkgroep';
	}

	public function getLidNaam() {
		return 'aanmeldingen';
	}

	public function getModel() {
		return WerkgroepenModel::class;
	}
}
