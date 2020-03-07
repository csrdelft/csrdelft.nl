<?php

namespace CsrDelft\repository\forum;

use CsrDelft\entity\forum\ForumDraadVerbergen;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 * @method ForumDraadVerbergen|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumDraadVerbergen|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumDraadVerbergen[]    findAll()
 * @method ForumDraadVerbergen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumDradenVerbergenRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, ForumDraadVerbergen::class);
	}

	protected function maakForumDraadVerbergen($draad_id) {
		$verbergen = new ForumDraadVerbergen();
		$verbergen->draad_id = $draad_id;
		$verbergen->uid = LoginModel::getUid();
		$this->getEntityManager()->persist($verbergen);
		$this->getEntityManager()->flush();
		return $verbergen;
	}

	public function getAantalVerborgenVoorLid() {
		return count($this->findBy(['uid' => LoginModel::getUid()]));
	}

	public function getVerbergenVoorLid(ForumDraad $draad) {
		return $this->find(['draad_id' => $draad->draad_id, 'uid' => LoginModel::getUid()]);
	}

	public function setVerbergenVoorLid(ForumDraad $draad, $verbergen = true) {
		if ($verbergen) {
			if (!$this->getVerbergenVoorLid($draad)) {
				$this->maakForumDraadVerbergen($draad->draad_id);
			}
		} elseif ($entity = $this->getVerbergenVoorLid($draad)) {
			$this->getEntityManager()->remove($entity);
			$this->getEntityManager()->flush();
		}
	}

	public function toonAllesVoorLid($uid) {
		$manager = $this->getEntityManager();
		foreach ($this->findBy(['uid' =>$uid]) as $verborgen) {
			$manager->remove($verborgen);
		}
		$manager->flush();
	}

	public function toonDraadVoorIedereen(ForumDraad $draad) {
		$manager = $this->getEntityManager();
		foreach ($this->findBy(['draad_id' => $draad->draad_id]) as $verborgen) {
			$manager->remove($verborgen);
		}
		$manager->flush();
	}

}
