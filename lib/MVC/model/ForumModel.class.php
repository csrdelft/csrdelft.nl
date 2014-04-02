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
		$categorien = $this->find(null, array(), 'volgorde');
		foreach ($categorien as $i => $cat) {
			if (!$cat->magLezen()) {
				unset($categorien[$i]);
			} else {
				if (array_key_exists($cat->categorie_id, $delen)) {
					$cat->setForumDelen($delen[$cat->categorie_id]);
					unset($delen[$cat->categorie_id]);
				} else {
					$cat->setForumDelen(array());
				}
			}
		}
		return $categorien;
	}

	public function zoeken($query) {

		$queryparts = explode(' ', $query);
		foreach ($queryparts as $i => $part) {
			$queryparts[$i] = '%' . $part . '%';
		}
		$count = count($queryparts);
		if ($count < 1) {
			return array();
		}

		// titel draadje
		$like = implode(' OR ', array_fill(0, $count, 'd.titel LIKE ?'));
		$gevonden_draden = array_group_by('forum_id', ForumDradenModel::instance()->find($like, $queryparts));

		var_dump($gevonden_draden);

		$eerste_posts = array();
		foreach ($gevonden_draden as $draad) {
			$eerste_posts[$draad->draad_id] = ForumPostsModel::instance()->find('draad_id = ?', array($draad->draad_id), 'post_id DESC', 1);
		}

		// tekst posts
		$like = implode(' OR ', array_fill(0, $count, 'tekst LIKE ?'));
		$gevonden_posts = array_group_by('draad_id', ForumPostsModel::instance()->find($like, $queryparts));
		$draden_erbij = array_group_by('forum_id', ForumDradenModel::instance()->getForumDradenById(array_keys($gevonden_posts)));

		// resultaten samenvoegen
		$postsByDraad = $eerste_posts + $gevonden_posts;
		$dradenByDeel = $gevonden_draden + $draden_erbij;
		$delenById = array_key_property('forum_id', ForumDelenModel::instance()->getForumDelenById(array_keys($dradenByDeel)));

		// filteren met rechten
		foreach ($delenById as $forum_id => $deel) {
			if ($deel->magLezen()) {
				$deel->setForumDraden($dradenByDeel[$deel->forum_id]);
			} else {
				unset($delenById[$forum_id]);
			}
		}
		foreach ($dradenByDeel as $forum_id => $dradenByDeel) {
			if (array_key_exists($forum_id, $delenById)) {
				foreach ($dradenByDeel as $draad) {
					$draad->setForumPosts($postsByDraad[$draad->draad_id]);
				}
			} else {
				unset($dradenByDeel[$forum_id]);
			}
		}
		return $delenById;
	}

}

class ForumDelenModel extends PersistenceModel {

	const orm = 'ForumDeel';

	protected static $instance;

	public function getAlleForumDelenPerCategorie() {
		$delen = $this->find(null, array(), 'volgorde');
		foreach ($delen as $i => $deel) {
			if (!$deel->magLezen()) {
				unset($delen[$i]);
			}
		}
		return array_group_by('categorie_id', $delen);
	}

	public function getForumDelenVoorCategorie($cid) {
		return $this->find('categorie_id = ?', array($cid), 'volgorde');
	}

	public function bestaatForumDeel($id) {
		return $this->existsByPrimaryKey(array($id));
	}

	public function getForumDeel($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function getForumDelenById(array $ids) {
		$count = count($ids);
		if ($count < 1) {
			return array();
		}
		$in = implode(', ', array_fill(0, $count, '?'));
		return array_key_property('forum_id', $this->find('forum_id IN (' . $in . ')', $ids));
	}

	public function getRecent() {
		$deel = new ForumDeel();
		$deel->titel = 'Recent';
		$deel->setForumDraden(ForumDradenModel::instance()->getRecenteForumDraden(LidInstellingen::get('forum', 'zoekresultaten')));
		return $deel;
	}

	/**
	 * Laadt de posts die wachten op goedkeuring en de draadjes en forumdelen die er bijhoren.
	 * Check modrechten van gebruiker.
	 * 
	 * @return ForumDelen[]
	 */
	public function getWachtOpGoedkeuring() {
		$postsByDraad = array_group_by('draad_id', ForumPostsModel::instance()->find('wacht_goedkeuring = TRUE AND verwijderd = FALSE'));
		$dradenByDeel = array_group_by('forum_id', ForumDradenModel::instance()->getForumDradenById(array_keys($postsByDraad)));
		$delenById = array_key_property('forum_id', ForumDelenModel::instance()->getForumDelenById(array_keys($dradenByDeel)));
		foreach ($delenById as $forum_id => $deel) {
			if ($deel->magModereren()) {
				$deel->setForumDraden($dradenByDeel[$deel->forum_id]);
			} else {
				unset($delenById[$forum_id]);
			}
		}
		foreach ($dradenByDeel as $forum_id => $draden) {
			if (array_key_exists($forum_id, $delenById)) {
				foreach ($draden as $draad) {
					$draad->setForumPosts($postsByDraad[$draad->draad_id]);
				}
			} else {
				unset($draden[$forum_id]);
			}
		}
		return $delenById;
	}

}

class ForumDradenGelezenModel extends PersistenceModel {

