<?php

namespace CsrDelft\repository\forum;

use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadReageren;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 * @method ForumDraadReageren|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumDraadReageren|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumDraadReageren[]    findAll()
 * @method ForumDraadReageren[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumDradenReagerenRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ForumDraadReageren::class);
	}

	protected function maakForumDraadReageren(
		ForumDeel $deel,
		$draad_id = null,
		$concept = null,
		$titel = null
	) {
		$reageren = new ForumDraadReageren();
		$reageren->forum_id = $deel->forum_id;
		$reageren->draad_id = (int) $draad_id;
		$reageren->uid = LoginService::getUid();
		$reageren->datum_tijd = date_create_immutable();
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
	protected function getReagerenDoorLid(ForumDeel $deel, $draad_id = null)
	{
		return $this->find([
			'forum_id' => (int) $deel->forum_id,
			'draad_id' => (int) $draad_id,
			'uid' => LoginService::getUid(),
		]);
	}

	public function getReagerenVoorDraad(ForumDraad $draad)
	{
		return $this->createQueryBuilder('r')
			->where(
				'r.draad_id = :draad_id and r.uid != :uid and r.datum_tijd > :datum_tijd'
			)
			->setParameters(new ArrayCollection([
				new Parameter('draad_id', $draad->draad_id),
				new Parameter('uid', LoginService::getUid()),
				new Parameter('datum_tijd', date_create_immutable(
					InstellingUtil::instelling('forum', 'reageren_tijd')
				)),
			]))
			->getQuery()
			->getResult();
	}

	public function getReagerenVoorDeel(ForumDeel $deel)
	{
		return $this->createQueryBuilder('r')
			->where(
				'r.forum_id = :forum_id and r.draad_id = 0 and r.uid != :uid and r.datum_tijd > :datum_tijd'
			)
			->setParameters(new ArrayCollection([
				new Parameter('forum_id' , $deel->forum_id),
				new Parameter('uid' , LoginService::getUid()),
				new Parameter('datum_tijd' , date_create_immutable(
					InstellingUtil::instelling('forum', 'reageren_tijd')
				)),
			]))
			->getQuery()
			->getResult();
	}

	public function verwijderLegeConcepten()
	{
		$this->createQueryBuilder('r')
			->where('r.concept IS NULL and r.datum_tijd < :datum_tijd')
			->setParameter(
				'datum_tijd',
				date_create_immutable(
					InstellingUtil::instelling('forum', 'reageren_tijd')
				)
			)
			->delete()
			->getQuery()
			->execute();
	}

	public function verwijderReagerenVoorDraad(array $draadIds)
	{
		$this->createQueryBuilder('r')
			->where('r.draad_id in (:draad_ids)')
			->setParameter('draad_ids', $draadIds)
			->delete()
			->getQuery()
			->execute();
	}

	public function verwijderReagerenVoorLeden($uids)
	{
		$this->createQueryBuilder('r')
			->where('r.uid in (:uids)')
			->setParameter('uids', $uids)
			->delete()
			->getQuery()
			->execute();
	}

	public function setWanneerReagerenDoorLid(ForumDeel $deel, $draad_id = null)
	{
		$reageren = $this->getReagerenDoorLid($deel, $draad_id);
		if ($reageren) {
			$reageren->datum_tijd = date_create_immutable();
			$this->getEntityManager()->persist($reageren);
			$this->getEntityManager()->flush();
		} else {
			$this->maakForumDraadReageren($deel, $draad_id);
		}
	}

	public function getConcept(ForumDeel $deel, $draad_id = null)
	{
		$reageren = $this->getReagerenDoorLid($deel, $draad_id);
		if ($reageren) {
			return $reageren->concept;
		}
		return null;
	}

	public function getConceptTitel(ForumDeel $deel)
	{
		$reageren = $this->getReagerenDoorLid($deel);
		if ($reageren) {
			return $reageren->titel;
		}
		return null;
	}

	public function setConcept(
		ForumDeel $deel,
		$draad_id = null,
		$concept = null,
		$titel = null
	) {
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
