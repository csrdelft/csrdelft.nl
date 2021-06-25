<?php

namespace CsrDelft\repository\pin;

use CsrDelft\common\CsrException;
use CsrDelft\entity\pin\PinTransactie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PinTransactieModel
 *
 * @package model\fiscaat
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/09/2017
 * @method PinTransactie|null find($id, $lockMode = null, $lockVersion = null)
 * @method PinTransactie|null findOneBy(array $criteria, array $orderBy = null)
 * @method PinTransactie[]    findAll()
 * @method PinTransactie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PinTransactieRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, PinTransactie::class);
	}

	/**
	 * @param string $from
	 * @param string $to
	 * @return int[]
	 */
	public function getPinTransactieInMoment($from, $to) {
		return $this->createQueryBuilder('t')
			->select('t.id')
			->where('t.datetime > :from and t.datetime < :to')
			->setParameter('from', $from)
			->setParameter('to', $to)
			->orderBy('t.datetime', 'DESC')
			->getQuery()->getResult();
	}

	/**
	 * @param int[] $ids
	 */
	public function clean($ids) {
		$this->createQueryBuilder('m')
			->delete()
			->where('m.id in (:ids)')
			->setParameter('ids', $ids)
			->getQuery()->execute();
	}

	/**
	 * @param PinTransactie $pinTransactie
	 * @return string
	 * @throws CsrException
	 */
	public function getKorteBeschrijving($pinTransactie) {
		return sprintf('€%.2f',$pinTransactie->getBedragInCenten()/100);
	}

	/**
	 * @param int $id
	 * @return PinTransactie
	 */
	public function get($id) {
		return $this->find($id);
	}
}
