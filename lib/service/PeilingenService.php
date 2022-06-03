<?php

namespace CsrDelft\service;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\entity\peilingen\PeilingStem;
use CsrDelft\repository\peilingen\PeilingenRepository;
use CsrDelft\repository\peilingen\PeilingOptiesRepository;
use CsrDelft\repository\peilingen\PeilingStemmenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 02/11/2018
 */
class PeilingenService
{
	/**
	 * @var PeilingenRepository
	 */
	private $peilingenRepository;
	/**
	 * @var PeilingOptiesRepository
	 */
	private $peilingOptiesRepository;
	/**
	 * @var PeilingStemmenRepository
	 */
	private $peilingStemmenRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(
		PeilingenRepository      $peilingenRepository,
		PeilingOptiesRepository  $peilingOptiesRepository,
		PeilingStemmenRepository $peilingStemmenRepository,
		EntityManagerInterface   $entityManager
	)
	{
		$this->peilingenRepository = $peilingenRepository;
		$this->peilingOptiesRepository = $peilingOptiesRepository;
		$this->peilingStemmenRepository = $peilingStemmenRepository;
		$this->entityManager = $entityManager;
	}

	public function magOptieToevoegen(Peiling $peiling)
	{
		if ($this->peilingenRepository->magBewerken($peiling)) {
			return true;
		}

		if ($this->peilingStemmenRepository->heeftGestemd($peiling->id, LoginService::getUid())) {
			return false;
		}

		if (!$peiling->getMagStemmen()) {
			return false;
		}

		$aantalVoorgesteld = $this->peilingOptiesRepository->count(['peiling_id' => $peiling->id, 'ingebracht_door' => LoginService::getUid()]);
		return $aantalVoorgesteld < $peiling->aantal_voorstellen;
	}

	public function stem($peilingId, $opties, $uid)
	{
		try {
			$this->entityManager->beginTransaction();

			if ($this->isGeldigeStem($peilingId, $opties, $uid)) {
				$opties = $this->valideerOpties($peilingId, $opties);

				foreach ($opties as $optieId) {
					$optie = $this->peilingOptiesRepository->find($optieId);
					$optie->stemmen += 1;

					$this->entityManager->persist($optie);
				}

				$stem = new PeilingStem();
				$stem->peiling_id = $peilingId;
				$stem->peiling = $this->entityManager->getReference(Peiling::class, $peilingId);
				$stem->profiel = ProfielRepository::get($uid);
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
	public function valideerOpties($peilingId, $opties)
	{
		$mogelijkeOpties = $this->peilingOptiesRepository->findBy(['peiling_id' => $peilingId]);
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
	public function isGeldigeStem($peilingId, $opties, $uid)
	{
		$peiling = $this->peilingenRepository->getPeilingById($peilingId);

		if (!$peiling) {
			throw new CsrGebruikerException('Deze peiling bestaat niet');
		}

		if (!$peiling->getMagStemmen()) {
			throw new CsrGebruikerException('Mag niet op deze peiling stemmen.');
		}

		if ($this->peilingStemmenRepository->heeftGestemd($peilingId, $uid)) {
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
}
