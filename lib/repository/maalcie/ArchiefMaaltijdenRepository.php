<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\entity\maalcie\ArchiefMaaltijd;
use CsrDelft\entity\maalcie\Maaltijd;
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
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;

	public function __construct(
		ManagerRegistry $registry,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	) {
		parent::__construct($registry, ArchiefMaaltijd::class);

		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
	}

	protected $default_order = 'datum DESC, tijd DESC';

	public function vanMaaltijd(Maaltijd $maaltijd)
	{
		$archief = new ArchiefMaaltijd();
		$archief->maaltijd_id = $maaltijd->maaltijd_id;
		$archief->titel = $maaltijd->titel;
		$archief->datum = $maaltijd->datum;
		$archief->tijd = $maaltijd->tijd;
		$archief->prijs = $maaltijd->getPrijs();
		$archief->aanmeldingen = '';
		foreach (
			$this->maaltijdAanmeldingenRepository->getAanmeldingenVoorMaaltijd(
				$maaltijd
			)
			as $aanmelding
		) {
			if (!$aanmelding->uid) {
				$archief->aanmeldingen .= 'gast';
			} else {
				$archief->aanmeldingen .= $aanmelding->uid;
			}
			if ($aanmelding->abonnementRepetitie) {
				$archief->aanmeldingen .= '_abo';
			}
			if ($aanmelding->door_uid !== null) {
				$archief->aanmeldingen .= '_' . $aanmelding->door_uid;
			}
			$archief->aanmeldingen .= ',';
		}

		return $archief;
	}

	/**
	 * @param ArchiefMaaltijd $archiefMaaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function create(ArchiefMaaltijd $archiefMaaltijd)
	{
		$this->_em->persist($archiefMaaltijd);
		$this->_em->flush();
	}
}
