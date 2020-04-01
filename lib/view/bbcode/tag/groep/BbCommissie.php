<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\model\groepen\CommissiesModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbCommissie extends BbTagGroep {
	public function __construct(CommissiesModel $model) {
		parent::__construct($model);
	}

	public static function getTagName() {
		return 'commissie';
	}

	public function getLidNaam() {
		return 'leden';
	}
}
