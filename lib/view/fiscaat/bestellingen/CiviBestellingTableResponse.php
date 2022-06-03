<?php

namespace CsrDelft\view\fiscaat\bestellingen;

use CsrDelft\view\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2017
 */
class CiviBestellingTableResponse extends DataTableResponse
{
	public function renderElement($element)
	{
		return $element;
	}
}
