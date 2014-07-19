<?php

/**
 * ForumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ForumModel extends PersistenceModel {

	const orm = 'ForumCategorie';

	protected static $instance;

	/**
	 * Eager loading of ForumDeel[].
	 * 
	 * @return ForumCategorie[]
	 */
	public function getForum() {
		$delen = ForumDelenModel::instance()->getAlleForumDelenPerCategorie();
		$categorien = $this->find(null, array(), 'volgorde ASC');
		$result = array();
		foreach ($categorien as $cat) {
			if ($cat->magLezen()) {
				$result[] = $cat;
				if (array_key_exists($cat->categorie_id, $delen)) {
					$cat->setForumDelen($delen[$cat->categorie_id]);
					unset($delen[$cat->categorie_id]);
				} else {
					$cat->setForumDelen(array());
				}
			}
		}
		return $result;
	}

}

class ForumDelenModel extends PersistenceModel {

	const orm = 'ForumDeel';

	protected static $instance;

	public function getAlleForumDelenPerCategorie() {
		$delen = $this->find(null, array(), 'volgorde ASC');
		$result = array();
		foreach ($delen as $deel) {
			if ($deel->magLezen()) {
				$result[$deel->categorie_id][$deel->forum_id] = $deel;
			}
		}
		return $result;
	}

	public function getForumDelenVoorCategorie($cid) {
		return $this->find('categorie_id = ?', array($cid), 'volgorde ASC');
	}

	public function getForumDelenVoorLid($rss) {
		$delen = group_by_distinct('forum_id', $this->find());
		foreach ($delen as $forum_id => $deel) {
			if (!$deel->magLezen($rss)) {
				unset($delen[$forum_id]);
			}
		}
		return $delen;
	}

	public function bestaatForumDeel($id) {
		return $this->existsByPrimaryKey(array($id));
	}

	public function getForumDeel($id) {
		$deel = $this->retrieveByPrimaryKey(array($id));
		if (!$deel) {
			throw new Exception('Forum bestaat niet!');
		}
		return $deel;
	}

	public function maakForumDeel() {
		$deel = new ForumDeel();
		$deel->categorie_id = 0;
		$deel->titel = '';
		$deel->omschrijving = '';
		$deel->laatst_gewijzigd = null;
		$deel->laatste_post_id = null;
		$deel->laatste_lid_id = null;
		$deel->aantal_draden = 0;
		$deel->aantal_posts = 0;
		$deel->rechten_lezen = 'P_FORUM_READ';
		$deel->rechten_posten = 'P_FORUM_POST';
		$deel->rechten_modereren = 'P_FORUM_MOD';
		$deel->volgorde = 0;
		$deel->forum_id = $this->create($deel);
		return $deel;
	}

	public function verwijderForumDeel($id) {

		$this->deleteByPrimaryKey(array($id));
	}

	public function getForumDelenById(array $ids) {
		$count = count($ids);
		if ($count < 1) {
			return array();
		}
		$in = implode(', ', array_fill(0, $count, '?'));
		return group_by_distinct('forum_id', $this->find('forum_id IN (' . $in . ')', $ids));
	}

	public function getRecent($belangrijk = null) {
		$deel = new ForumDeel();
		if ($belangrijk === true) {
			$deel->titel = 'Belangrijk recent gewijzigd';
		} else {
			$deel->titel = 'Recent gewijzigd';
		}
		$deel->setForumDraden(ForumDradenModel::instance()->getRecenteForumDraden(null, $belangrijk));
		return $deel;
	}

