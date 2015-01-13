<?php

require_once 'model/Paging.interface.php';

/**
 * ForumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class AbstractForumModel extends CachedPersistenceModel {

	protected function __construct() {
		parent::__construct('forum/');
	}

}

class ForumModel extends AbstractForumModel {

	const orm = 'ForumCategorie';

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

	/**
	 * Eager loading of ForumDeel[].
	 * 
	 * @return ForumCategorie[]
	 */
	public function getForumIndelingVoorLid() {
		if (!isset($this->indeling)) {
			$delenByCatId = group_by('categorie_id', ForumDelenModel::instance()->getForumDelenVoorLid());
			$this->indeling = array();
			foreach ($this->prefetch() as $cat) {
				if ($cat->magLezen()) {
					$this->indeling[] = $cat;
					if (isset($delenByCatId[$cat->categorie_id])) {
						$cat->setForumDelen($delenByCatId[$cat->categorie_id]);
					} else {
						$cat->setForumDelen(array());
					}
				}
			}
		}
		return $this->indeling;
	}

	public function getForumCategorie($id) {
		$cat = $this->retrieveByPrimaryKey(array($id));
		if (!$cat) {
			throw new Exception('Forum-categorie bestaat niet!');
		}
		return $cat;
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
		$uids = Database::instance()->sqlSelect(array('uid'), 'lid', 'status IN (?,?,?,?)', array(LidStatus::Commissie, LidStatus::Nobody, LidStatus::Exlid, LidStatus::Overleden));
		$uids->setFetchMode(PDO::FETCH_COLUMN, 0);
		foreach ($uids as $uid) {
			ForumDradenGelezenModel::instance()->verwijderDraadGelezenVoorLid($uid);
			ForumDradenVerbergenModel::instance()->toonAllesVoorLid($uid);
			ForumDradenVolgenModel::instance()->volgNietsVoorLid($uid);
			ForumDradenReagerenModel::instance()->verwijderReagerenVoorLid($uid);
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

class ForumDelenModel extends AbstractForumModel {

	const orm = 'ForumDeel';

	protected static $instance;
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

	public function getForumDelenVoorCategorie(ForumCategorie $cat) {
		return $this->prefetch('categorie_id = ?', array($cat->categorie_id));
	}

	public function getForumDelenVoorLid($rss = false) {
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
		$deel->rechten_lezen = 'P_FORUM_READ';
		$deel->rechten_posten = 'P_FORUM_POST';
		$deel->rechten_modereren = 'P_FORUM_MOD';
		$deel->volgorde = 0;
		$deel->forum_id = $this->create($deel);
		return $deel;
	}

	public function verwijderForumDeel($id) {
		$rowCount = $this->deleteByPrimaryKey(array($id));
		if ($rowCount !== 1) {
			throw new Exception('Deelforum verwijderen mislukt');
		}
	}

	public function getRecent($belangrijk = null) {
		$deel = new ForumDeel();
		if ($belangrijk) {
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
	 * @return ForumDraden[]
	 */
	public function getWachtOpGoedkeuring() {
		$postsByDraadId = group_by('draad_id', ForumPostsModel::instance()->find('wacht_goedkeuring = TRUE AND verwijderd = FALSE'));
		$dradenById = group_by_distinct('draad_id', ForumDradenModel::instance()->find('wacht_goedkeuring = TRUE AND verwijderd = FALSE'));
		$dradenById += ForumDradenModel::instance()->getForumDradenById(array_keys($postsByDraadId)); // laad draden bij posts
		foreach ($dradenById as $draad) { // laad posts bij draden
			if (array_key_exists($draad->draad_id, $postsByDraadId)) { // post is al gevonden
				$draad->setForumPosts($postsByDraadId[$draad->draad_id]);
			} else {
				$melding = 'Draad ' . $draad->draad_id . ' niet goedgekeurd, maar alle posts wel. Automatische actie: ';
				$draad->wacht_goedkeuring = false;
				if ($draad->aantal_posts === 0) {
					$draad->verwijderd = true;
					$melding .= 'verwijderd (bevat geen berichten)';
					setMelding($melding, 2);
				} else {
					$melding .= 'goedgekeurd (bevat ' . $draad->aantal_posts . ' berichten)';
					setMelding($melding, 2);
				}
				ForumDradenModel::instance()->update($draad);
			}
		}
		// check permissies
		foreach ($dradenById as $draad_id => $draad) {
			if (!$draad->magModereren()) {
				unset($dradenById[$draad_id]);
			}
		}
		if (empty($dradenById) AND ForumPostsModel::instance()->getAantalWachtOpGoedkeuring() > 0) {
			setMelding('U heeft onvoldoende rechten om de berichten goed te keuren', 0);
		}
		return $dradenById;
	}

	/**
	 * Zoek op titel van draadjes en tekst van posts en laad forumdelen die erbij horen.
	 * Check leesrechten van gebruiker.
	 * 
	 * @return ForumDraden[]
	 */
	public function zoeken($query, $titel, $datum, $ouder, $jaar, $limit) {
		$gevonden_draden = group_by_distinct('draad_id', ForumDradenModel::instance()->zoeken($query, $datum, $ouder, $jaar, $limit)); // zoek op titel in draden
		if ($titel === true) {
			$gevonden_posts = array();
		} else {
			$gevonden_posts = group_by('draad_id', ForumPostsModel::instance()->zoeken($query, $datum, $ouder, $jaar, $limit)); // zoek op tekst in posts
			$gevonden_draden += ForumDradenModel::instance()->getForumDradenById(array_keys($gevonden_posts)); // laad draden bij posts
			// laad posts bij draden
			foreach ($gevonden_draden as $draad) {
				if (property_exists($draad, 'score')) { // gevonden op draad titel
					$draad->score = (float) 50;
				} else { // gevonden op post tekst
					$draad->score = (float) 0;
				}
				if (array_key_exists($draad->draad_id, $gevonden_posts)) { // posts al gevonden
					$draad->setForumPosts($gevonden_posts[$draad->draad_id]);
					foreach ($draad->getForumPosts() as $post) {
						$draad->score += (float) $post->score;
					}
				} else { // laad eerste post
					$array_first_post = ForumPostsModel::instance()->prefetch('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), null, null, 1);
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

class ForumDradenReagerenModel extends AbstractForumModel {

	const orm = 'ForumDraadReageren';

	protected static $instance;

	/**
	 * Fetch reageren object voor deel of draad.
	 * 
	 * @param ForumDeel $deel
	 * @param int $draad_id
	 * @return ForumDraadReageren
	 */
	private function getReagerenDoorLid(ForumDeel $deel, $draad_id = null) {
		return $this->retrieveByPrimaryKey(array($deel->forum_id, (int) $draad_id, LoginModel::getUid()));
	}

	private function nieuwReagerenDoorLid(ForumDeel $deel, $draad_id = null, $concept = null, $titel = null) {
		$reageren = new ForumDraadReageren();
		$reageren->forum_id = $deel->forum_id;
		$reageren->draad_id = (int) $draad_id;
		$reageren->uid = LoginModel::getUid();
		$reageren->datum_tijd = getDateTime();
		$reageren->concept = $concept;
		$reageren->titel = $titel;
		$this->create($reageren);
		return $reageren;
	}

	public function getReagerenVoorDraad(ForumDraad $draad) {
		return $this->prefetch('draad_id = ? AND uid != ? AND datum_tijd > ?', array($draad->draad_id, LoginModel::getUid(), getDateTime(strtotime(Instellingen::get('forum', 'reageren_tijd')))));
	}

	public function getReagerenVoorDeel(ForumDeel $deel) {
		return $this->prefetch('forum_id = ? AND draad_id = 0 AND uid != ? AND datum_tijd > ?', array($deel->forum_id, LoginModel::getUid(), getDateTime(strtotime(Instellingen::get('forum', 'reageren_tijd')))));
	}

	public function verwijderLegeConcepten() {
		foreach ($this->find('concept IS NULL AND datum_tijd < ?', array(getDateTime(strtotime(Instellingen::get('forum', 'reageren_tijd'))))) as $reageren) {
			$this->delete($reageren);
		}
	}

	public function verwijderReagerenVoorDraad(ForumDraad $draad) {
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $reageren) {
			$this->delete($reageren);
		}
	}

	public function verwijderReagerenVoorLid($uid) {
		foreach ($this->find('uid = ?', array($uid)) as $reageren) {
			$this->delete($reageren);
		}
	}

	public function setWanneerReagerenDoorLid(ForumDeel $deel, $draad_id = null) {
		$reageren = $this->getReagerenDoorLid($deel, $draad_id);
		if (!$reageren) {
			$this->nieuwReagerenDoorLid($deel, $draad_id);
		} else {
			$reageren->datum_tijd = getDateTime();
			$this->update($reageren);
		}
	}

	public function getConcept(ForumDeel $deel, $draad_id = null) {
		$reageren = $this->getReagerenDoorLid($deel, $draad_id);
		if ($reageren) {
			return $reageren->concept;
		}
		return null;
	}

	public function getConceptTitel(ForumDeel $deel) {
		$reageren = $this->getReagerenDoorLid($deel);
		if ($reageren) {
			return $reageren->titel;
		}
		return null;
	}

	public function setConcept(ForumDeel $deel, $draad_id = null, $concept = null, $titel = null) {
		$reageren = $this->getReagerenDoorLid($deel, $draad_id);
		if (empty($concept)) {
			if ($reageren) {
				$this->delete($reageren);
			}
		} else {
			if (!$reageren) {
				$this->nieuwReagerenDoorLid($deel, $draad_id, $concept, $titel);
			} else {
				$reageren->concept = $concept;
				$reageren->titel = $titel;
				$this->update($reageren);
			}
		}
	}

}

class ForumDradenGelezenModel extends AbstractForumModel {

	const orm = 'ForumDraadGelezen';

	protected static $instance;

	public function getWanneerGelezenDoorLid(ForumDraad $draad) {
		if (!LoginModel::mag('P_LOGGED_IN')) {
			return false;
		}
		return $this->retrieveByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
	}

	/**
	 * Ga na welke posts op de huidige pagina het laatst is geplaatst of gewijzigd.
	 * 
	 * @param ForumDraad $draad
	 * @param int $timestamp
	 */
	public function setWanneerGelezenDoorLid(ForumDraad $draad, $timestamp = null) {
		if (!LoginModel::mag('P_LOGGED_IN')) {
			return false;
		}
		$gelezen = $this->getWanneerGelezenDoorLid($draad);
		if ($gelezen) {
			$create = false;
		} else {
			$gelezen = new ForumDraadGelezen();
			$gelezen->draad_id = $draad->draad_id;
			$gelezen->uid = LoginModel::getUid();
			$create = true;
		}
		if ($timestamp === null) {
			foreach ($draad->getForumPosts() as $post) {
				if (strtotime($post->laatst_gewijzigd) > strtotime($gelezen->datum_tijd)) {
					$gelezen->datum_tijd = $post->laatst_gewijzigd;
				}
			}
		} else {
			if (is_int($timestamp)) {
				$gelezen->datum_tijd = getDateTime($timestamp);
			} else {
				throw new Exception('Geen int: $timestamp');
			}
		}
		if ($gelezen->datum_tijd) { // > 0 posts?
			if ($create) {
				$this->create($gelezen);
			} else {
				$this->update($gelezen);
			}
		}
		return true;
	}

	/**
	 * Bereken het percentage lezers dat een post heeft gelezen.
	 * 
	 * @param ForumPost $post
	 * @param array $draad
	 * @return int percentage
	 */
	public function getGelezenPercentage(Forumpost $post) {
		$lezers = $post->getForumDraad()->getLezers();
		$counter = 0;
		foreach ($lezers as $gelezen) {
			if ($post->laatst_gewijzigd) {
				if ($post->laatst_gewijzigd <= $gelezen->datum_tijd) {
					$counter++;
				}
			}
		}
		return (int) ($counter * 100 / $post->getForumDraad()->getAantalLezers());
	}

	public function getLezersVanDraad(ForumDraad $draad) {
		return $this->prefetch('draad_id = ?', array($draad->draad_id));
	}

	public function verwijderDraadGelezen(ForumDraad $draad) {
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $gelezen) {
			$this->delete($gelezen);
		}
	}

	public function verwijderDraadGelezenVoorLid($uid) {
		if (!AccountModel::isValidUid($uid)) {
			throw new Exception('invalid lid id');
		}
		foreach ($this->find('uid = ?', array($uid)) as $gelezen) {
			$this->delete($gelezen);
		}
	}

}

class ForumDradenVerbergenModel extends AbstractForumModel {

	const orm = 'ForumDraadVerbergen';

	protected static $instance;

	public function getAantalVerborgenVoorLid() {
		return $this->count('uid = ?', array(LoginModel::getUid()));
	}

	public function getVerbergenVoorLid(ForumDraad $draad) {
		return $this->existsByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
	}

	public function setVerbergenVoorLid(ForumDraad $draad, $verbergen = true) {
		$verborgen = $this->getVerbergenVoorLid($draad);
		if ($verbergen) {
			if (!$verborgen) {
				$verborgen = new ForumDraadVerbergen();
				$verborgen->draad_id = $draad->draad_id;
				$verborgen->uid = LoginModel::getUid();
				$this->create($verborgen);
			}
		} else {
			if ($verborgen) {
				$rowCount = $this->deleteByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
				if ($rowCount !== 1) {
					throw new Exception('Weer tonen mislukt');
				}
			}
		}
	}

	public function toonAllesVoorLid($uid) {
		if (!AccountModel::isValidUid($uid)) {
			throw new Exception('invalid lid id');
		}
		foreach ($this->find('uid = ?', array($uid)) as $verborgen) {
			$this->delete($verborgen);
		}
	}

	public function toonDraadVoorIedereen(ForumDraad $draad) {
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $verborgen) {
			$this->delete($verborgen);
		}
	}

}

class ForumDradenVolgenModel extends AbstractForumModel {

	const orm = 'ForumDraadVolgen';

	protected static $instance;

	public function getAantalVolgenVoorLid() {
		return $this->count('uid = ?', array(LoginModel::getUid()));
	}

	public function getVolgersVanDraad(ForumDraad $draad) {
		return $this->find('draad_id = ?', array($draad->draad_id))->fetchAll(PDO::FETCH_COLUMN, 1);
	}

	public function getVolgenVoorLid(ForumDraad $draad) {
		return $this->existsByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
	}

	public function setVolgenVoorLid(ForumDraad $draad, $volgen = true) {
		$gevolgd = $this->getVolgenVoorLid($draad);
		if ($volgen) {
			if (!$gevolgd) {
				$gevolgd = new ForumDraadVolgen();
				$gevolgd->draad_id = $draad->draad_id;
				$gevolgd->uid = LoginModel::getUid();
				$this->create($gevolgd);
			}
		} else {
			if ($gevolgd) {
				$rowCount = $this->deleteByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
				if ($rowCount !== 1) {
					throw new Exception('Volgen stoppen mislukt');
				}
			}
		}
	}

	public function volgNietsVoorLid($uid) {
		if (!AccountModel::isValidUid($uid)) {
			throw new Exception('invalid lid id');
		}
		foreach ($this->find('uid = ?', array($uid)) as $volgen) {
			$this->delete($volgen);
		}
	}

	public function stopVolgenVoorIedereen(ForumDraad $draad) {
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $volgen) {
			$this->delete($volgen);
		}
	}

}

class ForumDradenModel extends AbstractForumModel implements Paging {

	const orm = 'ForumDraad';

	protected static $instance;
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

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = (int) LidInstellingen::get('forum', 'draden_per_pagina');
		$this->aantal_paginas = array();
		$this->aantal_plakkerig = null;
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function setAantalPerPagina($aantal) {
		$this->per_pagina = (int) $aantal;
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
			$this->aantal_paginas[$forum_id] = (int) ceil($this->count('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($forum_id)) / $this->per_pagina);
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
		return (int) ceil($count / $this->per_pagina);
	}

	public function zoeken($query, $datumsoort, $ouder, $jaar, $limit) {
		$this->per_pagina = (int) $limit;
		$attributes = array('*', 'MATCH(titel) AGAINST (? IN BOOLEAN MODE) AS score');
		$terms = explode(' ', $query);
		foreach ($terms as $i => $term) {
			if (!endsWith($term, '*')) {
				$terms[$i] .= '*'; // append wildcard
			}
		}
		$where_params = array(implode(' +', $terms)); // set terms to AND
		$where = 'wacht_goedkeuring = FALSE AND verwijderd = FALSE';
		$order = 'score DESC, plakkerig DESC';
		if (in_array($datumsoort, array('datum_tijd', 'laatst_gewijzigd'))) {
			$order .=', ' . $datumsoort . ' DESC';
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
		$results = Database::sqlSelect($attributes, $this->orm->getTableName(), $where, $where_params, null, $order, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		$results->setFetchMode(PDO::FETCH_CLASS, self::orm, array($cast = true));
		return $results;
	}

	public function getPrullenbakVoorDeel(ForumDeel $deel) {
		return $this->prefetch('forum_id = ? AND verwijderd = TRUE', array($deel->forum_id));
	}

	public function getBelangrijkeForumDradenVoorDeel(ForumDeel $deel) {
		return $this->prefetch('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE AND belangrijk = TRUE', array($deel->forum_id));
	}

	public function getForumDradenVoorDeel(ForumDeel $deel) {
		return $this->prefetch('(forum_id = ? OR gedeeld_met = ?) AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($deel->forum_id, $deel->forum_id), null, null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
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
	public function getRecenteForumDraden($aantal, $belangrijk, $rss = false) {
		if (!is_int($aantal)) {
			$aantal = (int) LidInstellingen::get('forum', 'draden_per_pagina');
			$pagina = $this->pagina;
		} else {
			$pagina = 1;
		}
		$delenById = ForumDelenModel::instance()->getForumDelenVoorLid($rss);
		$count = count($delenById);
		if ($count < 1) {
			return array();
		}
		$forum_ids_stub = implode(', ', array_fill(0, $count, '?'));
		$forum_ids = array_keys($delenById);
		$params = array_merge($forum_ids, $forum_ids);
		$verbergen = ForumDradenVerbergenModel::instance()->prefetch('uid = ?', array(LoginModel::getUid()));
		$draden_ids = array_keys(group_by_distinct('draad_id', $verbergen));
		$count = count($draden_ids);
		if ($count > 0) {
			$verborgen = ' AND draad_id NOT IN (' . implode(', ', array_fill(0, $count, '?')) . ')';
			$params = array_merge($params, $draden_ids);
		} else {
			$verborgen = '';
		}
		if ($belangrijk !== null) {
			$params[] = (boolean) $belangrijk;
			$belangrijk = ' AND belangrijk = ?';
		} else {
			$belangrijk = '';
		}
		$dradenById = group_by_distinct('draad_id', $this->find('(forum_id IN (' . $forum_ids_stub . ') OR gedeeld_met IN (' . $forum_ids_stub . '))' . $verborgen . ' AND wacht_goedkeuring = FALSE AND verwijderd = FALSE' . $belangrijk, $params, null, 'laatst_gewijzigd DESC', $aantal, ($pagina - 1) * $aantal));
		$count = count($dradenById);
		$draden_ids = array_keys($dradenById);
		array_unshift($draden_ids, LoginModel::getUid());
		ForumDradenGelezenModel::instance()->prefetch('uid = ? AND draad_id IN (' . implode(', ', array_fill(0, $count, '?')) . ')', $draden_ids);
		return $dradenById;
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
		return group_by_distinct('draad_id', $this->find('draad_id IN (' . $in . ')' . $where, array_merge($ids, $where_params)));
	}

	public function maakForumDraad($forum_id, $titel, $wacht_goedkeuring) {
		$draad = new ForumDraad();
		$draad->forum_id = (int) $forum_id;
		$draad->gedeeld_met = null;
		$draad->uid = LoginModel::getUid();
		$draad->titel = $titel;
		$draad->datum_tijd = getDateTime();
		$draad->laatst_gewijzigd = $draad->datum_tijd;
		$draad->laatste_post_id = null;
		$draad->laatste_wijziging_uid = null;
		$draad->aantal_posts = 0;
		$draad->gesloten = false;
		$draad->verwijderd = false;
		$draad->wacht_goedkeuring = $wacht_goedkeuring;
		$draad->plakkerig = false;
		$draad->belangrijk = false;
		$draad->eerste_post_plakkerig = false;
		$draad->pagina_per_post = false;
		$draad->draad_id = (int) ForumDradenModel::instance()->create($draad);
		return $draad;
	}

	public function wijzigForumDraad(ForumDraad $draad, $property, $value) {
		if (!property_exists($draad, $property)) {
			throw new Exception('Property undefined: ' . $property);
		}
		$draad->$property = $value;
		$rowCount = $this->update($draad);
		if ($rowCount !== 1) {
			throw new Exception('Wijzigen van ' . $property . ' mislukt');
		}
		if ($property === 'belangrijk') {
			ForumDradenVerbergenModel::instance()->toonDraadVoorIedereen($draad);
		} elseif ($property === 'gesloten') {
			ForumDradenVolgenModel::instance()->stopVolgenVoorIedereen($draad);
		} elseif ($property === 'verwijderd') {
			ForumDradenVolgenModel::instance()->stopVolgenVoorIedereen($draad);
			ForumDradenVerbergenModel::instance()->toonDraadVoorIedereen($draad);
			ForumDradenGelezenModel::instance()->verwijderDraadGelezen($draad);
			ForumDradenReagerenModel::instance()->verwijderReagerenVoorDraad($draad);
			ForumPostsModel::instance()->verwijderForumPostsVoorDraad($draad);
		}
		ForumPostsModel::instance()->hertellenVoorDraad($draad);
	}

}

class ForumPostsModel extends AbstractForumModel implements Paging {

	const orm = 'ForumPost';

	protected static $instance;
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'datum_tijd ASC';
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
	 * Totaal aantal posts die wachten op goedkeuring
	 * @var int
	 */
	private $aantal_wacht;

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = (int) LidInstellingen::get('forum', 'posts_per_pagina');
		$this->aantal_paginas = array();
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function setAantalPerPagina($aantal) {
		$this->per_pagina = (int) $aantal;
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
			$draad = ForumDradenModel::instance()->getForumDraad($draad_id);
			if ($draad->pagina_per_post) {
				$this->per_pagina = 1;
			} else {
				$this->per_pagina = (int) LidInstellingen::get('forum', 'posts_per_pagina');
			}
			$this->aantal_paginas[$draad_id] = (int) ceil($this->count('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad_id)) / $this->per_pagina);
		}
		return max(1, $this->aantal_paginas[$draad_id]);
	}

	public function getPaginaVoorPost(ForumPost $post) {
		$count = $this->count('draad_id = ? AND post_id <= ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($post->draad_id, $post->post_id));
		return (int) ceil($count / $this->per_pagina);
	}

	public function setPaginaVoorLaatstGelezen(ForumDraadGelezen $gelezen) {
		$count = 1 + $this->count('draad_id = ? AND datum_tijd <= ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($gelezen->draad_id, $gelezen->datum_tijd));
		$this->getAantalPaginas($gelezen->draad_id); // set per_pagina
		$this->setHuidigePagina((int) ceil($count / $this->per_pagina), $gelezen->draad_id);
	}

	public function hertellenVoorDraad(ForumDraad $draad) {
		$draad->aantal_posts = $this->count('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id));
		if ($draad->verwijderd OR $draad->aantal_posts < 1) {
			if (!$draad->verwijderd) {
				$draad->verwijderd = true;
				setMelding('Draad ' . $draad->draad_id . ' bevat geen berichten. Automatische actie: draad verwijderd', 2);
			} elseif ($draad->aantal_posts > 0) {
				ForumPostsModel::instance()->verwijderForumPostsVoorDraad($draad);
				$draad->aantal_posts = 0;
				setMelding('Draad ' . $draad->draad_id . ' bevat nog berichten. Automatische actie: berichten verwijderd', 2);
			}
			$draad->laatste_post_id = null;
			$draad->laatste_wijziging_uid = null;
			$draad->laatst_gewijzigd = null;
			ForumDradenGelezenModel::instance()->verwijderDraadGelezen($draad);
			ForumDradenVerbergenModel::instance()->toonDraadVoorIedereen($draad);
			ForumDradenVolgenModel::instance()->stopVolgenVoorIedereen($draad);
			ForumDradenReagerenModel::instance()->verwijderReagerenVoorDraad($draad);
		} else { // reset last post
			$last_post = $this->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), null, 'laatst_gewijzigd DESC', 1)->fetch();
			$draad->laatste_post_id = $last_post->post_id;
			$draad->laatste_wijziging_uid = $last_post->uid;
			$draad->laatst_gewijzigd = $last_post->laatst_gewijzigd;
			if ($draad->gesloten) {
				ForumDradenVolgenModel::instance()->stopVolgenVoorIedereen($draad);
				ForumDradenReagerenModel::instance()->verwijderReagerenVoorDraad($draad);
			}
		}
		ForumDradenModel::instance()->update($draad);
	}

	public function zoeken($query, $datumsoort, $ouder, $jaar, $limit) {
		$this->per_pagina = (int) $limit;
		$attributes = array('*', 'MATCH(tekst) AGAINST (? IN NATURAL LANGUAGE MODE) AS score');
		$where = 'wacht_goedkeuring = FALSE AND verwijderd = FALSE';
		$where_params = array($query);
		$order = 'score DESC';
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
		$results = Database::sqlSelect($attributes, $this->orm->getTableName(), $where, $where_params, null, $order, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		$results->setFetchMode(PDO::FETCH_CLASS, self::orm, array($cast = true));
		return $results;
	}

	public function getAantalForumPostsVoorLid($uid) {
		return $this->count('uid = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($uid));
	}

	public function getAantalWachtOpGoedkeuring() {
		if (!isset($this->aantal_wacht)) {
			$this->aantal_wacht = $this->count('wacht_goedkeuring = TRUE AND verwijderd = FALSE');
		}
		return $this->aantal_wacht;
	}

	public function getPrullenbakVoorDraad(ForumDraad $draad) {
		return $this->prefetch('draad_id = ? AND verwijderd = TRUE', array($draad->draad_id));
	}

	public function getForumPostsVoorDraad(ForumDraad $draad) {
		if (LoginModel::mag('P_FORUM_MOD')) {
			$goedkeuring = '';
		} else {
			$goedkeuring = ' AND wacht_goedkeuring = FALSE';
		}
		$posts = $this->prefetch('draad_id = ?' . $goedkeuring . ' AND verwijderd = FALSE', array($draad->draad_id), null, null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		if ($draad->eerste_post_plakkerig AND $this->pagina !== 1) {
			$first_post = $this->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), null, null, 1)->fetch();
			array_unshift($posts, $first_post);
		}
		// 2008-filter
		if (LidInstellingen::get('forum', 'filter2008') == 'ja') {
			foreach ($posts as $post) {
				if (startsWith($post->uid, '08')) {
					$post->gefilterd = 'Bericht van 2008';
				}
			}
		}
		return $posts;
	}

	/**
	 * Laad de meest recente forumposts van een gebruiker.
	 * Check leesrechten van gebruiker.
	 * 
	 * @param string $uid
	 * @param int $aantal
	 * @param int $draad_uniek
	 * @return ForumPost[]
	 */
	public function getRecenteForumPostsVanLid($uid, $aantal, $draad_uniek = false) {
		$where = 'uid = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE';
		$posts = array();
		$draden_ids = array();
		foreach ($this->find($where, array($uid), $draad_uniek ? 'draad_id' : null, 'laatst_gewijzigd DESC', $aantal) as $post) {
			if ($post->getForumDraad()->magLezen()) {
				$posts[] = $post;
				$draden_ids[] = $post->draad_id;
			}
		}
		$count = count($draden_ids);
		array_unshift($draden_ids, LoginModel::getUid());
		ForumDradenGelezenModel::instance()->prefetch('uid = ? AND draad_id IN (' . implode(', ', array_fill(0, $count, '?')) . ')', $draden_ids);
		return $posts;
	}

	public function getForumPost($id) {
		$post = $this->retrieveByPrimaryKey(array($id));
		if (!$post) {
			throw new Exception('Forum-reactie bestaat niet!');
		}
		return $post;
	}

	public function maakForumPost($draad_id, $tekst, $ip, $wacht_goedkeuring, $email) {
		$post = new ForumPost();
		$post->draad_id = (int) $draad_id;
		$post->uid = LoginModel::getUid();
		$post->tekst = $tekst;
		$post->datum_tijd = getDateTime();
		$post->laatst_gewijzigd = $post->datum_tijd;
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

	public function verwijderForumPost(ForumPost $post) {
		$post->verwijderd = !$post->verwijderd;
		$rowCount = $this->update($post);
		if ($rowCount !== 1) {
			throw new Exception('Verwijderen mislukt');
		}
		$this->hertellenVoorDraad($post->getForumDraad());
	}

	public function verwijderForumPostsVoorDraad(ForumDraad $draad) {
		Database::sqlUpdate($this->orm->getTableName(), array('verwijderd' => $draad->verwijderd), 'draad_id = :id', array(':id' => $draad->draad_id));
	}

	public function bewerkForumPost($nieuwe_tekst, $reden, ForumPost $post) {
		$verschil = levenshtein($post->tekst, $nieuwe_tekst);
		$post->tekst = $nieuwe_tekst;
		$post->laatst_gewijzigd = getDateTime();
		$bewerkt = 'bewerkt door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate]';
		if ($reden !== '') {
			$bewerkt .= ': [tekst]' . CsrBB::escapeUbbOff($reden) . '[/tekst]';
		}
		$bewerkt .= "\n";
		$post->bewerkt_tekst .= $bewerkt;
		$rowCount = $this->update($post);
		if ($rowCount !== 1) {
			throw new Exception('Bewerken mislukt');
		}
		if ($verschil > 3) {
			$draad = $post->getForumDraad();
			$draad->laatst_gewijzigd = $post->laatst_gewijzigd;
			$draad->laatste_post_id = $post->post_id;
			$draad->laatste_wijziging_uid = $post->uid;
			$rowCount = ForumDradenModel::instance()->update($draad);
			if ($rowCount !== 1) {
				throw new Exception('Bewerken mislukt');
			}
		}
	}

	public function verplaatsForumPost(ForumDraad $nieuwDraad, ForumPost $post) {
		$post->draad_id = $nieuwDraad->draad_id;
		$post->laatst_gewijzigd = getDateTime();
		$post->bewerkt_tekst .= 'verplaatst door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate]' . "\n";
		$rowCount = $this->update($post);
		if ($rowCount !== 1) {
			throw new Exception('Verplaatsen mislukt');
		}
	}

	public function offtopicForumPost(ForumPost $post) {
		$post->tekst = '[offtopic]' . $post->tekst . '[/offtopic]';
		$post->laatst_gewijzigd = getDateTime();
		$post->bewerkt_tekst .= 'offtopic door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate]' . "\n";
		$rowCount = $this->update($post);
		if ($rowCount !== 1) {
			throw new Exception('Offtopic mislukt');
		}
	}

	public function goedkeurenOptellenForumPost(ForumPost $post) {
		if ($post->wacht_goedkeuring) {
			$post->wacht_goedkeuring = false;
			$post->laatst_gewijzigd = getDateTime();
			$post->bewerkt_tekst .= '[prive=P_FORUM_MOD]Goedgekeurd door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate][/prive]' . "\n";
			$rowCount = $this->update($post);
			if ($rowCount !== 1) {
				throw new Exception('Goedkeuren mislukt');
			}
		}
		$draad = $post->getForumDraad();
		$draad->aantal_posts++;
		$draad->laatst_gewijzigd = $post->laatst_gewijzigd;
		$draad->laatste_post_id = $post->post_id;
		$draad->laatste_wijziging_uid = $post->uid;
		if ($draad->wacht_goedkeuring) {
			$draad->wacht_goedkeuring = false;
		}
		$rowCount = ForumDradenModel::instance()->update($draad);
		if ($rowCount !== 1) {
			throw new Exception('Goedkeuren mislukt');
		}
	}

	public function citeerForumPost(ForumPost $post) {
		$tekst = CsrBB::filterCommentaar(CsrBB::filterPrive($post->tekst));
		return '[citaat=' . $post->uid . ']' . CsrBB::sluitTags($tekst) . '[/citaat]';
	}

	public function getStats($terug) {
		$fields = array('(UNIX_TIMESTAMP(DATE(datum_tijd)) * 1000) AS datum', 'COUNT(*)');
		return Database::sqlSelect($fields, $this->orm->getTableName(), 'datum_tijd > ?', array(getDateTime(strtotime($terug))), 'datum');
	}

}
