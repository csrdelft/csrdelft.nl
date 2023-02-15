<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * MaaltijdRepetitiesRepository  |  P.W.G. Brussee (brussee@live.nl)
 *
 * @method MaaltijdRepetitie|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaaltijdRepetitie|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaaltijdRepetitie[]    findAll()
 * @method MaaltijdRepetitie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaaltijdRepetitiesRepository extends AbstractRepository
{
	protected $default_order = '(periode_in_dagen = 0) ASC, periode_in_dagen ASC, dag_vd_week ASC, standaard_titel ASC';

	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, MaaltijdRepetitie::class);
	}

	public function getAbboneerbareRepetities()
	{
		return $this->findBy(['abonneerbaar' => 'true']);
	}

	public function getAlleRepetities($groupById = false)
	{
		$repetities = $this->findAll();
		if ($groupById) {
			$result = [];
			foreach ($repetities as $repetitie) {
				$result[$repetitie->mlt_repetitie_id] = $repetitie;
			}
			return $result;
		}
		return $repetities;
	}

	/**
	 * @param $mrid
	 * @return MaaltijdRepetitie
	 * @throws CsrGebruikerException
	 */
	public function getRepetitie($mrid)
	{
		$repetitie = $this->find($mrid);
		if (!$repetitie) {
			throw new CsrGebruikerException(
				'Get maaltijd-repetitie faalt: Not found $mrid =' . $mrid
			);
		}
		return $repetitie;
	}
}
