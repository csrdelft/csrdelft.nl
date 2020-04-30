<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\repository\groepen\BesturenRepository;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbBestuur extends BbTagGroep {
	public function __construct(BesturenRepository $model) {
		parent::__construct($model);
	}

	public static function getTagName() {
		return 'bestuur';
	}

	public function getLidNaam() {
		return 'personen';
	}
}
