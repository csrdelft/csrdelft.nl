<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\view\datatable\DataTableResponse;

class BibliotheekCatalogusDatatableResponse extends DataTableResponse
{
	/**
	 * @param Boek $entity
	 *
	 * @return (int|mixed|string)[]
	 *
	 * @psalm-return array{titel_link: string, recensie_count: int<0, max>,...}
	 */
	public function renderElement($entity)
	{
		$arr = (array) $entity;
		$arr['titel_link'] = "<a href='{$entity->getUrl()}'>$entity->titel</a>";
		$arr['recensie_count'] = sizeof($entity->getRecensies());
		return $arr;
	}
}
