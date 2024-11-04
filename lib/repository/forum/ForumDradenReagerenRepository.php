<?php

namespace CsrDelft\repository\forum;

use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadReageren;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
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


	public function getReagerenVoorDraad(ForumDraad $draad)
	{
		return $this->createQueryBuilder('r')
			->where(
				'r.draad_id = :draad_id and r.uid != :uid and r.datum_tijd > :datum_tijd'
			)
			->setParameters([
				'draad_id' => $draad->draad_id,
				'uid' => LoginService::getUid(),
				'datum_tijd' => date_create_immutable(
					InstellingUtil::instelling('forum', 'reageren_tijd')
				),
			])
			->getQuery()
			->getResult();
	}

	public function getReagerenVoorDeel(ForumDeel $deel)
	{
		return $this->createQueryBuilder('r')
			->where(
				'r.forum_id = :forum_id and r.draad_id = 0 and r.uid != :uid and r.datum_tijd > :datum_tijd'
			)
			->setParameters([
				'forum_id' => $deel->forum_id,
				'uid' => LoginService::getUid(),
				'datum_tijd' => date_create_immutable(
					InstellingUtil::instelling('forum', 'reageren_tijd')
				),
			])
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

	/**
	 * @psalm-param list<mixed> $uids
	 */
	public function verwijderReagerenVoorLeden(array $uids)
	{
		$this->createQueryBuilder('r')
			->where('r.uid in (:uids)')
			->setParameter('uids', $uids)
			->delete()
			->getQuery()
			->execute();
	}

	public function setWanneerReagerenDoorLid(ForumDeel $deel, int|null $draad_id = null)
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

	public function getConcept(ForumDeel $deel, int|null $draad_id = null)
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

	/**
	 * @param null|string $titel
	 * @param null|string $concept
	 */
	public function setConcept(
		ForumDeel $deel,
		int|null $draad_id = null,
		string|null $concept = null,
		string|null $titel = null
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
