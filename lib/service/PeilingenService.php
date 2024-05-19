<?php

namespace CsrDelft\service;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\entity\peilingen\PeilingStem;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\peilingen\PeilingenRepository;
use CsrDelft\repository\peilingen\PeilingOptiesRepository;
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
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(
		PeilingenRepository $peilingenRepository,
		PeilingOptiesRepository $peilingOptiesRepository,
		EntityManagerInterface $entityManager
	) {
		$this->peilingenRepository = $peilingenRepository;
		$this->peilingOptiesRepository = $peilingOptiesRepository;
		$this->entityManager = $entityManager;
	}

	public function magOptieToevoegen(Peiling $peiling): bool
	{
		if ($this->peilingenRepository->magBewerken($peiling)) {
			return true;
		}

		if ($peiling->getStem(LoginService::getProfiel())) {
			return false;
		}

		if (!$peiling->getMagStemmen()) {
			return false;
		}

		$aantalVoorgesteld = $this->peilingOptiesRepository->count([
			'peiling_id' => $peiling->id,
			'ingebracht_door' => LoginService::getUid(),
		]);
		return $aantalVoorgesteld < $peiling->aantal_voorstellen;
	}

	public function stem(Peiling $peiling, $opties, Profiel $profiel)
	{
		try {
			$this->entityManager->beginTransaction();

			if ($this->isGeldigeStem($peiling, $opties, $profiel)) {
				$opties = $this->valideerOpties($peiling, $opties);

				foreach ($opties as $optieId) {
					$optie = $this->peilingOptiesRepository->find($optieId);
					$optie->stemmen += 1;

					$this->entityManager->persist($optie);
				}

				$stem = new PeilingStem();
				$stem->peiling_id = $peiling->id;
				$stem->peiling = $peiling;
				$stem->profiel = $profiel;
				$stem->uid = $profiel->uid;
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
	public function valideerOpties(Peiling $peiling, $opties): array
	{
		$mogelijkeOptieIds = array_map(function ($optie) {
			return $optie->id;
		}, $peiling->opties->toArray());
		return array_intersect($mogelijkeOptieIds, $opties);
	}

	/**
	 * @param $peilingId
	 * @param $opties
	 * @param $uid
	 * @return bool
	 * @throws CsrGebruikerException
	 */
	public function isGeldigeStem(Peiling $peiling, $opties, Profiel $profiel): bool
	{
		if ($peiling->getStem($profiel)) {
			throw new CsrGebruikerException('Alreeds gestemd.');
		}

		if (count($opties) == 0) {
			throw new CsrGebruikerException('Selecteer tenminste een optie.');
		}

		$geldigeOptieIds = $this->valideerOpties($peiling, $opties);

		if (count($geldigeOptieIds) > $peiling->aantal_stemmen) {
			throw new CsrGebruikerException(
				sprintf('Selecteer maximaal %d opties.', $peiling->aantal_stemmen)
			);
		}

		// Er zijn opties in $opties die niet in $mogelijkeOpties zitten
		if (count($geldigeOptieIds) != count($opties)) {
			throw new CsrGebruikerException('Gestemd op optie die niet geldig is.');
		}

		return true;
	}
}
