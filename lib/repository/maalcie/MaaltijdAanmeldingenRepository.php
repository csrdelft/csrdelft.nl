<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\fiscaat\CiviProductRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccessService;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
	/**
	 * @var AccessService
	 */
	private $accessService;
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;

	public function __construct(
		ManagerRegistry $registry,
		AccessService $accessService,
		AccountRepository $accountRepository
	) {
		parent::__construct($registry, MaaltijdAanmelding::class);
		$this->accessService = $accessService;
		$this->accountRepository = $accountRepository;
	}

	/**
	 * @param string $uid
	 * @param string $filter
	 * @return bool Of de gebruiker voldoet aan het filter
	 * @throws CsrGebruikerException Als de gebruiker niet bestaat
	 */
	public function checkAanmeldFilter($uid, $filter)
	{
		$account = $this->accountRepository->find($uid); // false if account does not exist
		if (!$account) {
			throw new CsrGebruikerException('Lid bestaat niet: $uid =' . $uid);
		}
		if (empty($filter)) {
			return true;
		}
		return $this->accessService->isUserGranted($account, $filter);
	}

	public function getIsAangemeld($mid, $uid)
	{
		return $this->find(['maaltijd_id' => $mid, 'uid' => $uid]) != null;
	}

	/**
	 * @param $mid
	 * @param $uid
	 * @return MaaltijdAanmelding
	 */
	public function loadAanmelding($mid, $uid)
	{
		$aanmelding = $this->find(['maaltijd_id' => $mid, 'uid' => $uid]);
		if ($aanmelding == null) {
			throw new CsrGebruikerException(
				'Load aanmelding faalt: Not found $mid =' . $mid
			);
		}
		return $aanmelding;
	}

	/**
	 * Called when a MaaltijdAbonnement is being deleted (turned off) or a MaaltijdRepetitie is being deleted.
	 *
	 * @param MaaltijdRepetitie $repetitie
	 * @param string $uid Lid voor wie het MaaltijdAbonnement wordt uitschakeld
	 *
	 * @return int|null
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function afmeldenDoorAbonnement(MaaltijdRepetitie $repetitie, $uid)
	{
		// afmelden bij maaltijden waarbij dit abonnement de aanmelding heeft gedaan
		$maaltijden = ContainerFacade::getContainer()
			->get(MaaltijdenRepository::class)
			->getKomendeOpenRepetitieMaaltijden($repetitie->mlt_repetitie_id);
		if (empty($maaltijden)) {
			return 0;
		}
		$byMid = [];
		foreach ($maaltijden as $maaltijd) {
			if (!$maaltijd->gesloten && !$maaltijd->verwijderd) {
				$byMid[$maaltijd->maaltijd_id] = $maaltijd;
			}
		}
		$aanmeldingen = $this->getAanmeldingenVoorLid($byMid, $uid);
		$aantal = 0;
		foreach ($aanmeldingen as $mid => $aanmelding) {
			if (
				$aanmelding->abonnementRepetitie &&
				$repetitie->mlt_repetitie_id ===
					$aanmelding->abonnementRepetitie->mlt_repetitie_id
			) {
				$this->getEntityManager()->remove($aanmelding);
				$aantal++;
			}
		}
		$this->getEntityManager()->flush();
		return $aantal;
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

	/**
	 * Controleer of alle aanmeldingen voor de maaltijden nog in overeenstemming zijn met het aanmeldfilter.
	 *
	 * @param string $filter
	 * @param Maaltijd[] $maaltijden
	 * @return int
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function checkAanmeldingenFilter($filter, $maaltijden)
	{
		$mids = [];
		foreach ($maaltijden as $maaltijd) {
			if (!$maaltijd->gesloten && !$maaltijd->verwijderd) {
				$mids[] = $maaltijd->maaltijd_id;
			}
		}
		if (empty($mids)) {
			return 0;
		}
		$aantal = 0;
		$aanmeldingen = [];
		foreach ($mids as $mid) {
			$aanmeldingen = array_merge(
				$aanmeldingen,
				$this->findBy(['maaltijd_id' => $mid])
			);
		}
		foreach ($aanmeldingen as $aanmelding) {
			// check filter voor elk aangemeld lid
			$uid = $aanmelding->uid;
			if (!$this->checkAanmeldFilter($uid, $filter)) {
				// verwijder aanmelding indien niet toegestaan
				$aantal += 1 + $aanmelding->aantal_gasten;
				$this->getEntityManager()->remove($aanmelding);
			}
		}
		$this->getEntityManager()->flush();
		return $aantal;
	}
}
