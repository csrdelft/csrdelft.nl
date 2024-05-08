<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\SqlUtil;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\AbstractRepository;
use DateTimeInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * MaaltijdenRepository  |  P.W.G. Brussee (brussee@live.nl)
 *
 * @method Maaltijd|null find($id, $lockMode = null, $lockVersion = null)
 * @method Maaltijd|null findOneBy(array $criteria, array $orderBy = null)
 * @method Maaltijd[]    findAll()
 * @method Maaltijd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Maaltijd[]    findByVerwijderd($verwijderd, array $orderBy = null, $limit = null, $offset = null)
 */
class MaaltijdenRepository extends AbstractRepository
{
	protected $default_order = 'datum ASC, tijd ASC';

	/**
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Maaltijd::class);
	}

	public function vanRepetitie(MaaltijdRepetitie $repetitie, DateTimeInterface $datum): Maaltijd {
		$maaltijd = new Maaltijd();
		$maaltijd->repetitie = $repetitie;
		$maaltijd->product = $repetitie->product;
		$maaltijd->titel = $repetitie->standaard_titel;
		$maaltijd->aanmeld_limiet = $repetitie->standaard_limiet;
		$maaltijd->datum = $datum;
		$maaltijd->tijd = $repetitie->standaard_tijd;
		$maaltijd->aanmeld_filter = $repetitie->abonnement_filter;
		$maaltijd->omschrijving = null;
		$maaltijd->verwerkt = false;

		return $maaltijd;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return Maaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function openMaaltijd(Maaltijd $maaltijd): Maaltijd
	{
		if (!$maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is al geopend');
		}
		$maaltijd->gesloten = false;
		$this->_em->persist($maaltijd);
		$this->_em->flush();
		return $maaltijd;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function sluitMaaltijd(Maaltijd $maaltijd): void
	{
		if ($maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is al gesloten');
		}
		$maaltijd->gesloten = true;
		$maaltijd->laatst_gesloten = date_create_immutable();
		$this->_em->persist($maaltijd);
		$this->_em->flush();
	}

	/**
	 * @return Maaltijd[]
	 */
	public function getMaaltijdenToekomst(): mixed
	{
		return $this->createQueryBuilder('m')
			->where('m.verwijderd = false and m.datum > :datum')
			->setParameter(':datum', date_create('-1 week'))
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()
			->getResult();
	}

	/**
	 * @return Maaltijd[]
	 */
	public function getMaaltijdenHistorie(): mixed
	{
		return $this->createQueryBuilder('m')
			->where('m.verwijderd = false and m.datum <= NOW()')
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()
			->getResult();
	}

	/**
	 * @return Maaltijd[]
	 */
	public function getMaaltijden(): array
	{
		return $this->findBy(['verwijderd' => false]);
	}

	/**
	 * Haalt de maaltijden in het verleden op voor de ingestelde periode.
	 *
	 * @param DateTimeInterface $timestamp
	 * @param null $limit
	 * @return Maaltijd[]
	 */
	public function getRecenteMaaltijden(DateTimeInterface $timestamp, $limit = null): array {
		/** @var Maaltijd[] $maaltijden */
		$maaltijden = $this->createQueryBuilder('m')
			->where(
				'm.verwijderd = false and m.datum >= :van_datum and m.datum <= :tot_datum'
			)
			->setParameter('van_datum', $timestamp)
			->setParameter('tot_datum', date_create_immutable())
			->setMaxResults($limit)
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()
			->getResult();
		$maaltijdenById = [];
		foreach ($maaltijden as $maaltijd) {
			// Sla over als maaltijd nog niet voorbij is
			if ($maaltijd->getEindMoment() > date_create_immutable()) {
				continue;
			}
			$maaltijdenById[$maaltijd->maaltijd_id] = $maaltijd;
		}
		return $maaltijdenById;
	}

	public function getVerwijderdeMaaltijden(): array
	{
		return $this->findBy(['verwijderd' => 'true']);
	}

	/**
	 * @param $mid
	 * @param bool $verwijderd
	 *
	 * @return Maaltijd
	 * @throws CsrGebruikerException
	 */
	public function getMaaltijd($mid, $verwijderd = false): Maaltijd
	{
		$maaltijd = $this->find($mid);
		if (!$maaltijd) {
			throw new CsrGebruikerException('Maaltijd bestaat niet: ' . $mid);
		}
		if (!$verwijderd && $maaltijd->verwijderd) {
			throw new CsrGebruikerException('Maaltijd is verwijderd');
		}
		return $maaltijd;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return Maaltijd|null
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function herstelMaaltijd(Maaltijd $maaltijd): Maaltijd
	{
		if (!$maaltijd->verwijderd) {
			throw new CsrGebruikerException('Maaltijd is niet verwijderd');
		}
		$maaltijd->verwijderd = false;
		$this->_em->persist($maaltijd);
		$this->_em->flush();
		return $maaltijd;
	}

	/**
	 * @param $van
	 * @param $tot
	 * @return Maaltijd[]
	 */
	public function getMaaltijdenTussen($van, $tot): mixed
	{
		return $this->createQueryBuilder('m')
			->where(
				'm.verwijderd = false and m.datum >= :van_datum and m.datum <= :tot_datum'
			)
			->setParameter('van_datum', $van)
			->setParameter('tot_datum', $tot)
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()
			->getResult();
	}

	// Repetitie-Maaltijden ############################################################

	/**
	 * @param $mrid
	 * @return Maaltijd[]
	 */
	public function getKomendeRepetitieMaaltijden($mrid): mixed
	{
		return $this->createQueryBuilder('m')
			->where(
				'm.mlt_repetitie_id = :maaltijd_id and verwijderd = false and datum >= :datum'
			)
			->setParameter('maaltijd_id', $mrid)
			->setParameter('datum', date_create())
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()
			->getResult();
	}

	/**
	 * @param $mrid
	 * @return Maaltijd[]
	 */
	public function getKomendeOpenRepetitieMaaltijden($mrid): mixed
	{
		return $this->createQueryBuilder('m')
			->where(
				'm.mlt_repetitie_id = :repetitie and m.gesloten = false and m.verwijderd = false and m.datum >= :datum'
			)
			->setParameter('repetitie', $mrid)
			->setParameter('datum', date_create())
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()
			->getResult();
	}

	/**
	 * @param $mrid
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijderRepetitieMaaltijden($mrid): void
	{
		$maaltijden = $this->findBy(['mlt_repetitie_id' => $mrid]);
		foreach ($maaltijden as $maaltijd) {
			$maaltijd->verwijderd = true;
			$this->_em->persist($maaltijd);
			$this->_em->flush();
		}
	}

	/**
	 * Called when a MaaltijdRepetitie is updated or is going to be deleted.
	 *
	 * @param int $mrid
	 *
	 * @return bool
	 */
	public function existRepetitieMaaltijden($mrid): bool
	{
		return $this->count(['mlt_repetitie_id' => $mrid]) > 0;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function update(Maaltijd $maaltijd): void
	{
		$this->_em->persist($maaltijd);
		$this->_em->flush();
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function delete(Maaltijd $maaltijd): void
	{
		$this->_em->remove($maaltijd);
		$this->_em->flush();
	}

	/**
	 * @param $query
	 * @return Maaltijd[]
	 */
	public function getSuggesties($query): mixed
	{
		return $this->createQueryBuilder('m')
			->where(
				'm.titel like :query or date_format(m.datum, \'%Y-%m-%d\') like :query or m.maaltijd_id like :query'
			)
			->setParameter('query', SqlUtil::sql_contains($query))
			->getQuery()
			->getResult();
	}
}
