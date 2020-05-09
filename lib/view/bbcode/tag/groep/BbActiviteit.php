<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\repository\groepen\ActiviteitenRepository;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbActiviteit extends BbTagGroep {
	public function __construct(ActiviteitenRepository $model) {
		parent::__construct($model);
	}

	public static function getTagName() {
		return 'activiteit';
	}

	public function getLidNaam() {
		return 'aanmeldingen';
	}
}
