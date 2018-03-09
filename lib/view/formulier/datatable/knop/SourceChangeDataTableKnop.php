<?php

namespace CsrDelft\view\formulier\datatable\knop;

use CsrDelft\view\formulier\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/03/2018
 */
class SourceChangeDataTableKnop extends DataTableKnop {
	public function __construct($url, $label, $title, string $icon = '') {
		parent::__construct(Multiplicity::None(), $url, $label, $title, $icon, 'sourceChange');
	}
}
