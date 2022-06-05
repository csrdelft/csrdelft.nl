<?php

namespace CsrDelft\view\datatable\knoppen;

use CsrDelft\view\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/03/2018
 */
class UrlDataTableKnop extends DataTableKnop
{
	public function __construct(
		Multiplicity $multiplicity,
		$url,
		$label,
		$title,
		string $icon = ''
	) {
		parent::__construct($multiplicity, $url, $label, $title, $icon, 'url');
	}
}
