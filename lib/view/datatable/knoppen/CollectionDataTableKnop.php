<?php

namespace CsrDelft\view\datatable\knoppen;

use CsrDelft\view\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/03/2018
 */
class CollectionDataTableKnop extends DataTableKnop
{
	public function __construct(Multiplicity $multiplicity, string $label, string $title, string $icon = '')
	{
		parent::__construct($multiplicity, '', $label, $title, $icon, 'collection');
	}

	public function jsonSerialize()
	{
		return array_merge(parent::jsonSerialize(), ['buttons' => $this->buttons]);
	}

	public function addKnop(DataTableKnop $knop)
	{
		if ($this->tableId) {
			$knop->setDataTableId($this->tableId);
		}
		$this->buttons[] = $knop;
	}
}
