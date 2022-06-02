<?php

namespace CsrDelft\repository\forum;

use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadVerbergen;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 * @method ForumDraadVerbergen|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumDraadVerbergen|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumDraadVerbergen[]    findAll()
 * @method ForumDraadVerbergen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumDradenVerbergenRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ForumDraadVerbergen::class);
	}

	protected function maakForumDraadVerbergen(ForumDraad $draad)
	{
		$verbergen = new ForumDraadVerbergen();
		$verbergen->draad = $draad;
		$verbergen->draad_id = $draad->draad_id;
		$verbergen->uid = LoginService::getUid();
		$this->getEntityManager()->persist($verbergen);
		$this->getEntityManager()->flush();
		return $verbergen;
	}

	public function getAantalVerborgenVoorLid()
	{
		return count($this->findBy(['uid' => LoginService::getUid()]));
	}

	public function getVerbergenVoorLid(ForumDraad $draad)
	{
		return $this->find(['draad_id' => $draad->draad_id, 'uid' => LoginService::getUid()]);
	}

	public function setVerbergenVoorLid(ForumDraad $draad, $verbergen = true)
	{
		if ($verbergen) {
			if (!$this->getVerbergenVoorLid($draad)) {
				$this->maakForumDraadVerbergen($draad);
			}
		} elseif ($entity = $this->getVerbergenVoorLid($draad)) {
			$this->getEntityManager()->remove($entity);
			$this->getEntityManager()->flush();
		}
	}

	public function toonAllesVoorLeden(array $uids)
	{
		$this->createQueryBuilder('v')
			->delete()
			->where('v.uid in (:uids)')
			->setParameter('uids', $uids)
			->getQuery()->execute();
	}

	public function toonDraadVoorIedereen(array $draadIds)
	{
		$this->createQueryBuilder('v')
			->delete()
			->where('v.draad_id in (:draad_ids)')
			->setParameter('draad_ids', $draadIds)
			->getQuery()->execute();
	}

}
