<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\datatable\DataTableResponse;

class GroepLedenData extends DataTableResponse
{

	public function renderElement($lid)
	{
		$array = (array)$lid;

		$array['lid'] = ProfielRepository::getLink($array['uid'], 'civitas');
		$array['door_uid'] = ProfielRepository::getLink($array['door_uid'], 'civitas');

		return $array;
	}

}
