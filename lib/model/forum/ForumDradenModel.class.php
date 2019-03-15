<?php

namespace CsrDelft\model\forum;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\Paging;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Persistence\Database;
use PDO;

/**
 * ForumDradenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class ForumDradenModel extends CachedPersistenceModel implements Paging {

	const ORM = ForumDraad::class;

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
	 * @var int
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
	/**
	 * Mogelijke markeringen voor belangrijke draadjes
	 * @var array
	 */
	public static $belangrijk_opties = array(
		'Plaatje' => array(
			'asterisk_orange' => 'Asterisk',
			'ruby' => 'Robijn',
			'rosette' => 'Rozet'
		),
		'Vlag' => array(
			'flag_red' => 'Rood',
			'flag_orange' => 'Oranje',
			'flag_yellow' => 'Geel',
			'flag_green' => 'Groen',
			'flag_blue' => 'Blauw',
			'flag_purple' => 'Paars',
			'flag_pink' => 'Roze'
		)
	);

	/**
	 * @var ForumDradenGelezenModel
	 */
	private $forumDradenGelezenModel;

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
	 * @var ForumPostsModel
	 */
	private $forumPostsModel;

	/**
	 * @param $id
	 * @return ForumDraad
	 * @throws CsrGebruikerException
	 */
	public static function get($id) {
		$draad = static::instance()->retrieveByPrimaryKey(array($id));
		if (!$draad) {
			throw new CsrGebruikerException('Forum-onderwerp bestaat niet!');
		}
		return $draad;
	}

	protected function __construct(
		ForumDradenGelezenModel $forumDradenGelezenModel,
		ForumDradenReagerenModel $forumDradenReagerenModel,
		ForumDradenVerbergenModel $forumDradenVerbergenModel,
		ForumDradenMeldingModel $forumDradenMeldingModel,
		ForumPostsModel $forumPostsModel
	) {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = (int)LidInstellingenModel::get('forum', 'draden_per_pagina');
		$this->aantal_paginas = array();
		$this->aantal_plakkerig = null;

		$this->forumDradenGelezenModel = $forumDradenGelezenModel;
		$this->forumDradenReagerenModel = $forumDradenReagerenModel;
		$this->forumDradenVerbergenModel = $forumDradenVerbergenModel;
		$this->forumDradenMeldingModel = $forumDradenMeldingModel;
		$this->forumPostsModel = $forumPostsModel;
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function setAantalPerPagina($aantal) {
		$this->per_pagina = (int)$aantal;
	}

	public function getHuidigePagina() {
		return $this->pagina;
	}

	public function setHuidigePagina($pagina, $forum_id) {
		if (!is_int($pagina) OR $pagina < 1) {
			$pagina = 1;
		} elseif ($forum_id !== 0 AND $pagina > $this->getAantalPaginas($forum_id)) {
			$pagina = $this->getAantalPaginas($forum_id);
		}
		$this->pagina = $pagina;
	}

	public function setLaatstePagina($forum_id) {
		$this->pagina = $this->getAantalPaginas($forum_id);
	}

	public function getAantalPaginas($forum_id = null) {
		if (!isset($forum_id)) { // recent en zoeken hebben onbeperkte paginas
			return $this->pagina + 1;
		}
		if (!array_key_exists($forum_id, $this->aantal_paginas)) {
			$where = 'forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE';
			$where_params = array($forum_id);
			if (!LoginModel::mag('P_LOGGED_IN')) {
				$where .= ' AND (gesloten = FALSE OR laatst_gewijzigd >= ?)';
				$where_params[] = getDateTime(strtotime(InstellingenModel::get('forum', 'externen_geentoegang_gesloten')));
			}
			$this->aantal_paginas[$forum_id] = (int)ceil($this->count($where, $where_params) / $this->per_pagina);
		}
		return max(1, $this->aantal_paginas[$forum_id]);
	}

	public function getPaginaVoorDraad(ForumDraad $draad) {
		if ($draad->plakkerig) {
			return 1;
		}
		if ($this->aantal_plakkerig === null) {
			$this->aantal_plakkerig = $this->count('forum_id = ? AND plakkerig = TRUE AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->forum_id));
		}
		$count = $this->aantal_plakkerig + $this->count('forum_id = ? AND laatst_gewijzigd >= ? AND plakkerig = FALSE AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->forum_id, $draad->laatst_gewijzigd));
		return (int)ceil($count / $this->per_pagina);
	}

	public function zoeken($query, $datumsoort, $ouder, $jaar, $limit) {
		$this->per_pagina = (int)$limit;
		$attributes = ['*', 'MATCH(titel) AGAINST (? IN NATURAL LANGUAGE MODE) AS score'];
		$where_params = [$query];
		$where = 'wacht_goedkeuring = FALSE AND verwijderd = FALSE';
		if (!LoginModel::mag('P_LOGGED_IN')) {
			$where .= ' AND (gesloten = FALSE OR laatst_gewijzigd >= ?)';
			$where_params[] = getDateTime(strtotime(InstellingenModel::get('forum', 'externen_geentoegang_gesloten')));
		}
		$order = 'score DESC, plakkerig DESC';
		if (in_array($datumsoort, array('datum_tijd', 'laatst_gewijzigd'))) {
			$order .= ', ' . $datumsoort . ' DESC';
			if (is_int($jaar)) {
				$where .= ' AND ' . $datumsoort;
				if ($ouder === 'ouder') {
					$where .= ' < ?';
				} else {
					$where .= ' > ?';
				}
				$where_params[] = getDateTime(strtotime('-' . $jaar . ' year'));
			}
		}
		$where .= ' HAVING score > 0';
		$results = Database::instance()->sqlSelect($attributes, $this->getTableName(), $where, $where_params, null, $order, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		$results->setFetchMode(PDO::FETCH_CLASS, static::ORM, array($cast = true));
		return $results;
	}

	public function getPrullenbakVoorDeel(ForumDeel $deel) {
		return $this->prefetch('forum_id = ? AND verwijderd = TRUE', array($deel->forum_id));
	}

	public function getBelangrijkeForumDradenVoorDeel(ForumDeel $deel) {
		$where = 'forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE AND belangrijk = TRUE';
		$where_params = array($deel->forum_id);
		if (!LoginModel::mag('P_LOGGED_IN')) {
			$where .= ' AND (gesloten = FALSE OR laatst_gewijzigd >= ?)';
			$where_params[] = getDateTime(strtotime(InstellingenModel::get('forum', 'externen_geentoegang_gesloten')));
		}
		return $this->prefetch($where, $where_params);
	}

	public function getForumDradenVoorDeel(ForumDeel $deel) {
		$where = '(forum_id = ? OR gedeeld_met = ?) AND wacht_goedkeuring = FALSE AND verwijderd = FALSE';
		$where_params = array($deel->forum_id, $deel->forum_id);
		if (!LoginModel::mag('P_LOGGED_IN')) {
			$where .= ' AND (gesloten = FALSE OR laatst_gewijzigd >= ?)';
			$where_params[] = getDateTime(strtotime(InstellingenModel::get('forum', 'externen_geentoegang_gesloten')));
		}
		return $this->prefetch($where, $where_params, null, null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
	}

	/**
	 * Laad recente (niet) (belangrijke) draadjes.
	 * Eager loading van laatste ForumPost
	 * Check leesrechten van gebruiker.
	 * RSS: use token & return delen.
	 *
	 * @param int|null $aantal
	 * @param boolean|null $belangrijk
	 * @param boolean $rss
	 * @param int $offset
	 * @param boolean $getLatestPosts
	 * @return ForumDraad[]
	 */
	public function getRecenteForumDraden($aantal, $belangrijk, $rss = false, $offset = 0, $getLatestPosts = false) {
		if (!is_int($aantal)) {
			$aantal = (int)LidInstellingenModel::get('forum', 'draden_per_pagina');
			$pagina = $this->pagina;
			$offset = ($pagina - 1) * $aantal;
		}
		$delenById = ForumDelenModel::instance()->getForumDelenVoorLid($rss);
		$count = count($delenById);
		if ($count < 1) {
			return array();
		}
		$forum_ids_stub = implode(', ', array_fill(0, $count, '?'));
		$forum_ids = array_keys($delenById);
		$where_params = array_merge($forum_ids, $forum_ids);
		$verbergen = $this->forumDradenVerbergenModel->prefetch('uid = ?', array(LoginModel::getUid()));
		$draden_ids = array_keys(group_by_distinct('draad_id', $verbergen));
		$count = count($draden_ids);
		if ($count > 0) {
			$verborgen = ' AND draad_id NOT IN (' . implode(', ', array_fill(0, $count, '?')) . ')';
			$where_params = array_merge($where_params, $draden_ids);
		} else {
			$verborgen = '';
		}
		$where = '(forum_id IN (' . $forum_ids_stub . ') OR gedeeld_met IN (' . $forum_ids_stub . '))' . $verborgen . ' AND wacht_goedkeuring = FALSE AND verwijderd = FALSE';
		if (is_bool($belangrijk)) {
			if ($belangrijk) {
				$where .= ' AND belangrijk IS NOT NULL';
			} else {
				if (!isset($pagina) || LidInstellingenModel::get('forum', 'belangrijkBijRecent') === 'nee') {
					$where .= ' AND belangrijk IS NULL';
				}
			}
		}
		if (!LoginModel::mag('P_LOGGED_IN')) {
			$where .= ' AND (gesloten = FALSE OR laatst_gewijzigd >= ?)';
			$where_params[] = getDateTime(strtotime(InstellingenModel::get('forum', 'externen_geentoegang_gesloten')));
		}
		$dradenById = group_by_distinct('draad_id', $this->find($where, $where_params, null, 'laatst_gewijzigd DESC', $aantal, $offset));
		$count = count($dradenById);
		if ($count > 0) {
			$draden_ids = array_keys($dradenById);
			array_unshift($draden_ids, LoginModel::getUid());
			$this->forumDradenGelezenModel->prefetch('uid = ? AND draad_id IN (' . implode(', ', array_fill(0, $count, '?')) . ')', $draden_ids);
			if ($getLatestPosts) {
				$latest_post_ids = array_map(function ($draad) {
					return $draad->laatste_post_id;
				}, array_values($dradenById));
				$this->forumPostsModel->prefetch('wacht_goedkeuring = FALSE AND verwijderd = FALSE AND post_id IN (' . implode(', ', array_fill(0, $count, '?')) . ')', $latest_post_ids);
			}
		}
		return $dradenById;
	}

	public function getForumDradenById(array $ids, $where = null, array $where_params = array()) {
		$count = count($ids);
		if ($count < 1) {
			return array();
		}
		$in = implode(', ', array_fill(0, $count, '?'));
		return group_by_distinct('draad_id', $this->find('draad_id IN (' . $in . ')' . $where, array_merge($ids, $where_params)));
	}

	public function maakForumDraad($forum_id, $titel, $wacht_goedkeuring) {
		$draad = new ForumDraad();
		$draad->forum_id = (int)$forum_id;
		$draad->gedeeld_met = null;
		$draad->uid = LoginModel::getUid();
		$draad->titel = $titel;
		$draad->datum_tijd = getDateTime();
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
		$draad->draad_id = (int)$this->create($draad);
		return $draad;
	}

	public function wijzigForumDraad(ForumDraad $draad, $property, $value) {
		if (!property_exists($draad, $property)) {
			throw new CsrException('Property undefined: ' . $property);
		}
		$draad->$property = $value;
		$rowCount = $this->update($draad);
		if ($rowCount !== 1) {
			throw new CsrException('Wijzigen van ' . $property . ' mislukt');
		}
		if ($property === 'belangrijk') {
			$this->forumDradenVerbergenModel->toonDraadVoorIedereen($draad);
		} elseif ($property === 'gesloten') {
			$this->forumDradenMeldingModel->stopMeldingenVoorIedereen($draad);
		} elseif ($property === 'verwijderd') {
			$this->forumDradenMeldingModel->stopMeldingenVoorIedereen($draad);
			$this->forumDradenVerbergenModel->toonDraadVoorIedereen($draad);
			$this->forumDradenGelezenModel->verwijderDraadGelezen($draad);
			$this->forumDradenReagerenModel->verwijderReagerenVoorDraad($draad);
			$this->forumPostsModel->verwijderForumPostsVoorDraad($draad);
		}
	}

	public function resetLastPost(ForumDraad $draad) {
		// reset last post
		$last_post = $this->forumPostsModel->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), null, 'laatst_gewijzigd DESC', 1)->fetch();
		if ($last_post) {
			$draad->laatste_post_id = $last_post->post_id;
			$draad->laatste_wijziging_uid = $last_post->uid;
			$draad->laatst_gewijzigd = $last_post->laatst_gewijzigd;
		} else {
			$draad->laatste_post_id = null;
			$draad->laatste_wijziging_uid = null;
			$draad->laatst_gewijzigd = null;
			$draad->verwijderd = true;
			setMelding('Enige bericht in draad verwijderd: draad ook verwijderd', 2);
		}
		$this->update($draad);
	}

}
