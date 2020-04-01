<?php

namespace CsrDelft\repository\forum;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\forum\ForumCategorie;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\security\AccountModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;
use PDO;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @method ForumCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumCategorieRepository extends AbstractRepository {
	/**
	 * Lazy loading
	 * @var array
	 */
	private $indeling;
	/**
	 * @var ForumDelenRepository
	 */
	private $forumDelenRepository;
	/**
	 * @var ForumDradenRepository
	 */
	private $forumDradenRepository;
	/**
	 * @var ForumDradenGelezenRepository
	 */
	private $forumDradenGelezenRepository;
	/**
	 * @var ForumDradenReagerenRepository
	 */
	private $forumDradenReagerenRepository;
	/**
	 * @var ForumDradenVerbergenRepository
	 */
	private $forumDradenVerbergenRepository;
	/**
	 * @var ForumDradenMeldingRepository
	 */
	private $forumDradenMeldingModel;
	/**
	 * @var ForumDelenMeldingRepository
	 */
	private $forumDelenMeldingRepository;
	/**
	 * @var ForumPostsRepository
	 */
	private $forumPostsRepository;

	public function __construct(
		ManagerRegistry $managerRegistry,
		ForumDelenRepository $forumDelenRepository,
		ForumDradenRepository $forumDradenRepository,
		ForumDradenGelezenRepository $forumDradenGelezenRepository,
		ForumDradenReagerenRepository $forumDradenReagerenRepository,
		ForumDradenVerbergenRepository $forumDradenVerbergenRepository,
		ForumDradenMeldingRepository $forumDradenMeldingModel,
		ForumPostsRepository $forumPostsRepository,
		ForumDelenMeldingRepository $forumDelenMeldingRepository
	) {
		parent::__construct($managerRegistry, ForumCategorie::class);

		$this->forumDelenRepository = $forumDelenRepository;
		$this->forumDradenRepository = $forumDradenRepository;
		$this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
		$this->forumDradenReagerenRepository = $forumDradenReagerenRepository;
		$this->forumDradenVerbergenRepository = $forumDradenVerbergenRepository;
		$this->forumDradenMeldingModel = $forumDradenMeldingModel;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->forumDelenMeldingRepository = $forumDelenMeldingRepository;
	}

	public function get($id) {
		$categorie = $this->find($id);
		if (!$categorie) {
			throw new CsrGebruikerException('Forum-categorie bestaat niet!');
		}
		return $categorie;
	}

	public function findAll() {
		return $this->findBy([], ['volgorde' => 'ASC']);
	}

	/**
	 * Eager loading of ForumDeel[].
	 *
	 * @return ForumCategorie[]
	 */
	public function getForumIndelingVoorLid() {
		if (!isset($this->indeling)) {
			$delenByCategorieId = group_by('categorie_id', $this->forumDelenRepository->getForumDelenVoorLid());
			$this->indeling = array();
			foreach ($this->findAll() as $categorie) {
				/** @var ForumCategorie $categorie */
				if ($categorie->magLezen()) {
					$this->indeling[] = $categorie;
					if (isset($delenByCategorieId[$categorie->categorie_id])) {
						$categorie->setForumDelen($delenByCategorieId[$categorie->categorie_id]);
					} else {
						$categorie->setForumDelen(array());
					}
				}
			}
		}
		return $this->indeling;
	}

	public function opschonen() {
		// Oude lege concepten verwijderen
		$this->forumDradenReagerenRepository->verwijderLegeConcepten();

		// Niet-goedgekeurde posts verwijderen

		$this->forumPostsRepository->createQueryBuilder('fp')
			->delete()
			->where('fp.verwijderd = true and fp.wacht_goedkeuring = true')
			->getQuery()->execute();

		// Voor alle ex-leden settings opschonen
		$uids = Database::instance()->sqlSelect(array('uid'), 'profielen', 'status IN (?,?,?,?)', array(LidStatus::Commissie, LidStatus::Nobody, LidStatus::Exlid, LidStatus::Overleden));
		$uids->setFetchMode(PDO::FETCH_COLUMN, 0);
		foreach ($uids as $uid) {
			if (AccountModel::isValidUid($uid)) {
				$this->forumDradenGelezenRepository->verwijderDraadGelezenVoorLid($uid);
				$this->forumDradenVerbergenRepository->toonAllesVoorLid($uid);
				$this->forumDradenMeldingModel->stopAlleMeldingenVoorLid($uid);
				$this->forumDelenMeldingRepository->stopAlleMeldingenVoorLid($uid);
				$this->forumDradenReagerenRepository->verwijderReagerenVoorLid($uid);
			}
		}

		// Settings voor oude topics opschonen en oude/verwijderde topics en posts definitief verwijderen
		/** @var ForumDraad[] $draden */
		$draden = $this->forumDradenRepository->createQueryBuilder('fd')
			->where('fd.verwijderd = true or (fd.gesloten = true and (fd.laatst_gewijzigd is null or fd.laatst_gewijzigd < :laatst_gewijzigd))')
			->setParameter('laatst_gewijzigd', date_create_immutable('-1 year'))
			->getQuery()->getResult();
		foreach ($draden as $draad) {

			// Settings verwijderen
			$this->forumDradenMeldingModel->stopMeldingenVoorIedereen($draad);
			$this->forumDradenVerbergenRepository->toonDraadVoorIedereen($draad);
			$this->forumDradenGelezenRepository->verwijderDraadGelezen($draad);
			$this->forumDradenReagerenRepository->verwijderReagerenVoorDraad($draad);

			// Oude verwijderde posts definitief verwijderen
			$this->forumPostsRepository->createQueryBuilder('fp')
				->delete()
				->where('fp.verwijderd = true and fp.draad_id = :draad_id')
				->setParameter('draad_id', $draad->draad_id)
				->getQuery()->execute();

			if ($draad->verwijderd) {
				// Als het goed is zijn er nooit niet-verwijderde posts in een verwijderd draadje
				$this->getEntityManager()->remove($draad);
				$this->getEntityManager()->flush();
			}
		}
	}

}
