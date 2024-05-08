<?php

namespace CsrDelft\service\corvee;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\repository\corvee\CorveeRepetitiesRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Operaties die te doen hebben met corvee repetities
 */
class CorveeRepetitiesService
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var CorveeVoorkeurenRepository
	 */
	private $corveeVoorkeurenRepository;
	/**
	 * @var CorveeRepetitiesRepository
	 */
	private $corveeRepetitiesRepository;

	public function __construct(
		EntityManagerInterface $entityManager,
		CorveeTakenRepository $corveeTakenRepository,
		CorveeRepetitiesRepository $corveeRepetitiesRepository,
		CorveeVoorkeurenRepository $corveeVoorkeurenRepository
	) {
		$this->entityManager = $entityManager;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->corveeVoorkeurenRepository = $corveeVoorkeurenRepository;
		$this->corveeRepetitiesRepository = $corveeRepetitiesRepository;
	}

	public function verwijderRepetitie($crid)
	{
		if (!is_numeric($crid) || $crid <= 0) {
			throw new CsrGebruikerException(
				'Verwijder corvee-repetitie faalt: Invalid $crid =' . $crid
			);
		}
		if ($this->corveeTakenRepository->existRepetitieTaken($crid)) {
			// delete corveetaken first (foreign key)
			$this->corveeTakenRepository->verwijderRepetitieTaken($crid);
			throw new CsrGebruikerException(
				'Alle bijbehorende corveetaken zijn naar de prullenbak verplaatst. Verwijder die eerst!'
			);
		}

		return $this->entityManager->wrapInTransaction(function () use ($crid) {
			// delete voorkeuren first (foreign key)
			$aantal = $this->corveeVoorkeurenRepository->verwijderVoorkeuren($crid);
			$repetitie = $this->corveeRepetitiesRepository->find($crid);
			$this->entityManager->remove($repetitie);
			$this->entityManager->flush();
			return $aantal;
		});
	}
}
