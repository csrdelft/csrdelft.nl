<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\groepen\ActiviteitenModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbActiviteit extends BbTagGroep {

	public static function getTagName() {
		return 'activiteit';
	}

	public function getModel() {
		return ActiviteitenModel::class;
	}

	public function getLidNaam() {
		return 'aanmeldingen';
	}
}
