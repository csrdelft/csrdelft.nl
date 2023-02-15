<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\repository\AbstractRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee (brussee@live.nl)
 *
 * @method MaaltijdAanmelding|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaaltijdAanmelding|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaaltijdAanmelding[]    findAll()
 * @method MaaltijdAanmelding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaaltijdAanmeldingenRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, MaaltijdAanmelding::class);
	}

	/**
	 * @param $maaltijdenById
	 * @param $uid
	 * @return MaaltijdAanmelding[]
	 */
	public function getAanmeldingenVoorLid($maaltijdenById, $uid)
	{
		if (empty($maaltijdenById)) {
			return $maaltijdenById; // array()
		}

		$aanmeldingen = [];
		foreach ($maaltijdenById as $maaltijd) {
			$aanmeldingen[] = $this->find([
				'maaltijd_id' => $maaltijd->maaltijd_id,
				'uid' => $uid,
			]);
		}

		$result = [];
		foreach ($aanmeldingen as $aanmelding) {
			if ($aanmelding) {
				$aanmelding->maaltijd = $maaltijdenById[$aanmelding->maaltijd_id];
				$result[$aanmelding->maaltijd_id] = $aanmelding;
			}
		}
		return $result;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return MaaltijdAanmelding[]
	 */
	public function getAanmeldingenVoorMaaltijd(Maaltijd $maaltijd)
	{
		$aanmeldingen = $this->findBy(['maaltijd_id' => $maaltijd->maaltijd_id]);
		$lijst = [];
		foreach ($aanmeldingen as $aanmelding) {
			$aanmelding->maaltijd = $maaltijd;
			$naam = $aanmelding->profiel->getNaam('streeplijst');
			$lijst[$naam] = $aanmelding;
			for ($i = $aanmelding->aantal_gasten; $i > 0; $i--) {
				$gast = new MaaltijdAanmelding();
				$gast->door_uid = $aanmelding->profiel->uid;
				$gast->door_profiel = $aanmelding->profiel;
				$lijst[$naam . 'gast' . $i] = $gast;
			}
		}
		ksort($lijst);
		return $lijst;
	}

	/**
	 * Called when a Maaltijd is being deleted.
	 *
	 * @param int $mid maaltijd-id
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function deleteAanmeldingenVoorMaaltijd($mid)
	{
		$aanmeldingen = $this->findBy(['maaltijd_id', $mid]);
		foreach ($aanmeldingen as $aanmelding) {
			$this->getEntityManager()->remove($aanmelding);
		}
		$this->getEntityManager()->flush();
	}
}
