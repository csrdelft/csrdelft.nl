<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\model\groepen\OnderverenigingenModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbOndervereniging extends BbTagGroep {
	public function __construct(OnderverenigingenModel $model) {
		parent::__construct($model);
	}

	public static function getTagName() {
		return 'ondervereniging';
	}

	public function getLidNaam() {
		return 'leden';
	}
}
