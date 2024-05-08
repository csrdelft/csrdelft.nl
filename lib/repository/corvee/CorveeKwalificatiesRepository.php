<?php

namespace CsrDelft\repository\corvee;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\corvee\CorveeFunctie;
use CsrDelft\entity\corvee\CorveeKwalificatie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method CorveeKwalificatie|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorveeKwalificatie|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorveeKwalificatie[]    findAll()
 * @method CorveeKwalificatie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorveeKwalificatiesRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, CorveeKwalificatie::class);
	}

	public function getKwalificatiesVoorFunctie($fid): array
	{
		return $this->findBy(['functie_id' => $fid]);
	}

	/**
	 * Eager loading of corveefuncties.
	 *
	 * @param string $uid
	 * @return CorveeKwalificatie[]
	 */
	public function getKwalificatiesVanLid($uid): array
	{
		return $this->findBy(['uid' => $uid]);
	}

	public function isLidGekwalificeerdVoorFunctie($uid, $fid)
	{
		return $this->find(['uid' => $uid, 'functie_id' => $fid]) != null;
	}

	public function nieuw(CorveeFunctie $functie): CorveeKwalificatie
	{
		$kwalificatie = new CorveeKwalificatie();
		$kwalificatie->setCorveeFunctie($functie);
		$kwalificatie->wanneer_toegewezen = date_create_immutable();
		return $kwalificatie;
	}

	/**
	 * @param CorveeKwalificatie $kwali
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function kwalificatieToewijzen(CorveeKwalificatie $kwali): void
	{
		if (
			$this->find([
				'uid' => $kwali->profiel->uid,
				'functie_id' => $kwali->corveeFunctie->functie_id,
			]) != null
		) {
			throw new CsrGebruikerException('Is al gekwalificeerd!');
		}

		$this->_em->persist($kwali);
		$this->_em->flush();
	}

	/**
	 * @param CorveeKwalificatie $kwalificatie
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function kwalificatieIntrekken(CorveeKwalificatie $kwalificatie): void
	{
		$this->_em->remove($kwalificatie);
		$this->_em->flush();
	}

	/**
	 * @param $uid
	 * @param $fid
	 * @return CorveeKwalificatie|null
	 */
	public function getKwalificatie($uid, $fid): ?CorveeKwalificatie
	{
		return $this->find(['uid' => $uid, 'functie_id' => $fid]);
	}
}
