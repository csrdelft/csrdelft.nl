<?php

namespace CsrDelft\view\datatable;

use CsrDelft\view\JsonLijstResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */
abstract class DataTableResponse extends JsonLijstResponse
{
	public $autoUpdate = false;
	public $modal = null;

	/**
	 * @return (int|mixed)[]
	 *
	 * @psalm-return array{modal: mixed, autoUpdate: mixed, lastUpdate: int<0, max>, data: mixed}
	 */
	public function getModel()
	{
		return [
			'modal' => $this->modal,
			'autoUpdate' => $this->autoUpdate,
			'lastUpdate' => time() - 1,
			'data' => parent::getModel(),
		];
	}
}