	/**
	 * Laadt de posts die wachten op goedkeuring en de draadjes en forumdelen die erbij horen.
	 * Check modrechten van gebruiker.
	 * 
	 * @return array( ForumDraden[], ForumDelen[] )
	 */
	public function getWachtOpGoedkeuring() {
		$gevonden_posts = group_by('draad_id', ForumPostsModel::instance()->find('wacht_goedkeuring = TRUE AND verwijderd = FALSE'));
		$gevonden_draden = group_by_distinct('draad_id', ForumDradenModel::instance()->find('wacht_goedkeuring = TRUE AND verwijderd = FALSE'));
		$gevonden_draden += ForumDradenModel::instance()->getForumDradenById(array_keys($gevonden_posts)); // laad draden bij posts
		foreach ($gevonden_draden as $draad) { // laad posts bij draden
			if (array_key_exists($draad->draad_id, $gevonden_posts)) { // post is al gevonden
				$draad->setForumPosts($gevonden_posts[$draad->draad_id]);
			} else {
				$melding = 'Draad ' . $draad->draad_id . ' niet goedgekeurd, maar alle posts wel. Automatische actie: ';
				$draad->wacht_goedkeuring = false;
				if ($draad->aantal_posts === 0) {
					$draad->verwijderd = true;
					$melding .= 'verwijderd (bevat geen berichten)';
					setMelding($melding, 2);
					DebugLogModel::instance()->log(get_called_class(), 'getWachtOpGoedkeuring', array(), $melding);
				} else {
					$melding .= 'goedgekeurd (bevat ' . $draad->aantal_posts . ' berichten)';
					setMelding($melding, 2);
					DebugLogModel::instance()->log(get_called_class(), 'getWachtOpGoedkeuring', array(), $melding);
				}
				ForumDradenModel::instance()->update($draad);
			}
		}
// check permissies op delen
		$delen_ids = array_keys(group_by_distinct('forum_id', $gevonden_draden, false));
		$gevonden_delen = group_by_distinct('forum_id', ForumDelenModel::instance()->getForumDelenById($delen_ids));
		foreach ($gevonden_delen as $forum_id => $deel) {
			if (!$deel->magModereren()) {
				foreach ($gevonden_draden as $draad_id => $draad) {
					if ($draad->forum_id === $deel->forum_id) {
						unset($gevonden_draden[$draad_id]);
					}
				}
				unset($gevonden_delen[$forum_id]);
			}
		}
		return array($gevonden_draden, $gevonden_delen);
	}

	/**
	 * Zoek op titel van draadjes en tekst van posts en laad forumdelen die erbij horen.
	 * Check leesrechten van gebruiker.
	 * 
	 * @return array( ForumDraden[], ForumDelen[] )
	 */
	public function zoeken($query) {
		$gevonden_posts = group_by('draad_id', ForumPostsModel::instance()->zoeken($query)); // zoek op tekst in posts
		$gevonden_draden = group_by_distinct('draad_id', ForumDradenModel::instance()->zoeken($query)); // zoek op titel in draden
		$gevonden_draden += ForumDradenModel::instance()->getForumDradenById(array_keys($gevonden_posts)); // laad draden bij posts
		foreach ($gevonden_draden as $draad) { // laad posts bij draden
			if (property_exists($draad, 'score')) { // gevonden op draad titel
				$draad->score = (float) $draad->score;
			} else { // gevonden op post tekst
				$draad->score = (float) 0;
			}
			if (array_key_exists($draad->draad_id, $gevonden_posts)) { // posts al gevonden
				$draad->setForumPosts($gevonden_posts[$draad->draad_id]);
				foreach ($draad->getForumPosts() as $post) {
					$draad->score += (float) $post->score;
				}
			} else { // laad eerste post
				$array_first_post = ForumPostsModel::instance()->find('draad_id = ?', array($draad->draad_id), 'post_id ASC', null, 1)->fetchAll();
				$draad->setForumPosts($array_first_post);
			}
		}
// check permissies op delen
		$delen_ids = array_keys(group_by_distinct('forum_id', $gevonden_draden, false));
		$gevonden_delen = group_by_distinct('forum_id', ForumDelenModel::instance()->getForumDelenById($delen_ids));
		foreach ($gevonden_delen as $forum_id => $deel) {
			if (!$deel->magLezen()) {
				foreach ($gevonden_draden as $draad_id => $draad) {
					if ($draad->forum_id === $deel->forum_id) {
						unset($gevonden_draden[$draad_id]);
					}
				}
				unset($gevonden_delen[$forum_id]);
			}
		}
		usort($gevonden_draden, array($this, 'sorteren'));
		return array($gevonden_draden, $gevonden_delen);
	}

