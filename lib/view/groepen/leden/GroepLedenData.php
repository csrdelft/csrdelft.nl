<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\datatable\DataTableResponse;

class GroepLedenData extends DataTableResponse
{
	/**
	 * @return (mixed|null|string)[]
	 *
	 * @psalm-return array{lid: null|string, door_uid: null|string,...}
	 */
	public function renderElement($lid)
	{
		$array = (array) $lid;

		$array['lid'] = ProfielRepository::getLink($array['uid'], 'civitas');
		$array['door_uid'] = ProfielRepository::getLink(
			$array['door_uid'],
			'civitas'
		);

		return $array;
	}
}
