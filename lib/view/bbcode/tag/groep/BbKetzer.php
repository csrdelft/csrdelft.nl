<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\model\groepen\KetzersModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbKetzer extends BbTagGroep {
	public function __construct(KetzersModel $model) {
		parent::__construct($model);
	}

	public static function getTagName() {
		return 'ketzer';
	}

	public function getLidNaam() {
		return 'aanmeldingen';
	}
}
