<?php

namespace CsrDelft\model\forum;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\forum\ForumCategorie;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\security\AccountModel;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
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
	 * @var ForumDelenModel
	 */
	private $forumDelenModel;
	/**
	 * @var ForumDradenModel
	 */
	private $forumDradenModel;
	/**
	 * @var ForumDradenGelezenRepository
	 */
	private $forumDradenGelezenRepository;
	/**
	 * @var ForumDradenReagerenModel
	 */
	private $forumDradenReagerenModel;
	/**
	 * @var ForumDradenVerbergenModel
	 */
	private $forumDradenVerbergenModel;
	/**
	 * @var ForumDradenMeldingModel
	 */
	private $forumDradenMeldingModel;
	/**
	 * @var ForumDradenMeldingModel
	 */
	private $forumDelenMeldingModel;
	/**
	 * @var ForumPostsModel
	 */
	private $forumPostsModel;

	public function __construct(
		ForumDelenModel $forumDelenModel,
		ForumDradenModel $forumDradenModel,
		ForumDradenGelezenRepository $forumDradenGelezenRepository,
		ForumDradenReagerenModel $forumDradenReagerenModel,
		ForumDradenVerbergenModel $forumDradenVerbergenModel,
		ForumDradenMeldingModel $forumDradenMeldingModel,
		ForumPostsModel $forumPostsModel,
		ForumDelenMeldingModel $forumDelenMeldingModel
	) {
		parent::__construct();

		$this->forumDelenModel = $forumDelenModel;
		$this->forumDradenModel = $forumDradenModel;
		$this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
		$this->forumDradenReagerenModel = $forumDradenReagerenModel;
		$this->forumDradenVerbergenModel = $forumDradenVerbergenModel;
		$this->forumDradenMeldingModel = $forumDradenMeldingModel;
		$this->forumPostsModel = $forumPostsModel;
		$this->forumDelenMeldingModel = $forumDelenMeldingModel;
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
			$delenByCategorieId = group_by('categorie_id', $this->forumDelenModel->getForumDelenVoorLid());
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
		$this->forumDradenReagerenModel->verwijderLegeConcepten();

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
				$this->forumDradenVerbergenModel->toonAllesVoorLid($uid);
				$this->forumDradenMeldingModel->stopAlleMeldingenVoorLid($uid);
				$this->forumDelenMeldingModel->stopAlleMeldingenVoorLid($uid);
				$this->forumDradenReagerenModel->verwijderReagerenVoorLid($uid);
			}
		}

		// Settings voor oude topics opschonen en oude/verwijderde topics en posts definitief verwijderen
		$datetime = getDateTime(strtotime('-1 year'));
		$draden = $this->forumDradenModel->find('verwijderd = TRUE OR (gesloten = TRUE AND (laatst_gewijzigd IS NULL OR laatst_gewijzigd < ?))', array($datetime));
		foreach ($draden as $draad) {

			// Settings verwijderen
			$this->forumDradenMeldingModel->stopMeldingenVoorIedereen($draad);
			$this->forumDradenVerbergenModel->toonDraadVoorIedereen($draad);
			$this->forumDradenGelezenRepository->verwijderDraadGelezen($draad);
			$this->forumDradenReagerenModel->verwijderReagerenVoorDraad($draad);

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
