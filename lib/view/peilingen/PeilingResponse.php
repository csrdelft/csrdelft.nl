<?php

namespace CsrDelft\view\peilingen;

use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\formulier\datatable\DataTableColumn;
use CsrDelft\view\formulier\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 */
class PeilingResponse extends DataTableResponse
{

	/**
	 * @param Peiling $entity
	 * @return string
	 */
	public function getJson($entity)
	{
		$arr = $entity->jsonSerialize();

		$arr['detailSource'] = '/peilingen/opties/' . $entity->id;

		$eigenaar = ProfielModel::get($entity->eigenaar);
		$arr['eigenaar'] = $eigenaar ? new DataTableColumn(
			$eigenaar->getLink('volledig'),
			$eigenaar->achternaam,
			$eigenaar->getNaam('volledig')
		) : '';

		return parent::getJson($arr);
	}
}
