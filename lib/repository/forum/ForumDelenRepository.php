<?php

namespace CsrDelft\repository\forum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\entity\forum\ForumCategorie;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumPost;
use CsrDelft\entity\forum\ForumZoeken;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 * @method ForumDeel|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumDeel|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumDeel[]    findAll()
 * @method ForumDeel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumDelenRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ForumDeel::class);
	}

	/**
	 * @param $id
	 * @return ForumDeel
	 * @throws CsrGebruikerException
	 */
	public function get($id)
	{
		$deel = $this->find($id);
		if (!$deel) {
			throw new CsrGebruikerException('Forum bestaat niet!');
		}
		return $deel;
	}

	/**
	 * @param ForumDeel $entity
	 * @return int
	 */
	public function create(ForumDeel $entity)
	{
		$this->getEntityManager()->persist($entity);
		$this->getEntityManager()->flush();
		return $entity->forum_id;
	}

	public function nieuwForumDeel()
	{
		$deel = new ForumDeel();
		$deel->categorie_id = 0;
		$deel->titel = 'Nieuw deelforum';
		$deel->omschrijving = '';
		$deel->rechten_lezen = P_FORUM_READ;
		$deel->rechten_posten = P_FORUM_POST;
		$deel->rechten_modereren = P_FORUM_MOD;
		$deel->volgorde = 0;
		return $deel;
	}

	public function bestaatForumDeel($id)
	{
		return $this->findBy($id) !== null;
	}

	public function getForumDelenVoorCategorie(ForumCategorie $categorie)
	{
		return $this->findBy(
			['categorie_id' => $categorie->categorie_id],
			['volgorde' => 'ASC']
		);
	}

	public function getForumDelenVoorLid($rss = false)
	{
		/** @var ForumDeel[] $delen */
		$delen = ArrayUtil::group_by_distinct(
			'forum_id',
			$this->findBy([], ['volgorde' => 'ASC'])
		);
		foreach ($delen as $forum_id => $deel) {
			if (!$deel->magLezen($rss)) {
				unset($delen[$forum_id]);
			}
		}
		return $delen;
	}

	/**
	 * Geeft de mogelijke opties om een draadje mee te delen.
	 *
	 * @param ForumDeel $deel
	 * @return ForumDeel[]
	 */
	public function getForumDelenOptiesOmTeDelen(ForumDeel $deel)
	{
		$qb = $this->createQueryBuilder('r')
			->where(
				'r.rechten_posten != :rechten_posten and r.rechten_posten LIKE :query'
			)
			->setParameter('rechten_posten', $deel->rechten_posten);
		if (strpos($deel->rechten_posten, 'verticale:') !== false) {
			$qb->setParameter('query', '%verticale:%');
			$qb->orderBy('r.titel', 'ASC');
		} elseif (strpos($deel->rechten_posten, 'lidjaar:') !== false) {
			$qb->setParameter('query', '%lidjaar:%');
			$qb->orderBy('r.titel', 'DESC');
		} else {
			return [];
		}

		return $qb->getQuery()->getResult();
	}

	public function update(ForumDeel $deel)
	{
		$this->getEntityManager()->persist($deel);
		$this->getEntityManager()->flush();
	}
}
