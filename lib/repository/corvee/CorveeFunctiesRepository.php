<?php

namespace CsrDelft\repository\corvee;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\corvee\CorveeFunctie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method CorveeFunctie|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorveeFunctie|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorveeFunctie[]    findAll()
 * @method CorveeFunctie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorveeFunctiesRepository extends AbstractRepository
{
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var CorveeRepetitiesRepository
	 */
	private $corveeRepetitiesRepository;

	public function __construct(
		ManagerRegistry $registry,
		CorveeTakenRepository $corveeTakenRepository,
		CorveeRepetitiesRepository $corveeRepetitiesRepository
	) {
		parent::__construct($registry, CorveeFunctie::class);
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->corveeRepetitiesRepository = $corveeRepetitiesRepository;
	}

	/**
	 * Lazy loading of kwalificaties.
	 *
	 * @param int $fid
	 * @return CorveeFunctie|null
	 */
	public function get($fid)
	{
		return $this->find($fid);
	}

	/**
	 * Optional eager loading of kwalificaties.
	 *
	 * @return CorveeFunctie[]
	 */
	public function getAlleFuncties()
	{
		return group_by_distinct('functie_id', $this->findAll());
	}

	public function nieuw()
	{
		$functie = new CorveeFunctie();
		$functie->kwalificatie_benodigd = (bool) instelling(
			'corvee',
			'standaard_kwalificatie'
		);
		return $functie;
	}

	public function removeFunctie(CorveeFunctie $functie)
	{
		if ($this->corveeTakenRepository->existFunctieTaken($functie->functie_id)) {
			throw new CsrGebruikerException(
				'Verwijder eerst de bijbehorende corveetaken!'
			);
		}
		if (
			$this->corveeRepetitiesRepository->existFunctieRepetities(
				$functie->functie_id
			)
		) {
			throw new CsrGebruikerException(
				'Verwijder eerst de bijbehorende corveerepetities!'
			);
		}
		if ($functie->hasKwalificaties()) {
			throw new CsrGebruikerException(
				'Verwijder eerst de bijbehorende kwalificaties!'
			);
		}
		$this->_em->remove($functie);
		$this->_em->flush();
	}

	public function getSuggesties($query)
	{
		return $this->createQueryBuilder('f')
			->where('f.naam LIKE :query')
			->setParameter('query', sql_contains($query))
			->getQuery()
			->getResult();
	}
}
