<?php

namespace CsrDelft\view\datatable\knoppen;

use CsrDelft\view\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/03/2018
 */
class ConfirmDataTableKnop extends DataTableKnop
{
	public function __construct(
		Multiplicity $multiplicity,
		string $url,
		string $label,
		string $title,
		string $icon = ''
	) {
		parent::__construct($multiplicity, $url, $label, $title, $icon, 'confirm');
	}

	public function jsonSerialize(): array
	{
		return array_merge(parent::jsonSerialize(), ['buttons' => []]);
	}
}
