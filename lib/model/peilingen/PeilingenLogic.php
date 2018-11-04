<?php

namespace CsrDelft\model\peilingen;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\peilingen\PeilingOptie;
use CsrDelft\model\entity\peilingen\PeilingStem;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\DependencyManager;
use CsrDelft\Orm\Persistence\Database;

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
		if (LoginModel::mag('P_PEILING_MOD')) {
			return true;
		}

		if ($this->peilingStemmenModel->heeftGestemd($peiling_id, $uid)) {
			return false;
		}

		$peiling = $this->peilingenModel->getPeilingById($peiling_id);
		$aantalVoorgesteld = $this->peilingOptiesModel->count('peiling_id = ? AND ingebracht_door = ?', [$peiling_id, $uid]);
		return $aantalVoorgesteld < $peiling->aantal_voorstellen;
	}

	public function stem($peiling_id, $opties, $uid) {
		$peilingOptiesModel  = $this->peilingOptiesModel;
		$peilingStemmenModel = $this->peilingStemmenModel;
		return Database::transaction(function () use ($peiling_id, $opties, $uid, $peilingOptiesModel, $peilingStemmenModel) {
			if ($this->isGeldigeStem($peiling_id, $opties, $uid)) {
				$opties = $this->valideerOpties($peiling_id, $opties);

				foreach ($opties as $optie_id) {
					$optie = $peilingOptiesModel->getById($optie_id);
					$optie->stemmen += 1;

					$peilingOptiesModel->update($optie);
				}

				$stem = new PeilingStem();
				$stem->peiling_id = $peiling_id;
				$stem->uid = $uid;
				$stem->aantal = count($opties);

				$peilingStemmenModel->create($stem);

				return true;
			}

			return false;
		});
	}

	/**
	 * Geef alle geldige opties voor een peiling. Gegeven een set met opties.
	 *
	 * @param int $peiling_id
	 * @param int[] $opties
	 * @return int[]
	 */
	public function valideerOpties($peiling_id, $opties) {
		$mogelijkeOpties = $this->peilingOptiesModel->find('peiling_id = ?', [$peiling_id])->fetchAll();
		$mogelijkeOptieIds = array_map(function ($optie) {
			return $optie->id;
		}, $mogelijkeOpties);
		return array_intersect($mogelijkeOptieIds, $opties);
	}

	/**
	 * @param $peiling_id
	 * @param $opties
	 * @param $uid
	 * @return bool
	 * @throws CsrGebruikerException
	 */
	public function isGeldigeStem($peiling_id, $opties, $uid) {
		if ($this->peilingStemmenModel->heeftGestemd($peiling_id, $uid)) {
			throw new CsrGebruikerException('Alreeds gestemd.');
		}

		if (count($opties) == 0) {
			throw new CsrGebruikerException('Selecteer tenminste een optie.');
		}

		$peiling = $this->peilingenModel->getPeilingById($peiling_id);

		$geldigeOptieIds = $this->valideerOpties($peiling_id, $opties);

		if (count($geldigeOptieIds) > $peiling->aantal_stemmen) {
			throw new CsrGebruikerException(sprintf('Selecteer maximaal %d opties.', $peiling->aantal_stemmen));
		}

		// Er zijn opties in $opties die niet in $mogelijkeOpties zitten
		if (count($geldigeOptieIds) != count($opties)) {
			throw new CsrGebruikerException('Gestemd op optie die niet geldig is.');
		}

		return true;
	}

	public function getOptionsAsJson($peiling_id, $uid) {
		$opties = $this->peilingOptiesModel->getByPeilingId($peiling_id);

		$heeftGestemd = $this->peilingStemmenModel->heeftgestemd($peiling_id, $uid);

		return array_map(function (PeilingOptie $optie) use ($heeftGestemd) {
			$arr = $optie->jsonSerialize();

			// Als iemand nog niet gestemd heeft is deze info niet zichtbaar.
			if (!$heeftGestemd && !LoginModel::mag('P_PEILING_MOD')) {
				$arr['stemmen']	= 0;
			}

			return $arr;
		}, $opties);
	}
}
