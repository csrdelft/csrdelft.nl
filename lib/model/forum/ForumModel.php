<?php

namespace CsrDelft\model\forum;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\forum\ForumCategorie;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\security\AccountModel;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\repository\forum\ForumDelenMeldingRepository;
use CsrDelft\repository\forum\ForumDelenRepository;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenMeldingRepository;
use CsrDelft\repository\forum\ForumDradenReagerenRepository;
use CsrDelft\repository\forum\ForumDradenVerbergenRepository;
use PDO;

/**
 * ForumModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class ForumModel extends CachedPersistenceModel {

	const ORM = ForumCategorie::class;

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'volgorde ASC';
	/**
	 * Store forum categorien array as a whole in memcache
	 * @var boolean
	 */
	protected $memcache_prefetch = true;
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
	 * @var ForumDradenModel
	 */
	private $forumDradenModel;
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
	 * @var ForumPostsModel
	 */
	private $forumPostsModel;

	public function __construct(
		ForumDelenRepository $forumDelenRepository,
		ForumDradenModel $forumDradenModel,
		ForumDradenGelezenRepository $forumDradenGelezenRepository,
		ForumDradenReagerenRepository $forumDradenReagerenRepository,
		ForumDradenVerbergenRepository $forumDradenVerbergenRepository,
		ForumDradenMeldingRepository $forumDradenMeldingModel,
		ForumPostsModel $forumPostsModel,
		ForumDelenMeldingRepository $forumDelenMeldingRepository
	) {
		parent::__construct();

		$this->forumDelenRepository = $forumDelenRepository;
		$this->forumDradenModel = $forumDradenModel;
		$this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
		$this->forumDradenReagerenRepository = $forumDradenReagerenRepository;
		$this->forumDradenVerbergenRepository = $forumDradenVerbergenRepository;
		$this->forumDradenMeldingModel = $forumDradenMeldingModel;
		$this->forumPostsModel = $forumPostsModel;
		$this->forumDelenMeldingRepository = $forumDelenMeldingRepository;
	}

	public function get($id) {
		$categorie = $this->retrieveByPrimaryKey(array($id));
		if (!$categorie) {
			throw new CsrGebruikerException('Forum-categorie bestaat niet!');
		}
		return $categorie;
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
			foreach ($this->prefetch() as $categorie) {
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
		$posts = $this->forumPostsModel->find('verwijderd = TRUE AND wacht_goedkeuring = TRUE');
		foreach ($posts as $post) {
			$this->forumPostsModel->delete($post);
		}

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
		$datetime = getDateTime(strtotime('-1 year'));
		$draden = $this->forumDradenModel->find('verwijderd = TRUE OR (gesloten = TRUE AND (laatst_gewijzigd IS NULL OR laatst_gewijzigd < ?))', array($datetime));
		foreach ($draden as $draad) {

			// Settings verwijderen
			$this->forumDradenMeldingModel->stopMeldingenVoorIedereen($draad);
			$this->forumDradenVerbergenRepository->toonDraadVoorIedereen($draad);
			$this->forumDradenGelezenRepository->verwijderDraadGelezen($draad);
			$this->forumDradenReagerenRepository->verwijderReagerenVoorDraad($draad);

			// Oude verwijderde posts definitief verwijderen
			$posts = $this->forumPostsModel->find('verwijderd = TRUE AND draad_id = ?', array($draad->draad_id));
			foreach ($posts as $post) {
				$this->forumPostsModel->delete($post);
			}
			if ($draad->verwijderd) {

				// Als het goed is zijn er nooit niet-verwijderde posts in een verwijderd draadje
				$this->forumDradenModel->delete($draad);
			}
		}
	}

}
