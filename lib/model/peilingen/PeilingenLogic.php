<?php

namespace CsrDelft\model\peilingen;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\peilingen\PeilingOptie;
use CsrDelft\model\entity\peilingen\PeilingStem;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\DependencyManager;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\view\bbcode\CsrBB;

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

	public function getOptiesVoorPeiling($peilingId) {
		$peiling = $this->peilingenModel->getPeilingById($peilingId);
		if ($peiling) {
			return $this->peilingOptiesModel->getByPeilingId($peilingId);
		}
		return [];
	}

	public function magOptieToevoegen($peilingId) {
		$peiling = $this->peilingenModel->getPeilingById($peilingId);
		if ($this->peilingenModel->magBewerken($peiling)) {
			return true;
		}

		if ($this->peilingStemmenModel->heeftGestemd($peilingId, LoginModel::getUid())) {
			return false;
		}

		if (!$this->peilingenModel->magStemmen($peiling)) {
			return false;
		}

		$aantalVoorgesteld = $this->peilingOptiesModel->count('peiling_id = ? AND ingebracht_door = ?', [$peilingId, LoginModel::getUid()]);
		return $aantalVoorgesteld < $peiling->aantal_voorstellen;
	}

	public function stem($peilingId, $opties, $uid) {
		$peilingOptiesModel = $this->peilingOptiesModel;
		$peilingStemmenModel = $this->peilingStemmenModel;
		return Database::transaction(function () use ($peilingId, $opties, $uid, $peilingOptiesModel, $peilingStemmenModel) {
			if ($this->isGeldigeStem($peilingId, $opties, $uid)) {
				$opties = $this->valideerOpties($peilingId, $opties);

				foreach ($opties as $optieId) {
					$optie = $peilingOptiesModel->getById($optieId);
					$optie->stemmen += 1;

					$peilingOptiesModel->update($optie);
				}

				$stem = new PeilingStem();
				$stem->peiling_id = $peilingId;
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
	 * @param int $peilingId
	 * @param int[] $opties
	 * @return int[]
	 */
	public function valideerOpties($peilingId, $opties) {
		$mogelijkeOpties = $this->peilingOptiesModel->find('peiling_id = ?', [$peilingId])->fetchAll();
		$mogelijkeOptieIds = array_map(function ($optie) {
			return $optie->id;
		}, $mogelijkeOpties);
		return array_intersect($mogelijkeOptieIds, $opties);
	}

	/**
	 * @param $peilingId
	 * @param $opties
	 * @param $uid
	 * @return bool
	 * @throws CsrGebruikerException
	 */
	public function isGeldigeStem($peilingId, $opties, $uid) {
		$peiling = $this->peilingenModel->getPeilingById($peilingId);

		if (!$peiling) {
			throw new CsrGebruikerException('Deze peiling bestaat niet');
		}

		if (!$this->peilingenModel->magStemmen($peiling)) {
			throw new CsrGebruikerException('Mag niet op deze peiling stemmen.');
		}

		if ($this->peilingStemmenModel->heeftGestemd($peilingId, $uid)) {
			throw new CsrGebruikerException('Alreeds gestemd.');
		}

		if (count($opties) == 0) {
			throw new CsrGebruikerException('Selecteer tenminste een optie.');
		}


		$geldigeOptieIds = $this->valideerOpties($peilingId, $opties);

		if (count($geldigeOptieIds) > $peiling->aantal_stemmen) {
			throw new CsrGebruikerException(sprintf('Selecteer maximaal %d opties.', $peiling->aantal_stemmen));
		}

		// Er zijn opties in $opties die niet in $mogelijkeOpties zitten
		if (count($geldigeOptieIds) != count($opties)) {
			throw new CsrGebruikerException('Gestemd op optie die niet geldig is.');
		}

		return true;
	}

	public function getOptionsAsJson($peilingId, $uid) {
		$peiling = $this->peilingenModel->getPeilingById($peilingId);
		$opties = $this->peilingOptiesModel->getByPeilingId($peilingId);

		$magStemmenZien = $this->peilingStemmenModel->heeftgestemd($peilingId, $uid) && $peiling->resultaat_zichtbaar;

		return array_map(function (PeilingOptie $optie) use ($magStemmenZien, $peiling) {
			$arr = $optie->jsonSerialize();

			// Als iemand nog niet gestemd heeft is deze info niet zichtbaar.
			if (!$magStemmenZien && !$this->peilingenModel->magBewerken($peiling)) {
				$arr['stemmen'] = 0;
			}

			$arr['beschrijving'] = CsrBB::parse($arr['beschrijving']);

			$ingebrachtDoor = ProfielModel::get($optie->ingebracht_door);

			$arr['ingebracht_door'] = $ingebrachtDoor ? [
				'display' => $ingebrachtDoor->getLink('volledig'),
				'sort' => $ingebrachtDoor->achternaam,
				'export' => $ingebrachtDoor->getNaam('volledig'),
				'filter' => $ingebrachtDoor->getNaam('volledig'),
			] : null;

			return $arr;
		}, $opties);
	}
}
