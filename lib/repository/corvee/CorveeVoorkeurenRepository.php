<?php

namespace CsrDelft\repository\corvee;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\corvee\CorveeVoorkeur;
use CsrDelft\entity\corvee\CorveeVoorkeurMatrixDTO;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee (brussee@live.nl)
 *
 * @method CorveeVoorkeur|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorveeVoorkeur|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorveeVoorkeur[]    findAll()
 * @method CorveeVoorkeur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorveeVoorkeurenRepository extends AbstractRepository
{
	public function __construct(
		ManagerRegistry $registry,
		private readonly CorveeRepetitiesRepository $corveeRepetitiesRepository,
		private readonly CorveeKwalificatiesRepository $corveeKwalificatiesRepository
	) {
		parent::__construct($registry, CorveeVoorkeur::class);
	}

	/**
	 * Geeft de ingeschakelde voorkeuren voor een lid terug en ook
	 * de voorkeuren die het lid nog kan inschakelen.
	 * Dat laatste kan alleen voor het ingelogde lid.
	 * Voor elk ander lid worden de permissies niet gefilterd.
	 *
	 * @param string $uid
	 * @param boolean $uitgeschakeld
	 * @return CorveeVoorkeur[]
	 */
	public function getVoorkeurenVoorLid($uid, $uitgeschakeld = false)
	{
		$repById = $this->corveeRepetitiesRepository->getVoorkeurbareRepetities(); // grouped by crid
		$lijst = [];
		$voorkeuren = $this->findBy(['uid' => $uid]);
		foreach ($voorkeuren as $voorkeur) {
			$crid = $voorkeur->crv_repetitie_id;
			if (!array_key_exists($crid, $repById)) {
				// ingeschakelde voorkeuren altijd weergeven
				$repById[$crid] = $voorkeur->corveeRepetitie;
			}
			$voorkeur->van_uid = $uid;
			$lijst[$crid] = $voorkeur;
		}
		foreach ($repById as $crid => $repetitie) {
			if ($repetitie->corveeFunctie->kwalificatie_benodigd) {
				if (
					!$this->corveeKwalificatiesRepository->isLidGekwalificeerdVoorFunctie(
						$uid,
						$repetitie->corveeFunctie->functie_id
					)
				) {
					continue;
				}
			}
			if (!array_key_exists($crid, $lijst)) {
				// uitgeschakelde voorkeuren weergeven
				if ($uitgeschakeld) {
					$voorkeur = new CorveeVoorkeur();
					$voorkeur->setCorveeRepetitie($repetitie);
					$voorkeur->van_uid = $uid;
					$lijst[$crid] = $voorkeur;
				}
			}
		}
		ksort($lijst);
		return $lijst;
	}

	/**
	 * Bouwt matrix voor alle repetities en voorkeuren van alle leden in format CorveeVoorkeur[uid][crid]
	 *
	 * @return CorveeVoorkeur[][]
	 */
	public function getVoorkeurenMatrix()
	{
		$repById = $this->corveeRepetitiesRepository->getVoorkeurbareRepetities(); // grouped by crid
		$leden_voorkeuren = $this->loadLedenVoorkeuren();
		$matrix = [];
		foreach ($leden_voorkeuren as $lv) {
			// build matrix
			$crid = $lv->crv_repetitie_id;
			$uid = $lv->uid;
			$voorkeur = new CorveeVoorkeur();
			$voorkeur->setCorveeRepetitie($repById[$crid]);
			if ($lv->voorkeur) {
				// ingeschakelde voorkeuren
				$voorkeur->setProfiel(ProfielRepository::get($uid));
			}
			$voorkeur->setCorveeRepetitie($repById[$crid]);
			$voorkeur->van_uid = $uid;
			$matrix[$uid][$crid] = $voorkeur;
			ksort($repById);
			ksort($matrix[$uid]);
		}
		return [$matrix, $repById];
	}

	/**
	 * @return CorveeVoorkeurMatrixDTO[]
	 */
	private function loadLedenVoorkeuren()
	{
		return $this->_em
			->createQuery(
				<<<'DQL'
SELECT NEW CsrDelft\entity\corvee\CorveeVoorkeurMatrixDTO(p.uid, r.crv_repetitie_id, v.uid)
FROM CsrDelft\entity\profiel\Profiel p
JOIN CsrDelft\entity\corvee\CorveeRepetitie r
LEFT JOIN CsrDelft\entity\corvee\CorveeVoorkeur v WITH v.uid = p.uid AND v.corveeRepetitie = r
WHERE r.voorkeurbaar = true AND p.status IN ('S_LID', 'S_GASTLID', 'S_NOVIET')
ORDER BY p.achternaam ASC, p.voornaam ASC
DQL
			)
			->getResult();
	}

	/**
	 * @param CorveeVoorkeur $voorkeur
	 * @return CorveeVoorkeur
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function inschakelenVoorkeur(CorveeVoorkeur $voorkeur)
	{
		if ($this->getHeeftVoorkeur($voorkeur->crv_repetitie_id, $voorkeur->uid)) {
			throw new CsrGebruikerException('Voorkeur al ingeschakeld');
		}
		$repetitie = $voorkeur->corveeRepetitie;
		if (!$repetitie->voorkeurbaar) {
			throw new CsrGebruikerException('Niet voorkeurbaar');
		}
		if ($repetitie->corveeFunctie->kwalificatie_benodigd) {
			if (
				!$this->corveeKwalificatiesRepository->isLidGekwalificeerdVoorFunctie(
					$voorkeur->uid,
					$repetitie->corveeFunctie->functie_id
				)
			) {
				throw new CsrGebruikerException('Niet gekwalificeerd');
			}
		}

		$this->_em->persist($voorkeur);
		$this->_em->flush();

		return $voorkeur;
	}

	public function getHeeftVoorkeur($crid, $uid)
	{
		return $this->find(['uid' => $uid, 'crv_repetitie_id' => $crid]) != null;
	}

	/**
	 * @param CorveeVoorkeur $voorkeur
	 * @return CorveeVoorkeur
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function uitschakelenVoorkeur(CorveeVoorkeur $voorkeur)
	{
		if (!$this->getHeeftVoorkeur($voorkeur->crv_repetitie_id, $voorkeur->uid)) {
			throw new CsrGebruikerException('Voorkeur al uitgeschakeld');
		}

		$this->_em->remove($voorkeur);
		$this->_em->flush();

		$voorkeur->uid = null;

		return $voorkeur;
	}

	/**
	 * Called when a CorveeRepetitie is being deleted.
	 * This is only possible after all CorveeVoorkeuren are deleted of this CorveeRepetitie (db foreign key)
	 *
	 * @param $crid
	 * @return int amount of deleted voorkeuren
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijderVoorkeuren($crid)
	{
		$voorkeuren = $this->findBy(['corveeRepetitie' => $crid]);
		$num = count($voorkeuren);
		foreach ($voorkeuren as $voorkeur) {
			$this->_em->remove($voorkeur);
		}
		$this->_em->flush();

		return $num;
	}

	/**
	 * Called when a Lid is being made Lid-af.
	 *
	 * @param $uid
	 * @return int amount of deleted voorkeuren
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijderVoorkeurenVoorLid($uid)
	{
		$voorkeuren = $this->findBy(['uid' => $uid]);
		$num = count($voorkeuren);
		foreach ($voorkeuren as $voorkeur) {
			$this->_em->remove($voorkeur);
		}
		$this->_em->flush();

		return $num;
	}

	public function getVoorkeur($crid, $uid)
	{
		return $this->find(['uid' => $uid, 'crv_repetitie_id' => $crid]);
	}
}
