<?php

namespace CsrDelft\repository\forum;

use CsrDelft\entity\forum\ForumDraadReageren;
use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 * @method ForumDraadReageren|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumDraadReageren|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumDraadReageren[]    findAll()
 * @method ForumDraadReageren[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumDradenReagerenRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, ForumDraadReageren::class);
	}

	protected function maakForumDraadReageren(ForumDeel $deel, $draad_id = null, $concept = null, $titel = null) {
		$reageren = new ForumDraadReageren();
		$reageren->forum_id = $deel->forum_id;
		$reageren->draad_id = (int)$draad_id;
		$reageren->uid = LoginModel::getUid();
		$reageren->datum_tijd = date_create();
		$reageren->concept = $concept;
		$reageren->titel = $titel;
		$this->getEntityManager()->persist($reageren);
		$this->getEntityManager()->flush();
		return $reageren;
	}

	/**
	 * Fetch reageren object voor deel of draad.
	 *
	 * @param ForumDeel $deel
	 * @param int $draad_id
	 * @return ForumDraadReageren
	 */
	protected function getReagerenDoorLid(ForumDeel $deel, $draad_id = null) {
		return $this->find(['forum_id' => $deel->forum_id, 'draad_id' => (int) $draad_id, 'uid' => LoginModel::getUid()]);
	}

	public function getReagerenVoorDraad(ForumDraad $draad) {
		return $this->createQueryBuilder('r')
			->where('r.draad_id = :draad_id and r.uid != :uid and r.datum_tijd > :datum_tijd')
			->setParameters(['draad_id' => $draad->draad_id, 'uid' => LoginModel::getUid(), 'datum_tijd' => date_create(instelling('forum', 'reageren_tijd'))])
			->getQuery()->getResult();
	}

	public function getReagerenVoorDeel(ForumDeel $deel) {
		return $this->createQueryBuilder('r')
			->where('r.forum_id = :forum_id and r.draad_id = 0 and r.uid != :uid and datum_tijd > :datum_tijd')
			->setParameters(['forum_id' => $deel->forum_id, 'uid' => LoginModel::getUid(), 'datum_tijd' => date_create(instelling('forum', 'reageren_tijd'))])
			->getQuery()->getResult();
	}

	public function verwijderLegeConcepten() {
		$this->createQueryBuilder('r')
			->where('r.concept IS NULL and r.datum_tijd < :datum_tijd')
			->setParameter('datum_tijd', date_create(instelling('forum', 'reageren_tijd')))
			->delete()
			->getQuery()->execute();
	}

	public function verwijderReagerenVoorDraad(ForumDraad $draad) {
		$this->createQueryBuilder('r')
			->where('r.draad_id = :draad_id')
			->setParameter('draad_id', $draad->draad_id)
			->delete()
			->getQuery()->execute();
	}

	public function verwijderReagerenVoorLid($uid) {
		$this->createQueryBuilder('r')
			->where('r.uid = :uid')
			->setParameter('uid', $uid)
			->delete()
			->getQuery()->execute();
	}

	public function setWanneerReagerenDoorLid(ForumDeel $deel, $draad_id = null) {
		$reageren = $this->getReagerenDoorLid($deel, $draad_id);
		if ($reageren) {
			$reageren->datum_tijd = date_create();
			$this->getEntityManager()->persist($reageren);
			$this->getEntityManager()->flush();
		} else {
			$this->maakForumDraadReageren($deel, $draad_id);
		}
	}

	public function getConcept(ForumDeel $deel, $draad_id = null) {
		$reageren = $this->getReagerenDoorLid($deel, $draad_id);
		if ($reageren) {
			return $reageren->concept;
		}
		return null;
	}

	public function getConceptTitel(ForumDeel $deel) {
		$reageren = $this->getReagerenDoorLid($deel);
		if ($reageren) {
			return $reageren->titel;
		}
		return null;
	}

	public function setConcept(ForumDeel $deel, $draad_id = null, $concept = null, $titel = null) {
		$reageren = $this->getReagerenDoorLid($deel, $draad_id);
		if (empty($concept)) {
			if ($reageren) {
				$this->getEntityManager()->remove($reageren);
				$this->getEntityManager()->flush();
			}
		} elseif ($reageren) {
			$reageren->concept = $concept;
			$reageren->titel = $titel;
			$this->getEntityManager()->persist($reageren);
			$this->getEntityManager()->flush();
		} else {
			$this->maakForumDraadReageren($deel, $draad_id, $concept, $titel);
		}
	}

}