	const orm = 'ForumDraadGelezen';

	protected static $instance;

	public function setWanneerGelezenDoorLid(ForumDraad $draad) {
		$gelezen = $this->retrieveByPrimaryKey(array($draad->draad_id, LoginLid::instance()->getUid()));
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

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = LidInstellingen::get('forum', 'draden_per_pagina');
	}

	public function getHuidigePagina() {
		return $this->pagina;
	}

	public function setHuidigePagina($number) {
		if ($number > 0) {
			$this->pagina = (int) $number;
		}
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function getAantalPaginas($forum_id) {
		return ceil($this->count('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($forum_id)) / $this->per_pagina);
	}

	public function hertellenVoorDeel(ForumDeel $deel) {
		$orm = self::orm;
		$result = Database::sqlSelect('SUM(aantal_posts)', $orm::getTableName(), 'forum_id = ?', array($deel->forum_id));
		$deel->aantal_posts = (int) $result->fetchColumn();
		$deel->aantal_draden = $this->count('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($deel->forum_id));
		ForumDelenModel::instance()->update($deel);
	}

	/**
	 * Eager loading of ForumDraadGelezen[].
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $orderby
	 * @param int $limit max amount of results
	 * @param int $start resultset from index
	 * @return PersistentEntity[]
	 */
	public function find($criteria = null, array $criteria_params = array(), $orderby = null, $limit = null, $start = 0) {
		$orm = self::orm;
		$from = $orm::getTableName() . ' AS d LEFT JOIN forum_draden_gelezen AS g ON d.draad_id = g.draad_id AND g.lid_id = ?';
		$columns = $orm::getFields();
		foreach ($columns as $i => $column) {
			$columns[$i] = 'd.' . $column;
		}
		$columns[] = 'g.datum_tijd AS wanneer_gelezen';
		$result = Database::sqlSelect($columns, $from, $criteria, array_merge(array(LoginLid::instance()->getUid()), $criteria_params), $orderby, $limit, $start);
		return $result->fetchAll(PDO::FETCH_CLASS, self::orm);
	}

	public function getForumDradenVoorDeel($forum_id) {
		return $this->find('d.forum_id = ? AND d.wacht_goedkeuring = FALSE AND d.verwijderd = FALSE', array($forum_id), 'd.plakkerig DESC, d.laatst_gewijzigd DESC', $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
	}

	/**
	 * Laad recente (niet) (belangrijke) draadjes.
	 * Eager loading van laatste ForumPost
	 * Check leesrechten van gebruiker.
	 * RSS: use token & return delen.
	 * 
	 * @param int $aantal
	 * @param boolean $belangrijk
	 * @param boolean $rss
	 * @return ForumDraad[]
	 */
	public function getRecenteForumDraden($aantal, $belangrijk = null, $rss = false) {
		$draden = $this->find(
				($belangrijk === null ? '' : 'd.belangrijk = ? AND ') . 'd.wacht_goedkeuring = FALSE AND d.verwijderd = FALSE', ($belangrijk === null ? array() : array($belangrijk)), 'd.laatst_gewijzigd DESC', $aantal);
		$posts_ids = array_keys(array_key_property('laatste_post_id', $draden, false));
		$posts = ForumPostsModel::instance()->getForumPostsById($posts_ids);
		$delen_ids = array_keys(array_key_property('forum_id', $draden, false));
		$delen = ForumDelenModel::instance()->getForumDelenById($delen_ids);
		foreach ($draden as $i => $draad) {
			if (!$delen[$draad->forum_id]->magLezen($rss)) {
				unset($draden[$i]);
			} elseif (array_key_exists($draad->laatste_post_id, $posts)) {
				$draad->setForumPosts(array($posts[$draad->laatste_post_id]));
			}
		}
		if ($rss) {
			return array($draden, $delen);
		}
		return $draden;
	}

	public function getRssForumDradenEnDelen() {
		return $this->getRecenteForumDraden(LidInstellingen::get('forum', 'zoekresultaten'), null, true);
	}

	public function getForumDraad($id) {
		$draad = $this->retrieveByPrimaryKey(array($id));
		if (!$draad) {
			throw new Exception('Forumdraad bestaat niet!');
		}
		return $draad;
	}

	public function getForumDradenById(array $ids) {
		$count = count($ids);
		if ($count < 1) {
			return array();
		}
		$in = implode(', ', array_fill(0, $count, '?'));
		return array_key_property('draad_id', $this->find('d.draad_id IN (' . $in . ')', $ids));
	}

	public function maakForumDraad($forum_id, $titel) {
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
		$draad->wacht_goedkeuring = !LoginLid::instance()->hasPermission('P_LOGGED_IN');
		$draad->plakkerig = false;
		$draad->belangrijk = false;
		$draad->draad_id = (int) ForumDradenModel::instance()->create($draad);
		return $draad;
	}

	public function wijzigForumDraad(ForumDraad $draad, $property, $value) {
		if (!property_exists($draad, $property)) {
			throw new Exception('Property undefined: ' . $property);
		}
		$draad->$property = $value;
		return $this->update($draad);
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

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = LidInstellingen::get('forum', 'posts_per_pagina');
	}

	public function getHuidigePagina() {
		return $this->pagina;
	}

	public function setHuidigePagina($number) {
		if ($number > 0) {
			$this->pagina = (int) $number;
		}
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function getAantalPaginas($draad_id) {
		return ceil($this->count('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad_id)) / $this->per_pagina);
	}

	public function hertellenVoorDraad(ForumDraad $draad) {
		$draad->aantal_posts = $this->count('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id));
		ForumDradenModel::instance()->update($draad);
	}

	public function getForumPostsVoorDraad(ForumDraad $draad) {
		$posts = $this->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		// 2008 filter
		if (LidInstellingen::get('forum', 'filter2008') == 'ja') {
			foreach ($posts as $post) {
				if (startsWith($post->lid_id, '08')) {
					$post->gefilterd = 'Bericht van 2008';
				}
			}
		}
		return $posts;
	}

	public function getPaginaVoorPost(ForumPost $post) {
		$count = $this->count('draad_id = ? AND post_id < ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($post->draad_id, $post->post_id));
		return ceil($count / $this->per_pagina);
	}

	public function getAantalForumPostsVoorLid($uid) {
		return $this->count('lid_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($uid));
	}

	/**
	 * Laad de meest recente forumposts van een gebruiker.
	 * Check leesrechten van gebruiker.
	 * 
	 * @param string $uid
	 * @param int $aantal
	 * @return array(ForumPost[], ForumDraad[])
	 */
	public function getRecenteForumPostsVanLid($uid, $aantal) {
		$posts = $this->find('lid_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($uid), 'post_id DESC', $aantal);
		$draden_ids = array_keys(array_key_property('draad_id', $posts, false));
		$draden = ForumDradenModel::instance()->getForumDradenById($draden_ids);
		$delen_ids = array_keys(array_key_property('forum_id', $draden, false));
		$delen = ForumDelenModel::instance()->getForumDelenById($delen_ids);
		foreach ($posts as $i => $post) {
			$deel = $delen[$draden[$post->draad_id]->forum_id];
			if (!$deel->magLezen()) {
				unset($draden[$post->draad_id]);
				unset($posts[$i]);
			}
		}
		return array($posts, $draden);
	}

	public function getForumPost($id) {
		$post = $this->retrieveByPrimaryKey(array($id));
		if (!$post) {
			throw new Exception('Forumpost bestaat niet!');
		}
		return $post;
	}

	public function getForumPostsById(array $ids) {
		$count = count($ids);
		if ($count < 1) {
			return array();
		}
		$in = implode(', ', array_fill(0, $count, '?'));
		return array_key_property('post_id', $this->find('post_id IN (' . $in . ')', $ids));
	}

	public function maakForumPost($draad_id, $tekst, $ip, $wacht_goedkeuring) {
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
		$post->post_id = (int) ForumPostsModel::instance()->create($post);
		return $post;
	}

	public function verwijderForumPost(ForumPost $post) {
		if ($post->verwijderd) {
			throw new Exception('Al verwijderd!');
		}
		$post->verwijderd = true;
		return $this->update($post);
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

	public function offtopicForumPost(ForumPost $post) {
		$post->tekst = '[offtopic]' . $post->tekst . '[/offtopic]';
		$post->laatst_bewerkt = getDateTime();
		$post->bewerkt_tekst = 'offtopic door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate]' . "\n";
		return $this->update($post);
	}

	public function goedkeurenForumPost(ForumPost $post) {
		if (!$post->wacht_goedkeuring) {
			throw new Exception('Al goedgekeurd!');
		}
		$post->wacht_goedkeuring = false;
		$post->laatst_bewerkt = getDateTime();
		$post->bewerkt_tekst .= '[prive=P_FORUM_MOD]Goedgekeurd door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate][/prive]' . "\n";
		return $this->update($post);
	}

	public function citeerForumPost(ForumPost $post) {
		$tekst = CsrUbb::filterPrive($post->tekst);
		return '[citaat=' . $post->lid_id . ']' . $tekst . '[/citaat]';
	}

}