	function sorteren($a, $b) {
		if ($a->score < $b->score) {
			return 1;
		} else {
			return -1;
		}
	}

}

class ForumDradenGelezenModel extends PersistenceModel {

	const orm = 'ForumDraadGelezen';

	protected static $instance;

	public function getWanneerGelezenDoorLid(ForumDraad $draad) {
		return $this->retrieveByPrimaryKey(array($draad->draad_id, LoginLid::instance()->getUid()));
	}

	public function setWanneerGelezenDoorLid(ForumDraad $draad) {
		$gelezen = $this->getWanneerGelezenDoorLid($draad);
		if (!$gelezen) {
			$gelezen = new ForumDraadGelezen();
			$gelezen->draad_id = $draad->draad_id;
			$gelezen->lid_id = LoginLid::instance()->getUid();
			$gelezen->datum_tijd = date('Y-m-d H:i:s');
			$this->create($gelezen);
		} else {
			$gelezen->datum_tijd = date('Y-m-d H:i:s');
			$this->update($gelezen);
		}
	}

}

class ForumDradenVerbergenModel extends PersistenceModel {

	const orm = 'ForumDraadVerbergen';

	protected static $instance;

	public function getVerbergenVoorLid(ForumDraad $draad) {
		return $this->existsByPrimaryKey(array($draad->draad_id, LoginLid::instance()->getUid()));
	}

	public function setVerbergenVoorLid(ForumDraad $draad, $verbergen = true) {
		$verborgen = $this->getVerbergenVoorLid($draad);
		if ($verbergen) {
			if (!$verborgen) {
				$verborgen = new ForumDraadVerbergen();
				$verborgen->draad_id = $draad->draad_id;
				$verborgen->lid_id = LoginLid::instance()->getUid();
				$this->create($verborgen);
			}
		} else {
			if ($verborgen) {
				$this->deleteByPrimaryKey(array($draad->draad_id, LoginLid::instance()->getUid()));
			}
		}
	}

}

class ForumDradenModel extends PersistenceModel implements Paging {

	const orm = 'ForumDraad';

	protected static $instance;
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

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = LidInstellingen::get('forum', 'draden_per_pagina');
		$this->aantal_paginas = array();
		$this->aantal_plakkerig = null;
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
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

