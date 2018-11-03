<?php

namespace CsrDelft\model\peilingen;

use CsrDelft\Orm\DependencyManager;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 02/11/2018
 */
class PeilingenLogic extends DependencyManager {
	/**
	 * @var PeilingenModel
	 */
	private $peilingenModel;
	/**
	 * @var PeilingOptiesModel
	 */
	private $peilingOptiesModel;
	/**
	 * @var PeilingStemmenModel
	 */
	private $peilingStemmenModel;

	public function __construct(
		PeilingenModel $peilingenModel,
		PeilingOptiesModel $peilingOptiesModel,
		PeilingStemmenModel $peilingStemmenModel
	) {
		$this->peilingenModel = $peilingenModel;
		$this->peilingOptiesModel = $peilingOptiesModel;
		$this->peilingStemmenModel = $peilingStemmenModel;
	}

	public function magOptieToevoegen($peiling_id, $uid) {
		if ($this->peilingStemmenModel->heeftGestemd($peiling_id, $uid)) {
			return false;
		}

		$peiling = $this->peilingenModel->getPeilingById($peiling_id);
		$aantalVoorgesteld = $this->peilingOptiesModel->count('peiling_id = ? AND ingebracht_door = ?', [$peiling_id, $uid]);
		return $aantalVoorgesteld < $peiling->aantal_voorstellen;
	}
}
