<?php

namespace CsrDelft\repository\pin;
use CsrDelft\common\CsrException;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 * @method PinTransactieMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method PinTransactieMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method PinTransactieMatch[]    findAll()
 * @method PinTransactieMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PinTransactieMatchRepository extends AbstractRepository {
	/**
	 * @var PinTransactieRepository
	 */
	private $pinTransactieRepository;
	/**
	 * @var CiviBestellingModel
	 */
	private $civiBestellingModel;

	/**
	 * PinTransactieMatchModel constructor.
	 * @param ManagerRegistry $registry
	 * @param PinTransactieRepository $pinTransactieRepository
	 * @param CiviBestellingModel $civiBestellingModel
	 */
	public function __construct(ManagerRegistry $registry, PinTransactieRepository $pinTransactieRepository, CiviBestellingModel $civiBestellingModel) {
		parent::__construct($registry, PinTransactieMatch::class);
		$this->pinTransactieRepository = $pinTransactieRepository;
		$this->civiBestellingModel = $civiBestellingModel;
	}

	/**
	 * @return PinTransactieMatch[]
	 */
	public function metFout() {
		return $this->createQueryBuilder('m')
			->where('m.status != \'match\' and m.status != \'verwijderd\'')
			->getQuery()->getResult();
	}

	/**
	 * @param int[] $ids
	 */
	public function cleanByBestellingIds($ids) {
		$this->createQueryBuilder('m')
			->delete()
			->where('m.bestelling_id in (:ids)')
			->setParameter('ids', $ids)
			->getQuery()->execute();
	}

	/**
	 * @param int[] $ids
	 */
	public function cleanByTransactieIds($ids) {
		$this->createQueryBuilder('m')
			->delete()
			->where('m.transactie_id in (:ids)')
			->setParameter('ids', $ids)
			->getQuery()->execute();
	}

	/**
	 * @param PinTransactieMatch $pinTransactieMatch
	 * @throws CsrException
	 */
	public function getMoment($pinTransactieMatch) {
		if ($pinTransactieMatch->transactie_id !== null) {
			return $this->pinTransactieRepository->get($pinTransactieMatch->transactie_id)->datetime;
		} elseif ($pinTransactieMatch->bestelling_id !== null) {
			return $this->civiBestellingModel->get($pinTransactieMatch->bestelling_id)->moment;
		} else {
			throw new CsrException('Pin Transactie Match heeft geen bestelling en transactie.');
		}
	}
}
