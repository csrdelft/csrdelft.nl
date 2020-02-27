<?php

namespace CsrDelft\model\peilingen;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\entity\peilingen\PeilingOptie;
use CsrDelft\entity\peilingen\PeilingStem;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\peilingen\PeilingenRepository;
use CsrDelft\repository\peilingen\PeilingOptiesRepository;
use CsrDelft\repository\peilingen\PeilingStemmenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\datatable\DataTableColumn;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 02/11/2018
 */
class PeilingenLogic {
	/**
	 * @var PeilingenRepository
	 */
	private $peilingenModel;
	/**
	 * @var PeilingOptiesRepository
	 */
	private $peilingOptiesModel;
	/**
	 * @var PeilingStemmenRepository
	 */
	private $peilingStemmenModel;
	/**
	 * @var SerializerInterface
	 */
	private $serializer;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(
		PeilingenRepository $peilingenModel,
		PeilingOptiesRepository $peilingOptiesModel,
		PeilingStemmenRepository $peilingStemmenModel,
	SerializerInterface $serializer,
	EntityManagerInterface $entityManager
	) {
		$this->peilingenModel = $peilingenModel;
		$this->peilingOptiesModel = $peilingOptiesModel;
		$this->peilingStemmenModel = $peilingStemmenModel;
		$this->serializer = $serializer;
		$this->entityManager = $entityManager;
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

		if (!$peiling->getMagStemmen()) {
			return false;
		}

		$aantalVoorgesteld = $this->peilingOptiesModel->count(['peiling_id' => $peilingId, 'ingebracht_door' => LoginModel::getUid()]);
		return $aantalVoorgesteld < $peiling->aantal_voorstellen;
	}

	public function stem($peilingId, $opties, $uid) {
		try {
			$this->entityManager->beginTransaction();

			if ($this->isGeldigeStem($peilingId, $opties, $uid)) {
				$opties = $this->valideerOpties($peilingId, $opties);

				foreach ($opties as $optieId) {
					$optie = $this->peilingOptiesModel->find($optieId);
					$optie->stemmen += 1;

					$this->entityManager->persist($optie);
				}

				$stem = new PeilingStem();
				$stem->peiling_id = $peilingId;
				$stem->peiling = $this->entityManager->getReference(Peiling::class, $peilingId);
				$stem->uid = $uid;
				$stem->aantal = count($opties);
				$this->entityManager->persist($stem);
				$this->entityManager->flush();
				$result = true;
			} else {
				$result = false;
			}
			$this->entityManager->commit();
			return $result;
		} catch (ORMException $ex) {
			$this->entityManager->rollback();
			throw new CsrException($ex->getMessage());
		}
	}

	/**
	 * Geef alle geldige opties voor een peiling. Gegeven een set met opties.
	 *
	 * @param int $peilingId
	 * @param int[] $opties
	 * @return int[]
	 */
	public function valideerOpties($peilingId, $opties) {
		$mogelijkeOpties = $this->peilingOptiesModel->findBy(['peiling_id' => $peilingId]);
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

		if (!$peiling->getMagStemmen()) {
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

		$magStemmenZien = ($this->peilingStemmenModel->heeftgestemd($peilingId, $uid) || !$peiling->getMagStemmen()) && $peiling->resultaat_zichtbaar;

		return $this->serializer->serialize($opties, 'json', ['groups' => 'vue']);

		return array_map(function (PeilingOptie $optie) use ($magStemmenZien, $peiling) {
			$arr = (array)$optie;

			// Als iemand nog niet gestemd heeft is deze info niet zichtbaar.
			if (!$magStemmenZien && !$this->peilingenModel->magBewerken($peiling)) {
				$arr['stemmen'] = 0;
			}

			$arr['beschrijving'] = CsrBB::parse($arr['beschrijving']);

			$ingebrachtDoor = ProfielRepository::get($optie->ingebracht_door);

			$arr['ingebracht_door'] = $ingebrachtDoor
				? new DataTableColumn($ingebrachtDoor->getLink('volledig'), $ingebrachtDoor->achternaam, $ingebrachtDoor->getNaam('volledig'))
				: null;

			return $arr;
		}, $opties);
	}
}
