<?php

namespace CsrDelft\repository\declaratie;

use CsrDelft\entity\declaratie\DeclaratieBon;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\AbstractRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeclaratieBon|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeclaratieBon|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeclaratieBon[]    findAll()
 * @method DeclaratieBon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeclaratieBonRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, DeclaratieBon::class);
	}

	/**
	 * @param string $filename
	 * @param Profiel $profiel
	 * @return DeclaratieBon
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function generate(string $filename, Profiel $profiel): DeclaratieBon
	{
		$bon = new DeclaratieBon();
		$bon->setBestand($filename);
		$bon->setMaker($profiel);

		$this->getEntityManager()->persist($bon);
		$this->getEntityManager()->flush();

		return $bon;
	}
}
