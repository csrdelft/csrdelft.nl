<?php

namespace CsrDelft\repository\bibliotheek;

use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\entity\bibliotheek\BoekRecensie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BoekRecensie|null find($id, $lockMode = null, $lockVersion = null)
 * @method BoekRecensie|null findOneBy(array $criteria, array $orderBy = null)
 * @method BoekRecensie[]    findAll()
 * @method BoekRecensie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoekRecensieRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, BoekRecensie::class);
	}

	public function get(Boek $boek, Profiel $profiel): BoekRecensie
	{
		$recensie = $this->findOneBy(['boek' => $boek, 'schrijver' => $profiel]);

		if (!$recensie) {
			$recensie = new BoekRecensie();
			$recensie->boek = $boek;
			$recensie->schrijver = $profiel;
			$recensie->toegevoegd = date_create_immutable();
		}

		return $recensie;
	}

	/**
	 * @param $uid
	 * @return BoekRecensie[]
	 */
	public function getVoorLid($uid): array
	{
		return $this->findBy(['schrijver_uid' => $uid]);
	}
}
