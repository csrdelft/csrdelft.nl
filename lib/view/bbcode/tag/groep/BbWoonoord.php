<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\repository\groepen\WoonoordenRepository;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbWoonoord extends BbTagGroep {
	public function __construct(WoonoordenRepository $model) {
		parent::__construct($model);
	}

	public static function getTagName() {
		return 'woonoord';
	}

	public function getLidNaam() {
		return 'bewoners';
	}
}
