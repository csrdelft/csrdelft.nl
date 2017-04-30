<?php

use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Persistence\Database;

require_once 'forum/model/Paging.interface.php';
require_once 'forum/model/ForumDelenModel.class.php';
require_once 'forum/model/ForumDradenReagerenModel.class.php';
require_once 'forum/model/ForumDradenGelezenModel.class.php';
require_once 'forum/model/ForumDradenVerbergenModel.class.php';
require_once 'forum/model/ForumDradenVolgenModel.class.php';
require_once 'forum/model/ForumDradenModel.class.php';
require_once 'forum/model/ForumPostsModel.class.php';

require_once 'forum/model/entity/ForumCategorie.class.php';

/**
 * ForumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ForumModel extends CachedPersistenceModel {

	const ORM = ForumCategorie::class;
	const DIR = 'forum/';

	protected static $instance;
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

	public static function get($id) {
		$categorie = static::instance()->retrieveByPrimaryKey(array($id));
		if (!$categorie) {
			throw new Exception('Forum-categorie bestaat niet!');
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
			$delenByCategorieId = group_by('categorie_id', ForumDelenModel::instance()->getForumDelenVoorLid());
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
		ForumDradenReagerenModel::instance()->verwijderLegeConcepten();

		// Niet-goedgekeurde posts verwijderen
		$posts = ForumPostsModel::instance()->find('verwijderd = TRUE AND wacht_goedkeuring = TRUE');
		foreach ($posts as $post) {
			ForumPostsModel::instance()->delete($post);
		}

		// Voor alle ex-leden settings opschonen
		$uids = Database::instance()->sqlSelect(array('uid'), ProfielModel::instance()->getTableName(), 'status IN (?,?,?,?)', array(LidStatus::Commissie, LidStatus::Nobody, LidStatus::Exlid, LidStatus::Overleden));
		$uids->setFetchMode(PDO::FETCH_COLUMN, 0);
		foreach ($uids as $uid) {
			if (AccountModel::isValidUid($uid)) {
				ForumDradenGelezenModel::instance()->verwijderDraadGelezenVoorLid($uid);
				ForumDradenVerbergenModel::instance()->toonAllesVoorLid($uid);
				ForumDradenVolgenModel::instance()->volgNietsVoorLid($uid);
				ForumDradenReagerenModel::instance()->verwijderReagerenVoorLid($uid);
			}
		}

		// Settings voor oude topics opschonen en oude/verwijderde topics en posts definitief verwijderen
		$datetime = getDateTime(strtotime('-1 year'));
		$draden = ForumDradenModel::instance()->find('verwijderd = TRUE OR (gesloten = TRUE AND (laatst_gewijzigd IS NULL OR laatst_gewijzigd < ?))', array($datetime));
		foreach ($draden as $draad) {

			// Settings verwijderen
			ForumDradenVolgenModel::instance()->stopVolgenVoorIedereen($draad);
			ForumDradenVerbergenModel::instance()->toonDraadVoorIedereen($draad);
			ForumDradenGelezenModel::instance()->verwijderDraadGelezen($draad);
			ForumDradenReagerenModel::instance()->verwijderReagerenVoorDraad($draad);

			// Oude verwijderde posts definitief verwijderen
			$posts = ForumPostsModel::instance()->find('verwijderd = TRUE AND draad_id = ?', array($draad->draad_id));
			foreach ($posts as $post) {
				ForumPostsModel::instance()->delete($post);
			}
			if ($draad->verwijderd) {

				// Als het goed is zijn er nooit niet-verwijderde posts in een verwijderd draadje
				ForumDradenModel::instance()->delete($draad);
			}
		}
	}

}
