<?php

namespace CsrDelft\view\peilingen;

use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\model\ProfielModel;
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
		$arr['eigenaar'] = ProfielModel::getLink($arr['eigenaar']);

		return parent::getJson($arr);
	}
}