	public function getAantalPaginas($forum_id) {
		if ($forum_id === 0) { // recent en zoeken hebben onbeperkte paginas
			return $this->pagina + 1;
		}
		if (!array_key_exists($forum_id, $this->aantal_paginas)) {
			$this->aantal_paginas[$forum_id] = ceil($this->count('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($forum_id)) / $this->per_pagina);
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
		return ceil($count / $this->per_pagina);
	}

	public function hertellenVoorDeel(ForumDeel $deel) {
		$orm = self::orm;
		$result = Database::sqlSelect(array('SUM(aantal_posts)'), $orm::getTableName(), 'forum_id = ?', array($deel->forum_id));
		$deel->aantal_posts = (int) $result->fetchColumn();
		$deel->aantal_draden = $this->count('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($deel->forum_id));
		ForumDelenModel::instance()->update($deel);
	}

	public function zoeken($query) {
		$this->per_pagina = (int) LidInstellingen::get('forum', 'zoekresultaten');
		$orm = self::orm;
		$fields = $orm::getFields();
		$fields[] = 'MATCH(titel) AGAINST (? IN NATURAL LANGUAGE MODE) AS score';
		$results = Database::sqlSelect($fields, $orm::getTableName(), 'wacht_goedkeuring = FALSE AND verwijderd = FALSE HAVING score > 0', array($query), 'score DESC', null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		$results->setFetchMode(PDO::FETCH_CLASS, $orm, array($cast = true));
		return $results;
	}

	/**
	 * Eager loading of ForumDraadGelezen[].
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $orderby
	 * @param int $limit max amount of results
	 * @param int $start results from index
	 * @return PersistentEntity[]
	 */
	public function find($criteria = null, array $criteria_params = array(), $orderby = null, $groupby = null, $limit = null, $start = 0) {
		$orm = self::orm;
		$from = $orm::getTableName() . ' AS d LEFT JOIN forum_draden_gelezen AS g ON d.draad_id = g.draad_id AND g.lid_id = ?';
		$fields = $orm::getFields();
		foreach ($fields as $i => $field) {
			$fields[$i] = 'd.' . $field;
		}
		$fields[] = 'g.datum_tijd AS wanneer_gelezen';
		$params = array_merge(array(LoginLid::instance()->getUid()), $criteria_params);
		$result = Database::sqlSelect($fields, $from, $criteria, $params, $orderby, $groupby, $limit, $start);
		$result->setFetchMode(PDO::FETCH_CLASS, $orm, array($cast = true));
		return $result;
	}

	public function getForumDradenVoorDeel($forum_id) {
		return $this->find('d.forum_id = ? AND d.wacht_goedkeuring = FALSE AND d.verwijderd = FALSE', array($forum_id), 'd.plakkerig DESC, d.laatst_gewijzigd DESC', null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina)->fetchAll();
	}

	/**
	 * Laad recente (niet) (belangrijke) draadjes.
	 * Eager loading van laatste ForumPost
	 * Check leesrechten van gebruiker.
	 * RSS: use token & return delen.
	 * 
	 * @param int $aantal
	 * @param boolean $belangrijk null voor maakt niet uit
	 * @param boolean $rss
	 * @return ForumDraad[] of voor rss: array( ForumDraad[], ForumDeel[] )
	 */
	public function getRecenteForumDraden($aantal = null, $belangrijk = null, $rss = false) {
		if (!is_int($aantal)) {
			$aantal = (int) LidInstellingen::get('forum', 'draden_per_pagina');
			$pagina = $this->pagina;
		} else {
			$pagina = 1;
		}
		$delen = ForumDelenModel::instance()->getForumDelenVoorLid($rss);
		$params = array_keys($delen);
		$count = count($delen);
		if ($count < 1) {
			if ($rss) {
				return array(array(), array());
			}
			return array();
		}
		$forum_ids = implode(', ', array_fill(0, $count, '?'));
		$verbergen = ForumDradenVerbergenModel::instance()->find('lid_id = ?', array(LoginLid::instance()->getUid()));
		$draden_ids = array_keys(group_by_distinct('draad_id', $verbergen));
		$count = count($draden_ids);
		if ($count > 0) {
			$params = array_merge($params, $draden_ids);
			$verborgen = ' AND d.draad_id NOT IN (' . implode(', ', array_fill(0, $count, '?')) . ')';
		} else {
			$verborgen = '';
		}
		if ($belangrijk !== null) {
			$params[] = $belangrijk;
			$belangrijk = ' AND d.belangrijk = ?';
		} else {
			$belangrijk = '';
		}
		$draden = $this->find('d.forum_id IN (' . $forum_ids . ')' . $verborgen . ' AND d.wacht_goedkeuring = FALSE AND d.verwijderd = FALSE' . $belangrijk, $params, 'd.laatst_gewijzigd DESC', null, $aantal, ($pagina - 1) * $aantal)->fetchAll();
		$posts_ids = array_keys(group_by_distinct('laatste_post_id', $draden, false));
		$posts = ForumPostsModel::instance()->getForumPostsById($posts_ids, ' AND wacht_goedkeuring = FALSE AND verwijderd = FALSE');
		foreach ($draden as $i => $draad) {
			if (array_key_exists($draad->laatste_post_id, $posts)) {
				$draad->setForumPosts(array($posts[$draad->laatste_post_id]));
			} else {
				unset($draden[$i]);
			}
		}
		if ($rss) {
			return array($draden, $delen);
		}
		return $draden;
	}

	public function getRssForumDradenEnDelen() {
		return $this->getRecenteForumDraden(null, null, true);
	}

	public function getForumDraad($id) {
		$draad = $this->retrieveByPrimaryKey(array($id));
		if (!$draad) {
			throw new Exception('Forum-onderwerp bestaat niet!');
		}
		return $draad;
	}

	public function getForumDradenById(array $ids, $where = '', array $where_params = array()) {
		$count = count($ids);
		if ($count < 1) {
			return array();
		}
		$in = implode(', ', array_fill(0, $count, '?'));
		return group_by_distinct('draad_id', $this->find('d.draad_id IN (' . $in . ')' . $where, array_merge($ids, $where_params)));
	}

	public function maakForumDraad($forum_id, $titel, $wacht_goedkeuring) {
		$draad = new ForumDraad();
		$draad->forum_id = (int) $forum_id;
		$draad->lid_id = LoginLid::instance()->getUid();
		$draad->titel = $titel;
		$draad->datum_tijd = date('Y-m-d H:i:s');
		$draad->laatst_gewijzigd = null;
		$draad->laatste_post_id = null;
		$draad->laatste_lid_id = null;
		$draad->aantal_posts = 0;
		$draad->gesloten = false;
		$draad->verwijderd = false;
		$draad->wacht_goedkeuring = $wacht_goedkeuring;
		$draad->plakkerig = false;
		$draad->belangrijk = false;
		$draad->eerste_post_plakkerig = false;
		$draad->draad_id = (int) ForumDradenModel::instance()->create($draad);
		return $draad;
	}

	public function wijzigForumDraad(ForumDraad $draad, $property, $value) {
		if (!property_exists($draad, $property)) {
			throw new Exception('Property undefined: ' . $property);
		}
		$draad->$property = $value;
		$rowcount = $this->update($draad);
		if ($rowcount !== 1) {
			throw new Exception('Wijzigen van ' . $property . ' mislukt');
		}
	}

}

class ForumPostsModel extends PersistenceModel implements Paging {

	const orm = 'ForumPost';

	protected static $instance;
	/**
	 * Huidige pagina
	 * @var int
	 */
	private $pagina;
	/**
	 * Aantal posts per pagina
	 * @var int
	 */
	private $per_pagina;
	/**
	 * Totaal aantal paginas per forumdraad
	 * @var int[]
	 */
	private $aantal_paginas;
	/**
	 * Aantal posts die wachten op goedkeuring per forumdeel
	 * @var int[]
	 */
	private $aantal_goedkeuring;

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = LidInstellingen::get('forum', 'posts_per_pagina');
		$this->aantal_paginas = array();
		$this->aantal_goedkeuring = array();
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function getHuidigePagina() {
		return $this->pagina;
	}

	public function setHuidigePagina($pagina, $draad_id) {
		if (!is_int($pagina) OR $pagina < 1) {
			$pagina = 1;
		} elseif ($draad_id !== 0 AND $pagina > $this->getAantalPaginas($draad_id)) {
			$pagina = $this->getAantalPaginas($draad_id);
		}
		$this->pagina = $pagina;
	}

	public function setLaatstePagina($draad_id) {
		$this->pagina = $this->getAantalPaginas($draad_id);
	}

	public function getAantalPaginas($draad_id) {
		if (!array_key_exists($draad_id, $this->aantal_paginas)) {
			$this->aantal_paginas[$draad_id] = ceil($this->count('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad_id)) / $this->per_pagina);
		}
		return max(1, $this->aantal_paginas[$draad_id]);
	}

	public function getPaginaVoorPost(ForumPost $post) {
		$count = $this->count('draad_id = ? AND post_id <= ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($post->draad_id, $post->post_id));
		return ceil($count / $this->per_pagina);
	}

	public function setPaginaVoorLaatstGelezen(ForumDraadGelezen $gelezen) {
		$count = 1 + $this->count('draad_id = ? AND datum_tijd < ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($gelezen->draad_id, $gelezen->datum_tijd));
		$this->setHuidigePagina((int) ceil($count / $this->per_pagina), $gelezen->draad_id);
	}

	public function hertellenVoorDraadEnDeel(ForumDraad $draad, ForumDeel $deel) {
		$draad->aantal_posts = $this->count('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id));
		if (!$draad->verwijderd AND $draad->aantal_posts < 1) {
			$draad->verwijderd = true;
			setMelding('Draad ' . $draad->draad_id . ' bevat geen berichten. Automatische actie: verwijderd', 2);
		}
		ForumDradenModel::instance()->update($draad);
		ForumDradenModel::instance()->hertellenVoorDeel($deel);
	}

	public function zoeken($query) {
		$this->per_pagina = (int) LidInstellingen::get('forum', 'zoekresultaten');
		$orm = self::orm;
		$fields = $orm::getFields();
		$fields[] = 'MATCH(tekst) AGAINST (? IN NATURAL LANGUAGE MODE) AS score';
		$results = Database::sqlSelect($fields, $orm::getTableName(), 'wacht_goedkeuring = FALSE AND verwijderd = FALSE HAVING score > 0', array($query), 'score DESC', null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		$results->setFetchMode(PDO::FETCH_CLASS, $orm, array($cast = true));
		return $results;
	}

	public function getForumPostsVoorDraad(ForumDraad $draad) {
		$posts = $this->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), 'post_id ASC', null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina)->fetchAll();
		if ($draad->eerste_post_plakkerig AND $this->pagina !== 1) {
			$array_first_post = $this->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), 'post_id ASC', null, 1)->fetch();
			array_unshift($posts, $array_first_post);
		}
// 2008-filter
		if (LidInstellingen::get('forum', 'filter2008') == 'ja') {
			foreach ($posts as $post) {
				if (startsWith($post->lid_id, '08')) {
					$post->gefilterd = 'Bericht van 2008';
				}
			}
		}
		return $posts;
	}

	public function getAantalForumPostsVoorLid($uid) {
		return $this->count('lid_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($uid));
	}

	public function getAantalWachtOpGoedkeuring($forum_id = 0) {
		if ($forum_id === 0) {
			if (!array_key_exists(0, $this->aantal_goedkeuring)) {
				$this->aantal_goedkeuring[0] = ForumPostsModel::instance()->count('wacht_goedkeuring = TRUE AND verwijderd = FALSE');
			}
			return $this->aantal_goedkeuring[0];
		} else {
			if (!array_key_exists($forum_id, $this->aantal_goedkeuring)) {
// laad draad ids van posts die wachten op goedkeuring
				$gevonden_posts = group_by('draad_id', ForumPostsModel::instance()->find('wacht_goedkeuring = TRUE AND verwijderd = FALSE'));
// laad draden bij posts die alleen in dit deel zitten
				$gevonden_draden = ForumDradenModel::instance()->getForumDradenById(array_keys($gevonden_posts), ' AND forum_id = ? AND verwijderd = FALSE', array($forum_id));
				$this->aantal_goedkeuring[$forum_id] = count($gevonden_draden);
			}
			return $this->aantal_goedkeuring[$forum_id];
		}
	}

	/**
	 * Laad de meest recente forumposts van een gebruiker.
	 * Check leesrechten van gebruiker.
	 * 
	 * @param string $uid
	 * @param int $aantal
	 * @param int $draad_uniek
	 * @return array( ForumPost[], ForumDraad[] )
	 */
	public function getRecenteForumPostsVanLid($uid, $aantal, $draad_uniek = false) {
		$where = 'lid_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE';
		if ($draad_uniek) {
			$orm = self::orm;
			$where = 'post_id = (
	SELECT MAX(post_id)
	FROM ' . $orm::getTableName() . ' AS zelfde_draad
	WHERE ' . $orm::getTableName() . '.draad_id = zelfde_draad.draad_id
	AND ' . $where . '
)';
		}
		$posts = $this->find($where, array($uid), 'post_id DESC', null, $aantal)->fetchAll();
		$draden_ids = array_keys(group_by_distinct('draad_id', $posts, false));
		$draden = ForumDradenModel::instance()->getForumDradenById($draden_ids);
		$delen_ids = array_keys(group_by_distinct('forum_id', $draden, false));
		$delen = ForumDelenModel::instance()->getForumDelenById($delen_ids);
		foreach ($delen as $forum_id => $deel) {
			if (!$deel->magLezen()) {
				foreach ($draden as $draad_id => $draad) {
					if ($draad->forum_id === $forum_id) {
						foreach ($posts as $i => $post) {
							if ($post->draad_id === $draad_id) {
								unset($posts[$i]);
							}
						}
						unset($draden[$draad_id]);
					}
				}
				unset($delen[$forum_id]);
			}
		}
		return array($posts, $draden);
	}

	public function getForumPost($id) {
		$post = $this->retrieveByPrimaryKey(array($id));
		if (!$post) {
			throw new Exception('Forum-reactie bestaat niet!');
		}
		return $post;
	}

	public function getForumPostsById(array $ids, $where = '', array $where_params = array()) {
		$count = count($ids);
		if ($count < 1) {
			return array();
		}
		$in = implode(', ', array_fill(0, $count, '?'));
		return group_by_distinct('post_id', $this->find('post_id IN (' . $in . ')' . $where, array_merge($ids, $where_params)));
	}

	public function maakForumPost($draad_id, $tekst, $ip, $wacht_goedkeuring, $email) {
		$post = new ForumPost();
		$post->draad_id = (int) $draad_id;
		$post->lid_id = LoginLid::instance()->getUid();
		$post->tekst = $tekst;
		$post->datum_tijd = date('Y-m-d H:i:s');
		$post->laatst_bewerkt = null;
		$post->bewerkt_tekst = null;
		$post->verwijderd = false;
		$post->auteur_ip = $ip;
		$post->wacht_goedkeuring = $wacht_goedkeuring;
		if ($wacht_goedkeuring) {
			$post->bewerkt_tekst = '[prive]email: [email]' . $email . '[/email][/prive]' . "\n";
		}
		$post->post_id = (int) ForumPostsModel::instance()->create($post);
		return $post;
	}

	public function verwijderForumPost(ForumPost $post, ForumDraad $draad, ForumDeel $deel) {
		if ($post->verwijderd) {
			throw new Exception('Al verwijderd!');
		}
		$post->verwijderd = true;
		$rowcount = $this->update($post);
		if ($rowcount !== 1) {
			throw new Exception('Verwijderen mislukt');
		}
		if ($draad->laatste_post_id === $post->post_id) {
			$draad->laatste_post_id = null;
			$draad->laatste_lid_id = null;
			$draad->laatst_gewijzigd = null;
		}
		if ($deel->laatste_post_id === $post->post_id) {
			$deel->laatste_post_id = null;
			$deel->laatste_lid_id = null;
			$deel->laatst_gewijzigd = null;
		}
		$this->hertellenVoorDraadEnDeel($draad, $deel);
	}

	public function verwijderForumPostsVoorDraad(ForumDraad $draad, ForumDeel $deel) {
		$orm = self::orm;
		Database::sqlUpdate($orm::getTableName(), array('verwijderd' => $draad->verwijderd), 'draad_id = :id', array(':id' => $draad->draad_id));
		$deel->laatste_post_id = null;
		$deel->laatste_lid_id = null;
		$deel->laatst_gewijzigd = null;
		$this->hertellenVoorDraadEnDeel($draad, $deel);
	}

	public function bewerkForumPost(ForumPost $post, $nieuwe_tekst, $reden = '') {
		$post->tekst = $nieuwe_tekst;
		$post->laatst_bewerkt = getDateTime();
		$bewerkt = 'bewerkt door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate]';
		if ($reden !== '') {
			$bewerkt .= ': ' . $reden;
		}
		$bewerkt .= "\n";
		$post->bewerkt_tekst .= $bewerkt;
		return $this->update($post);
	}

	public function verplaatsForumPost(ForumPost $post, $nieuw_draad_id) {
		$post->draad_id = $nieuw_draad_id;
		$post->laatst_bewerkt = getDateTime();
		$post->bewerkt_tekst .= 'verplaatst door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate]' . "\n";
		$rowcount = $this->update($post);
		if ($rowcount !== 1) {
			throw new Exception('Verplaatsen mislukt');
		}
	}

	public function afsplitsenForumPost(ForumPost $post, $nieuw_draad_naam, $forum_id) {
		$draad = ForumDradenModel::instance()->maakForumDraad($forum_id, $nieuw_draad_naam, false);
		$post->draad_id = $draad->draad_id;
		$post->laatst_bewerkt = getDateTime();
		$post->bewerkt_tekst .= 'verplaatst door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate]' . "\n";
		$rowcount = $this->update($post);
		if ($rowcount !== 1) {
			throw new Exception('Afsplitsen mislukt');
		}
		return $draad;
	}

	public function offtopicForumPost(ForumPost $post) {
		$post->tekst = '[offtopic]' . $post->tekst . '[/offtopic]';
		$post->laatst_bewerkt = getDateTime();
		$post->bewerkt_tekst .= 'offtopic door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate]' . "\n";
		$rowcount = $this->update($post);
		if ($rowcount !== 1) {
			throw new Exception('Offtopic mislukt');
		}
	}

	public function goedkeurenForumPost(ForumPost $post, ForumDraad $draad, ForumDeel $deel) {
		$laatst = $post->datum_tijd;
		if ($post->wacht_goedkeuring) {
			$post->wacht_goedkeuring = false;
			$post->laatst_bewerkt = getDateTime();
			$post->bewerkt_tekst .= '[prive=P_FORUM_MOD]Goedgekeurd door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate][/prive]' . "\n";
			$rowcount = $this->update($post);
			if ($rowcount !== 1) {
				throw new Exception('Goedkeuren mislukt');
			}
			$laatst = $post->laatst_bewerkt;
		}
		$draad->aantal_posts++;
		$draad->laatst_gewijzigd = $laatst;
		$draad->laatste_post_id = $post->post_id;
		$draad->laatste_lid_id = $post->lid_id;
		if ($draad->wacht_goedkeuring) {
			$draad->wacht_goedkeuring = false;
		}
		$rowcount = ForumDradenModel::instance()->update($draad);
		if ($rowcount !== 1) {
			throw new Exception('Goedkeuren mislukt');
		}
		$deel->aantal_posts++;
		$deel->aantal_draden++;
		$deel->laatst_gewijzigd = $laatst;
		$deel->laatste_post_id = $post->post_id;
		$deel->laatste_lid_id = $post->lid_id;
		$rowcount = ForumDelenModel::instance()->update($deel);
		if ($rowcount !== 1) {
			throw new Exception('Goedkeuren mislukt');
		}
	}

	public function citeerForumPost(ForumPost $post) {
		$tekst = CsrUbb::filterPrive($post->tekst);
		return '[citaat=' . $post->lid_id . ']' . $tekst . '[/citaat]';
	}

}
