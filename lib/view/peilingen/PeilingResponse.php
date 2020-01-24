<?php

namespace CsrDelft\view\peilingen;

use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\datatable\DataTableColumn;
use CsrDelft\view\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 */
class PeilingResponse extends DataTableResponse
{

	/**
	 * @param Peiling $entity
	 */
	public function renderElement($entity)
	{
		$arr = $entity->jsonSerialize();

		$arr['detailSource'] = '/peilingen/opties/' . $entity->id;

		$eigenaar = ProfielRepository::get($entity->eigenaar);
		$arr['eigenaar'] = $eigenaar ? new DataTableColumn(
			$eigenaar->getLink('volledig'),
			$eigenaar->achternaam,
			$eigenaar->getNaam('volledig')
		) : '';

		return $arr;
	}
}
