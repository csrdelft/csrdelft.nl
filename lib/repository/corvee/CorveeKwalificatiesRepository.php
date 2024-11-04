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


	/**
	 * Eager loading of corveefuncties.
	 *
	 * @param string $uid
	 * @return CorveeKwalificatie[]
	 */
	public function getKwalificatiesVanLid($uid)
	{
		return $this->findBy(['uid' => $uid]);
	}

	public function isLidGekwalificeerdVoorFunctie(string $uid, int $fid)
	{
		return $this->find(['uid' => $uid, 'functie_id' => $fid]) != null;
	}

	public function nieuw(CorveeFunctie $functie)
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
	public function kwalificatieToewijzen(CorveeKwalificatie $kwali)
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
}
