<?php

namespace CsrDelft\model;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 25/09/2018
 */
class DefaultService implements Service {
	/**
	 * @var PersistenceModel
	 */
	private $model;

	public function __construct(PersistenceModel $model) {
		$this->model = $model;
	}

	function find($zoekFilter, $zoekVelden, $order, $limit, $start) {
		$criteria = join(" OR ", array_map(function ($column) {
			return "${column['data']} LIKE :searchTerm";
		}, $zoekVelden));

		return $this->model->find($criteria, [':searchTerm' => $zoekFilter], null, $order, $limit, $start);
	}
}
