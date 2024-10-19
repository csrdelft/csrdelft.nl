<?php

namespace CsrDelft\repository\forum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadGelezen;
use CsrDelft\entity\forum\ForumPost;
use CsrDelft\entity\forum\ForumZoeken;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\Paging;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\CsrBB;
use DateInterval;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * @method ForumPost|null find($id, $lockMode = null, $lockVersion = null)
 */
class ForumPostsRepository extends AbstractRepository implements Paging
{
	/**
	 * Huidige pagina
	 * @var int
	 */
	private $pagina;
	/**
	 * Aantal posts per pagina
	 * Waarschuwing, is lazy, gebruik @see ForumPostsRepository::getAantalPerPagina()
	 * @var int|null
	 */
	private $per_pagina;
	/**
	 * Totaal aantal paginas per forumdraad
	 * @var int[]
	 */
	private $aantal_paginas;
	/**
	 * Totaal aantal posts die wachten op goedkeuring
	 * @var int
	 */
	private $aantal_wacht;

	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ForumPost::class);
		$this->pagina = 1;
		$this->aantal_paginas = [];
	}

	/**
	 * @return ForumPost[]
	 */
	public function findAll(): array
	{
		return $this->findBy([]);
	}

	/**
	 * @param array $criteria
	 * @param array|null $orderBy
	 * @param null $limit
	 * @param null $offset
	 * @return PersistentCollection|ForumPost[]
	 */
	public function findBy(
		array $criteria,
		array $orderBy = null,
		$limit = null,
		$offset = null
	) {
		$orderBy ??= ['datum_tijd' => 'ASC'];
		return parent::findBy($criteria, $orderBy, $limit, $offset);
	}

	/**
	 * @param $id
	 * @return ForumPost
	 * @throws CsrGebruikerException
	 */
	public function get($id)
	{
		$post = $this->find($id);
		if (!$post) {
			throw new CsrGebruikerException('Forum-reactie bestaat niet!');
		}
		return $post;
	}

	public function getAantalPerPagina()
	{
		if (!$this->per_pagina) {
			$this->per_pagina = (int) InstellingUtil::lid_instelling(
				'forum',
				'posts_per_pagina'
			);
		}
		return $this->per_pagina;
	}

	public function setAantalPerPagina($aantal)
	{
		$this->per_pagina = (int) $aantal;
	}

	public function getHuidigePagina()
	{
		return $this->pagina;
	}

	public function setLaatstePagina($draad_id)
	{
		$this->pagina = $this->getAantalPaginas($draad_id);
	}

	public function getAantalPaginas($draad_id)
	{
		if (!array_key_exists($draad_id, $this->aantal_paginas)) {
			$forumDradenRepository = ContainerFacade::getContainer()->get(
				ForumDradenRepository::class
			);
			$draad = $forumDradenRepository->get($draad_id);
			if ($draad->pagina_per_post) {
				$this->per_pagina = 1;
			} else {
				$this->per_pagina = (int) InstellingUtil::lid_instelling(
					'forum',
					'posts_per_pagina'
				);
			}
			$this->aantal_paginas[$draad_id] = (int) ceil(
				$this->count([
					'draad_id' => $draad_id,
					'wacht_goedkeuring' => false,
					'verwijderd' => false,
				]) / $this->getAantalPerPagina()
			);
		}
		return max(1, $this->aantal_paginas[$draad_id]);
	}

	public function getAantalOngelezenPosts(ForumDraad $draad)
	{
		$qb = $this->createQueryBuilder('fp')
			->select('count(fp.post_id)')
			->where(
				'fp.draad_id = :draad_id and fp.wacht_goedkeuring = false and fp.verwijderd = false'
			)
			->setParameter('draad_id', $draad->draad_id);
		$gelezen = $draad->getWanneerGelezen();
		if ($gelezen) {
			$qb->andWhere('fp.laatst_gewijzigd > :laatst_gewijzigd');
			$qb->setParameter('laatst_gewijzigd', $gelezen->datum_tijd);
		}
		return $qb->getQuery()->getSingleScalarResult();
	}

	public function getPaginaVoorPost(ForumPost $post)
	{
		$count = $this->createQueryBuilder('fp')
			->select('count(fp.post_id)')
			->where(
				'fp.draad_id = :draad_id and fp.post_id <= :post_id and fp.wacht_goedkeuring = false and fp.verwijderd = false'
			)
			->setParameter('draad_id', $post->draad_id)
			->setParameter('post_id', $post->post_id)
			->getQuery()
			->getSingleScalarResult();
		return (int) ceil($count / $this->getAantalPerPagina());
	}

	public function setPaginaVoorLaatstGelezen(ForumDraadGelezen $gelezen)
	{
		$count =
			1 +
			$this->createQueryBuilder('fp')
				->select('count(fp.post_id)')
				->where(
					'fp.draad_id = :draad_id and fp.datum_tijd <= :datum_tijd and fp.wacht_goedkeuring = false and fp.verwijderd = false'
				)
				->setParameter('draad_id', $gelezen->draad_id)
				->setParameter('datum_tijd', $gelezen->datum_tijd)
				->getQuery()
				->getSingleScalarResult();
		$this->getAantalPaginas($gelezen->draad_id); // set per_pagina
		$this->setHuidigePagina(
			(int) ceil($count / $this->getAantalPerPagina()),
			$gelezen->draad_id
		);
	}

	public function setHuidigePagina($pagina, $draad_id)
	{
		if (!is_int($pagina) || $pagina < 1) {
			$pagina = 1;
		} elseif ($draad_id !== 0 && $pagina > $this->getAantalPaginas($draad_id)) {
			$pagina = $this->getAantalPaginas($draad_id);
		}
		$this->pagina = $pagina;
	}

	/**
	 * @param ForumZoeken $forumZoeken
	 * @param $alleen_eerste_post
	 * @return ForumPost[]
	 */
	public function zoeken(ForumZoeken $forumZoeken, $alleen_eerste_post)
	{
		$results = $this->createQueryBuilder('fp')
			->addSelect('MATCH(fp.tekst) AGAINST (:query) AS score')
			->where(
				'fp.wacht_goedkeuring = false and fp.verwijderd = false and fp.laatst_gewijzigd >= :van and fp.laatst_gewijzigd <= :tot and MATCH(fp.tekst) AGAINST (:query) > 0'
			)
			->setParameter('query', $forumZoeken->zoekterm)
			->setParameter('van', $forumZoeken->van)
			->setParameter('tot', $forumZoeken->tot)
			->orderBy('score', 'DESC')
			->having('score > 0')
			->setMaxResults($forumZoeken->limit)
			->getQuery()
			->getResult();

		if ($alleen_eerste_post) {
			$out = [];
			foreach ($results as $result) {
				/** @var $post ForumPost */
				$post = $result[0];
				if (
					$this->getEerstePostVoorDraad($post->draad)->post_id == $post->post_id
				) {
					$out[] = $result;
				}
			}
			return $out;
		} else {
			return $results;
		}
	}

	public function getEerstePostVoorDraad(ForumDraad $draad)
	{
		return $this->findOneBy([
			'draad_id' => $draad->draad_id,
			'wacht_goedkeuring' => false,
			'verwijderd' => false,
		]);
	}

	/**
	 * @param array $criteria
	 * @param array|null $orderBy
	 * @return ForumPost|null
	 */
	public function findOneBy(array $criteria, array $orderBy = null)
	{
		$orderBy ??= ['datum_tijd' => 'ASC'];
		return parent::findOneBy($criteria, $orderBy);
	}

	public function getAantalForumPostsVoorLid($uid)
	{
		return $this->count([
			'uid' => $uid,
			'wacht_goedkeuring' => false,
			'verwijderd' => false,
		]);
	}

	public function getAantalWachtOpGoedkeuring()
	{
		if (!isset($this->aantal_wacht)) {
			$this->aantal_wacht = $this->count([
				'wacht_goedkeuring' => true,
				'verwijderd' => false,
			]);
		}
		return $this->aantal_wacht;
	}

	public function getPrullenbakVoorDraad(ForumDraad $draad)
	{
		return $this->findBy([
			'draad_id' => $draad->draad_id,
			'verwijderd' => true,
		]);
	}

	public function getForumPostsVoorDraad(ForumDraad $draad)
	{
		$qb = $this->createQueryBuilder('fp')
			->where('fp.draad_id = :draad_id and fp.verwijderd = false')
			->setParameter('draad_id', $draad->draad_id)
			->orderBy('fp.datum_tijd', 'ASC')
			->setMaxResults($this->getAantalPerPagina())
			->setFirstResult(($this->pagina - 1) * $this->getAantalPerPagina());

		if (!LoginService::mag(P_FORUM_MOD)) {
			$qb->andWhere('fp.wacht_goedkeuring = false');
		}

		$posts = $qb->getQuery()->getResult();
		if ($draad->eerste_post_plakkerig && $this->pagina !== 1) {
			$first_post = $this->getEerstePostVoorDraad($draad);
			array_unshift($posts, $first_post);
		}
		return $posts;
	}

	/**
	 * Laad de meest recente forumposts van een gebruiker.
	 * Check leesrechten van gebruiker.
	 *
	 * @param string $uid
	 * @param int $aantal
	 * @param boolean $draad_uniek
	 * @return ForumPost[]
	 */
	public function getRecenteForumPostsVanLid(
		$uid,
		$aantal,
		$draad_uniek = false
	) {
		$qb = $this->createQueryBuilder('fp')
			->where(
				'fp.uid = :uid and fp.wacht_goedkeuring = false and fp.verwijderd = false'
			)
			->setParameter('uid', $uid)
			->setMaxResults($aantal)
			->orderBy('fp.laatst_gewijzigd', 'DESC');

		if ($draad_uniek) {
			$qb->groupBy('fp.draad_id');
		}

		/** @var ForumPost[] $results */
		$results = $qb->getQuery()->getResult();
		$posts = [];
		$draden_ids = [];
		foreach ($results as $post) {
			if ($post->draad->magLezen()) {
				$posts[] = $post;
				$draden_ids[] = $post->draad_id;
			}
		}
		$count = count($draden_ids);
		if ($count > 0) {
			array_unshift($draden_ids, LoginService::getUid());
		}
		return $posts;
	}

	public function maakForumPost($draad, $tekst, $ip, $wacht_goedkeuring, $email)
	{
		$post = new ForumPost();
		$post->draad = $draad;
		$post->uid = LoginService::getUid();
		$post->tekst = $tekst;
		$post->datum_tijd = date_create_immutable();
		$post->laatst_gewijzigd = $post->datum_tijd;
		$post->bewerkt_tekst = null;
		$post->verwijderd = false;
		$post->auteur_ip = $ip;
		$post->wacht_goedkeuring = $wacht_goedkeuring;
		if ($wacht_goedkeuring) {
			$post->bewerkt_tekst =
				'[prive]email: [email]' . $email . '[/email][/prive]' . "\n";
		}
		$this->getEntityManager()->persist($post);
		$this->getEntityManager()->flush();
		return $post;
	}

	public function verwijderForumPostsVoorDraad(ForumDraad $draad)
	{
		$this->createQueryBuilder('fp')
			->update()
			->set('fp.verwijderd', $draad->verwijderd)
			->where('fp.draad_id = :id')
			->setParameter('id', $draad->draad_id)
			->getQuery()
			->execute();
	}

	public function offtopicForumPost(ForumPost $post)
	{
		$post->tekst = '[offtopic]' . $post->tekst . '[/offtopic]';
		$post->laatst_gewijzigd = date_create_immutable();
		$post->bewerkt_tekst .=
			'offtopic door [lid=' .
			LoginService::getUid() .
			'] [reldate]' .
			DateUtil::dateFormatIntl(
				$post->laatst_gewijzigd,
				DateUtil::DATETIME_FORMAT
			) .
			'[/reldate]' .
			"\n";
		try {
			$this->getEntityManager()->persist($post);
			$this->getEntityManager()->flush();
		} catch (ORMException $exception) {
			throw new CsrException('Offtopic mislukt', 500, $exception);
		}
	}

	public function citeerForumPost(ForumPost $post)
	{
		return CsrBB::filterCommentaar(CsrBB::filterPrive($post->tekst));
	}

	public function getStatsTotal()
	{
		$qb = $this->createQueryBuilder('fp');
		$qb->select([
			'UNIX_TIMESTAMP(DATE(fp.datum_tijd)) AS timestamp',
			'COUNT(fp.post_id) AS count',
		]);
		$qb->where('fp.datum_tijd > :terug');
		$qb->setParameter(
			'terug',
			date_create_immutable(
				InstellingUtil::instelling('forum', 'grafiek_stats_periode')
			)
		);
		$qb->groupBy('timestamp');
		$stats = $qb->getQuery()->getResult();

		reset($stats);

		$newStats = [];

		$curTime = date_create_immutable('@' . current($stats)['timestamp']);

		while (false !== ($current = next($stats))) {
			$next = date_create_immutable('@' . $current['timestamp']);
			$curTime = $curTime->add(new DateInterval('P1D'));
			while ($next > $curTime) {
				$newStats[] = ['timestamp' => $curTime->getTimestamp(), 'count' => 0];
				$curTime = $curTime->add(new DateInterval('P1D'));
			}
			$curTime = $next;
			$newStats[] = $current;
		}

		return $newStats;
	}

	public function getStatsVoorForumDeel(ForumDeel $deel)
	{
		$rsm = new ResultSetMapping();
		$rsm->addScalarResult('timestamp', 'timestamp', 'integer');
		$rsm->addScalarResult('count', 'count', 'integer');
		return $this->getEntityManager()
			->createNativeQuery(
				<<<'SQL'
select unix_timestamp(date(p.datum_tijd)) as timestamp, count(p.post_id) as count from forum_posts as p
right join forum_draden as d on p.draad_id = d.draad_id where d.forum_id = :forum_id and p.datum_tijd > :datum_tijd
group by timestamp
SQL
				,
				$rsm
			)
			->setParameters([
				'forum_id' => $deel->forum_id,
				'datum_tijd' => date_create_immutable(
					InstellingUtil::instelling('forum', 'grafiek_stats_periode')
				),
			])
			->getResult();
	}

	public function getStatsVoorDraad(ForumDraad $draad)
	{
		$qb = $this->createQueryBuilder('fp');
		$qb->select([
			'UNIX_TIMESTAMP(DATE(fp.datum_tijd)) AS timestamp',
			'COUNT(fp.post_id) AS count',
		]);
		$qb->where('fp.draad_id = :draad_id && fp.datum_tijd > :terug');
		$qb->setParameter('draad_id', $draad->draad_id);
		$qb->setParameter(
			'terug',
			$draad->laatst_gewijzigd->add(
				DateInterval::createFromDateString(
					InstellingUtil::instelling('forum', 'grafiek_draad_recent')
				)
			)
		);
		$qb->groupBy('timestamp');
		return $qb->getQuery()->getResult();
	}
}
