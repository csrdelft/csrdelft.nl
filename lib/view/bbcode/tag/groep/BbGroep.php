<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\model\groepen\RechtenGroepenModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbGroep extends BbTagGroep {
	public function __construct(RechtenGroepenModel $model) {
		parent::__construct($model);
	}

	public static function getTagName() {
		return 'groep';
	}

	public function getLidNaam() {
		return 'personen';
	}
}
