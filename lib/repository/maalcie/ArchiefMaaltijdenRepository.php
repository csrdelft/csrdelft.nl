<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\entity\maalcie\ArchiefMaaltijd;
use CsrDelft\repository\AbstractRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ArchiefMaaltijdenRepository
 * @package CsrDelft\repository\maalcie
 * @method ArchiefMaaltijd|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArchiefMaaltijd|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArchiefMaaltijd[]    findAll()
 * @method ArchiefMaaltijd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArchiefMaaltijdenRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ArchiefMaaltijd::class);
	}

	protected $default_order = 'datum DESC, tijd DESC';

	/**
	 * @param ArchiefMaaltijd $archiefMaaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function create(ArchiefMaaltijd $archiefMaaltijd): void
	{
		$this->_em->persist($archiefMaaltijd);
		$this->_em->flush();
	}
}
