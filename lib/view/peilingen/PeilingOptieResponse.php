<?php

namespace CsrDelft\view\peilingen;

use CsrDelft\view\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 */
class PeilingOptieResponse extends DataTableResponse
{
	public function renderElement($element)
	{
		return $element;
	}
}
