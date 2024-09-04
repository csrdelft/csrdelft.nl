<?php

namespace CsrDelft\repository\forum;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumZoeken;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\Paging;
use CsrDelft\service\security\LoginService;
use Doctrine\DBAL\Exception\SyntaxErrorException;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 * @method ForumDraad|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumDraad|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersistentCollection|ForumDraad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumDradenRepository extends AbstractRepository implements Paging
{
	/**
	 * Mogelijke markeringen voor belangrijke draadjes
	 * @var array
	 */
	public static $belangrijk_opties = [
		'Plaatje' => [
			'asterisk_orange' => 'Asterisk',
			'ruby' => 'Robijn',
			'rosette' => 'Rozet',
		],
		'Vlag' => [
			'flag_red' => 'Rood',
			'flag_orange' => 'Oranje',
			'flag_yellow' => 'Geel',
			'flag_green' => 'Groen',
			'flag_blue' => 'Blauw',
			'flag_purple' => 'Paars',
			'flag_pink' => 'Roze',
		],
	];
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'plakkerig DESC, laatst_gewijzigd DESC';
	/**
	 * Huidige pagina
	 * @var int
	 */
	private $pagina;
	/**
	 * Aantal draden per pagina
	 * Gebruik @see ForumDradenRepository::getAantalPerPagina()
	 * @var int|null
	 */
	private $per_pagina;
	/**
	 * Totaal aantal paginas per forumdeel
	 * @var int[]
	 */
	private $aantal_paginas;
	/**
	 * Aantal plakkerige draden
	 * @var int
	 */
	private $aantal_plakkerig;

	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ForumDraad::class);
		$this->pagina = 1;
		$this->aantal_paginas = [];
		$this->aantal_plakkerig = null;
	}

	/**
	 * @param $id
	 * @return ForumDraad
	 * @throws CsrGebruikerException
	 */
	public function get($id)
	{
		$draad = $this->find($id);
		if (!$draad) {
			throw new CsrGebruikerException('Forum-onderwerp bestaat niet!');
		}
		return $draad;
	}

	public function getAantalPerPagina()
	{
		if (!$this->per_pagina) {
			$this->per_pagina = (int) InstellingUtil::lid_instelling(
				'forum',
				'draden_per_pagina'
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

	public function setHuidigePagina($pagina, $forum_id)
	{
		if (!is_int($pagina) || $pagina < 1) {
			$pagina = 1;
		} elseif ($forum_id !== 0 && $pagina > $this->getAantalPaginas($forum_id)) {
			$pagina = $this->getAantalPaginas($forum_id);
		}
		$this->pagina = $pagina;
	}

	public function getAantalPaginas($forum_id = null)
	{
		if (!isset($forum_id)) {
			// recent en zoeken hebben onbeperkte paginas
			return $this->pagina + 1;
		}
		if (!array_key_exists($forum_id, $this->aantal_paginas)) {
			$qb = $this->createQueryBuilder('d');
			$qb->select('count(d.draad_id)');
			$qb->where(
				'd.forum_id = :forum_id and d.wacht_goedkeuring = false and d.verwijderd = false'
			);
			$qb->setParameter('forum_id', $forum_id);
			$this->filterLaatstGewijzigdExtern($qb);

			$aantal = $qb->getQuery()->getSingleScalarResult();

			$this->aantal_paginas[$forum_id] = (int) ceil(
				$aantal / $this->getAantalPerPagina()
			);
		}
		return max(1, $this->aantal_paginas[$forum_id]);
	}

	public function createQueryBuilder($alias, $indexBy = null)
	{
		return parent::createQueryBuilder($alias, $indexBy)
			->orderBy($alias . '.plakkerig', 'DESC')
			->addOrderBy($alias . '.laatst_gewijzigd', 'DESC');
	}

	public function createQueryBuilderWithoutOrder($alias, $indexBy = null)
	{
		return parent::createQueryBuilder($alias, $indexBy);
	}

	public function setLaatstePagina($forum_id)
	{
		$this->pagina = $this->getAantalPaginas($forum_id);
	}

	public function getPaginaVoorDraad(ForumDraad $draad)
	{
		if ($draad->plakkerig) {
			return 1;
		}
		if ($this->aantal_plakkerig === null) {
			$qb = $this->createQueryBuilder('d');
			$qb->select('count(d.draad_id)');
			$qb->where(
				'd.forum_id = :forum_id and d.plakkerig = true and d.wacht_goedkeuring = false and d.verwijderd = false'
			);
			$qb->setParameter('forum_id', $draad->forum_id);
			$this->aantal_plakkerig = $qb->getQuery()->getSingleScalarResult();
		}

		$qb = $this->createQueryBuilder('d');
		$qb->select('count(d.draad_id)');
		$qb->where(
			'd.forum_id = :forum_id and d.laatst_gewijzigd >= :laatst_gewijzigd and d.plakkerig = false and d.wacht_goedkeuring = false and d.verwijderd = false'
		);
		$qb->setParameter('forum_id', $draad->forum_id);
		$qb->setParameter('laatst_gewijzigd', $draad->laatst_gewijzigd);

		$count = $this->aantal_plakkerig + $qb->getQuery()->getSingleScalarResult();
		return (int) ceil($count / $this->getAantalPerPagina());
	}

	public function zoeken(ForumZoeken $forumZoeken)
	{
		$qb = $this->createQueryBuilder('draad');
		// Als er geen spatie in de zoekterm zit, doe dan keyword search met '<zoekterm>*'
		if (!str_contains($forumZoeken->zoekterm, ' ')) {
			$qb->addSelect(
				'MATCH(draad.titel) AGAINST (:query IN BOOLEAN MODE) AS score'
			);
		} else {
			$qb->addSelect('MATCH(draad.titel) AGAINST (:query) AS score');
		}

		$qb->setParameter('query', $forumZoeken->zoekterm);
		$qb->where(
			'draad.wacht_goedkeuring = false and draad.verwijderd = false and draad.laatst_gewijzigd >= :van and draad.laatst_gewijzigd <= :tot'
		);
		$qb->setParameter('van', $forumZoeken->van);
		$qb->setParameter('tot', $forumZoeken->tot);
		$this->filterLaatstGewijzigdExtern($qb, 'draad');
		$qb->orderBy('score', 'DESC');
		$qb->addOrderBy('draad.plakkerig', 'DESC');
		$qb->having('score > 0');
		$qb->setMaxResults($forumZoeken->limit);
		try {
			$results = $qb->getQuery()->getResult();
		} catch (SyntaxErrorException) {
			FlashUtil::setFlashWithContainerFacade(
				'Op deze term kan niet gezocht worden',
				-1
			);
			// Syntax error in de MATCH in BOOLEAN MODE
			return [];
		}
		return $results;
	}

	public function getPrullenbakVoorDeel(ForumDeel $deel)
	{
		return $this->findBy(
			['forum_id' => $deel->forum_id, 'verwijderd' => true],
			['plakkerig' => 'DESC', 'laatst_gewijzigd' => 'DESC']
		);
	}

	public function getBelangrijkeForumDradenVoorDeel(ForumDeel $deel)
	{
		$qb = $this->createQueryBuilder('d');
		$qb->where(
			'd.forum_id = :forum_id and d.wacht_goedkeuring = false and d.verwijderd = false and d.belangrijk = true'
		);
		$qb->setParameter('forum_id', $deel->forum_id);

		$this->filterLaatstGewijzigdExtern($qb);

		return $qb->getQuery()->getResult();
	}

	public function getForumDradenVoorDeel(ForumDeel $deel)
	{
		$qb = $this->createQueryBuilder('d');
		$qb->where(
			'(d.forum_id = :forum_id or d.gedeeld_met = :forum_id) and d.wacht_goedkeuring = false and d.verwijderd = false'
		);
		$qb->setParameter('forum_id', $deel->forum_id);

		$this->filterLaatstGewijzigdExtern($qb);

		$qb->setFirstResult(($this->pagina - 1) * $this->getAantalPerPagina());
		$qb->setMaxResults($this->getAantalPerPagina());

		$paginator = new Paginator($qb);

		return $paginator->getIterator();
	}

	/**
	 * @param array $ids
	 * @return array|ForumDraad[]
	 */
	public function getForumDradenById(array $ids)
	{
		$count = count($ids);
		if ($count < 1) {
			return [];
		}

		$draden = $this->createQueryBuilder('d')
			->where('d.draad_id in (:ids)')
			->setParameter('ids', $ids)
			->getQuery()
			->getResult();
		return ArrayUtil::group_by_distinct('draad_id', $draden);
	}

	public function maakForumDraad($deel, $titel, $wacht_goedkeuring)
	{
		$draad = new ForumDraad();
		$draad->deel = $deel;
		$draad->gedeeld_met_deel = null;
		$draad->uid = LoginService::getUid();
		$draad->titel = $titel;
		$draad->datum_tijd = date_create_immutable();
		$draad->laatst_gewijzigd = $draad->datum_tijd;
		$draad->laatste_post_id = null;
		$draad->laatste_wijziging_uid = null;
		$draad->gesloten = false;
		$draad->verwijderd = false;
		$draad->wacht_goedkeuring = $wacht_goedkeuring;
		$draad->plakkerig = false;
		$draad->belangrijk = null;
		$draad->eerste_post_plakkerig = false;
		$draad->pagina_per_post = false;
		$this->getEntityManager()->persist($draad);
		$this->getEntityManager()->flush();
		return $draad;
	}

	public function update(ForumDraad $draad)
	{
		try {
			$this->getEntityManager()->persist($draad);
			$this->getEntityManager()->flush();

			return 1;
		} catch (Exception) {
			return 0;
		}
	}

	public function filterLaatstGewijzigdExtern($qb, $alias = 'd')
	{
		if (!LoginService::mag(P_LOGGED_IN)) {
			$qb->andWhere(
				"({$alias}.gesloten = true and {$alias}.laatst_gewijzigd >= :laatst_gewijzigd_gesloten) or ({$alias}.gesloten = false and {$alias}.laatst_gewijzigd >= :laatst_gewijzigd_open)"
			);
			$qb->setParameter(
				'laatst_gewijzigd_gesloten',
				date_create_immutable(
					InstellingUtil::instelling('forum', 'externen_geentoegang_gesloten')
				)
			);
			$qb->setParameter(
				'laatst_gewijzigd_open',
				date_create_immutable(
					InstellingUtil::instelling('forum', 'externen_geentoegang_open')
				)
			);
		}
	}
}
