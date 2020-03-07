<?php

namespace CsrDelft\model\forum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\forum\ForumCategorie;
use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumZoeken;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\repository\forum\ForumDelenMeldingRepository;

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

	public function __construct(ForumDradenModel $forumDradenModel, ForumPostsModel $forumPostsModel) {
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
	public function get($id) {
		/** @var ForumDeel $deel */
		$deel = $this->retrieveByPrimaryKey(array($id));
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
		$deel->rechten_lezen = P_FORUM_READ;
		$deel->rechten_posten = P_FORUM_POST;
		$deel->rechten_modereren = P_FORUM_MOD;
		$deel->volgorde = 0;
		return $deel;
	}

	public function bestaatForumDeel($id) {
		return $this->existsByPrimaryKey(array($id));
	}

	public function verwijderForumDeel($id) {
		ContainerFacade::getContainer()->get(ForumDelenMeldingRepository::class)->stopMeldingenVoorIedereen($id);
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
	 * @param ForumZoeken $forumZoeken
	 * @param $query
	 * @param $titel
	 * @param $datum
	 * @param $ouder
	 * @param $jaar
	 * @param $per_pagina
	 * @param $offset
	 * @return ForumDraad[]
	 */
	public function zoeken(ForumZoeken $forumZoeken) {
		$zoek_in = $forumZoeken->zoek_in;

		$gevonden_draden = [];
		$gevonden_posts = [];

		if (in_array('titel', $zoek_in)) {
			$gevonden_draden = group_by_distinct('draad_id', $this->forumDradenModel->zoeken($forumZoeken));
		}

		if (in_array('alle_berichten', $zoek_in)) {
			$gevonden_posts += group_by('draad_id', $this->forumPostsModel->zoeken($forumZoeken, false));
		}

		if (in_array('eerste_bericht', $zoek_in)) {
			$gevonden_posts += group_by('draad_id', $this->forumPostsModel->zoeken($forumZoeken, true));
		}

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
				$draad->laatst_gewijzigd = $this->laatstGewijzigd($gevonden_posts[$draad->draad_id]);
				foreach ($draad->getForumPosts() as $post) {
					$draad->score += (float)$post->score;
				}
			} else { // laad eerste post
				$array_first_post = $this->forumPostsModel->getEerstePostVoorDraad($draad);
				$draad->setForumPosts([$array_first_post]);
			}
		}
		// check permissies
		foreach ($gevonden_draden as $draad_id => $draad) {
			if (!$draad->magLezen()) {
				unset($gevonden_draden[$draad_id]);
			}
		}
		usort($gevonden_draden, $this->sorteerFunctie($forumZoeken->sorteer_op));

		if ($forumZoeken->sorteer_volgorde == 'asc') {
			$gevonden_draden = array_reverse($gevonden_draden);
		}
		return $gevonden_draden;
	}

	function sorteerFunctie($sorteerOp) {
		switch ($sorteerOp) {
			case 'aangemaakt_op': return function ($a, $b) {
					return ($a->datum_tijd < $b->datum_tijd) ? 1 : -1;
				};
			case 'laatste_bericht': return function ($a, $b) {
					return ($a->laatst_gewijzigd < $b->laatst_gewijzigd) ? 1 : -1;
				};
			case 'relevantie': return function ($a, $b) {
					return ($a->score < $b->score) ? 1 : -1;
				};
			default: throw new CsrGebruikerException('Onbekende sorteermethode');
		}
	}

	function laatstGewijzigd($draden) {
		return max(array_map(function ($draad) { return $draad->laatst_gewijzigd; }, $draden));
	}
}
