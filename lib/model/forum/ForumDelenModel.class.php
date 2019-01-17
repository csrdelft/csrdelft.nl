<?php

namespace CsrDelft\model\forum;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\forum\ForumCategorie;
use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Entity\PersistentEntity;

/**
 * ForumDelenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class ForumDelenModel extends CachedPersistenceModel {

	const ORM = ForumDeel::class;
	/**
	 * @var ForumDradenModel
	 */
	private $forumDradenModel;
	/**
	 * @var ForumPostsModel
	 */
	private $forumPostsModel;

	protected function __construct(ForumDradenModel $forumDradenModel, ForumPostsModel $forumPostsModel) {
		parent::__construct();

		$this->forumDradenModel = $forumDradenModel;
		$this->forumPostsModel = $forumPostsModel;
	}

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'volgorde ASC';
	/**
	 * Store forum delen array as a whole in memcache
	 * @var boolean
	 */
	protected $memcache_prefetch = true;

	/**
	 * @param $id
	 * @return ForumDeel
	 * @throws CsrGebruikerException
	 */
	public static function get($id) {
		/** @var ForumDeel $deel */
		$deel = static::instance()->retrieveByPrimaryKey(array($id));
		if (!$deel) {
			throw new CsrGebruikerException('Forum bestaat niet!');
		}
		return $deel;
	}

	/**
	 * @param PersistentEntity|ForumDeel $entity
	 * @return int
	 */
	public function create(PersistentEntity $entity) {
		$entity->forum_id = (int)parent::create($entity);
		return $entity->forum_id;
	}

	public function nieuwForumDeel() {
		$deel = new ForumDeel();
		$deel->categorie_id = 0;
		$deel->titel = 'Nieuw deelforum';
		$deel->omschrijving = '';
		$deel->rechten_lezen = 'P_FORUM_READ';
		$deel->rechten_posten = 'P_FORUM_POST';
		$deel->rechten_modereren = 'P_FORUM_MOD';
		$deel->volgorde = 0;
		return $deel;
	}

	public function bestaatForumDeel($id) {
		return $this->existsByPrimaryKey(array($id));
	}

	public function verwijderForumDeel($id) {
		ForumDelenMeldingModel::instance()->stopMeldingenVoorIedereen($id);
		$rowCount = $this->deleteByPrimaryKey(array($id));
		if ($rowCount !== 1) {
			throw new CsrException('Deelforum verwijderen mislukt');
		}
	}

	public function getForumDelenVoorCategorie(ForumCategorie $categorie) {
		return $this->prefetch('categorie_id = ?', array($categorie->categorie_id));
	}

	public function getForumDelenVoorLid($rss = false) {
		/** @var ForumDeel[] $delen */
		$delen = group_by_distinct('forum_id', $this->prefetch());
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
	public function getForumDelenOptiesOmTeDelen(ForumDeel $deel) {
		if (strpos($deel->rechten_posten, 'verticale:') !== false) {
			$query = '%verticale:%';
			$orderby = 'titel ASC';
		} elseif (strpos($deel->rechten_posten, 'lidjaar:') !== false) {
			$query = '%lidjaar:%';
			$orderby = 'titel DESC';
		} else {
			return array();
		}
		return $this->prefetch('rechten_posten != ? AND rechten_posten LIKE ?', array($deel->rechten_posten, $query), null, $orderby);
	}

	public function getRecent($belangrijk = null) {
		$deel = new ForumDeel();
		if ($belangrijk) {
			$deel->titel = 'Belangrijk recent gewijzigd';
		} else {
			$deel->titel = 'Recent gewijzigd';
		}
		$deel->setForumDraden($this->forumDradenModel->getRecenteForumDraden(null, $belangrijk));
		return $deel;
	}

	/**
	 * Laadt de posts die wachten op goedkeuring en de draadjes en forumdelen die erbij horen.
	 * Check modrechten van gebruiker.
	 *
	 * @return ForumDraad[]
	 */
	public function getWachtOpGoedkeuring() {
		$postsByDraadId = group_by('draad_id', $this->forumPostsModel->find('wacht_goedkeuring = TRUE AND verwijderd = FALSE'));
		$dradenById = group_by_distinct('draad_id', $this->forumDradenModel->find('wacht_goedkeuring = TRUE AND verwijderd = FALSE'));
		$dradenById += $this->forumDradenModel->getForumDradenById(array_keys($postsByDraadId)); // laad draden bij posts
		foreach ($dradenById as $draad) { // laad posts bij draden
			if (array_key_exists($draad->draad_id, $postsByDraadId)) { // post is al gevonden
				$draad->setForumPosts($postsByDraadId[$draad->draad_id]);
			} else {
				$melding = 'Draad ' . $draad->draad_id . ' niet goedgekeurd, maar alle posts wel. Automatische actie: ';
				$draad->wacht_goedkeuring = false;
				if (count($draad->getPosts()) === 0) {
					$draad->verwijderd = true;
					$melding .= 'verwijderd (bevat geen berichten)';
					setMelding($melding, 2);
				} else {
					$melding .= 'goedgekeurd';
					setMelding($melding, 2);
				}
				$this->forumDradenModel->update($draad);
			}
		}
		// check permissies
		foreach ($dradenById as $draad_id => $draad) {
			if (!$draad->magModereren()) {
				unset($dradenById[$draad_id]);
			}
		}
		if (empty($dradenById) AND $this->forumPostsModel->getAantalWachtOpGoedkeuring() > 0) {
			setMelding('U heeft onvoldoende rechten om de berichten goed te keuren', 0);
		}
		return $dradenById;
	}

	/**
	 * Zoek op titel van draadjes en tekst van posts en laad forumdelen die erbij horen.
	 * Check leesrechten van gebruiker.
	 *
	 * @return ForumDraad[]
	 */
	public function zoeken($query, $titel, $datum, $ouder, $jaar, $limit) {
		$gevonden_draden = group_by_distinct('draad_id', $this->forumDradenModel->zoeken($query, $datum, $ouder, $jaar, $limit)); // zoek op titel in draden
		if ($titel === true) {
			$gevonden_posts = array();
		} else {
			$gevonden_posts = group_by('draad_id', $this->forumPostsModel->zoeken($query, $datum, $ouder, $jaar, $limit)); // zoek op tekst in posts
			$gevonden_draden += $this->forumDradenModel->getForumDradenById(array_keys($gevonden_posts)); // laad draden bij posts
			// laad posts bij draden
			foreach ($gevonden_draden as $draad) {
				if (property_exists($draad, 'score')) { // gevonden op draad titel
					$draad->score = (float)50;
				} else { // gevonden op post tekst
					$draad->score = (float)0;
				}
				if (array_key_exists($draad->draad_id, $gevonden_posts)) { // posts al gevonden
					$draad->setForumPosts($gevonden_posts[$draad->draad_id]);
					foreach ($draad->getForumPosts() as $post) {
						$draad->score += (float)$post->score;
					}
				} else { // laad eerste post
					$array_first_post = $this->forumPostsModel->prefetch('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), null, null, 1);
					$draad->setForumPosts($array_first_post);
				}
			}
		}
		// check permissies
		foreach ($gevonden_draden as $draad_id => $draad) {
			if (!$draad->magLezen()) {
				unset($gevonden_draden[$draad_id]);
			}
		}
		if ($titel !== true) {
			usort($gevonden_draden, array($this, 'sorteren'));
		}
		return $gevonden_draden;
	}

	function sorteren($a, $b) {
		if ($a->score < $b->score) {
			return 1;
		} else {
			return -1;
		}
	}

}
