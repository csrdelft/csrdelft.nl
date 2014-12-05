<?php

/**
 * ForumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class AbstractForumModel extends PersistenceModel {

	protected function __construct() {
		parent::__construct('forum/');
	}

}

class ForumModel extends AbstractForumModel {

	const orm = 'ForumCategorie';

	protected static $instance;

	/**
	 * Eager loading of ForumDeel[].
	 * 
	 * @return ForumCategorie[]
	 */
	public function getForumIndeling() {
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

	public function opschonen() {
		// Oude lege concepten verwijderen
		ForumDradenReagerenModel::instance()->verwijderLegeConcepten();

		// Niet-goedgekeurde posts verwijderen
		$posts = ForumPostsModel::instance()->find('verwijderd = TRUE AND wacht_goedkeuring = TRUE');
		foreach ($posts as $post) {
			ForumPostsModel::instance()->delete($post);
		}

		// Voor alle ex-leden settings opschonen
		$uids = Database::instance()->sqlSelect(array('uid'), 'lid', "status IN ('S_CIE','S_NOBODY','S_EXLID','S_OVERLEDEN')");
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

	public function getForumDelenVoorCategorie(ForumCategorie $cat) {
		return $this->find('categorie_id = ?', array($cat->categorie_id), 'volgorde ASC');
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

	/**
	 * Geeft de mogelijke opties om een draadje mee te delen.
	 * 
	 * @param ForumDeel $deel
	 * @return ForumDeel[]
	 */
	public function getForumDelenOptiesOmTeDelen(ForumDeel $deel) {
		if (strpos($deel->rechten_posten, 'verticale:') !== false) {
			$type = '%verticale:%';
			$sort = 'titel ASC';
		} elseif (strpos($deel->rechten_posten, 'lidjaar:') !== false) {
			$type = '%lidjaar:%';
			$sort = 'titel DESC';
		} else {
			return array();
		}
		return $this->find('rechten_posten != ? AND rechten_posten LIKE ?', array($deel->rechten_posten, $type), $sort)->fetchAll();
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
		$deel->laatste_wijziging_uid = null;
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
		$rowcount = $this->deleteByPrimaryKey(array($id));
		if ($rowcount !== 1) {
			throw new Exception('Deelforum verwijderen mislukt');
		}
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
				} else {
					$melding .= 'goedgekeurd (bevat ' . $draad->aantal_posts . ' berichten)';
					setMelding($melding, 2);
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
		if (empty($gevonden_delen) OR empty($gevonden_draden)) {
			if (ForumPostsModel::instance()->getAantalWachtOpGoedkeuring() > 0) {
				setMelding('U heeft onvoldoende rechten om de berichten goed te keuren', 0);
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
	public function zoeken($query, $titel, $datum, $ouder, $jaar) {
		$gevonden_draden = group_by_distinct('draad_id', ForumDradenModel::instance()->zoeken($query, $datum, $ouder, $jaar)); // zoek op titel in draden
		if ($titel === true) {
			$gevonden_posts = array();
		} else {
			$gevonden_posts = group_by('draad_id', ForumPostsModel::instance()->zoeken($query, $datum, $ouder, $jaar)); // zoek op tekst in posts
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
					$array_first_post = ForumPostsModel::instance()->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), 'post_id ASC', null, 1)->fetchAll();
					$draad->setForumPosts($array_first_post);
				}
			}
		}
		// check permissies op delen
		$delen_ids = array_keys(group_by_distinct('forum_id', $gevonden_draden, false));
		$gevonden_delen = group_by_distinct('forum_id', ForumDelenModel::instance()->getForumDelenById($delen_ids));
		$gedeeld_ids = array_keys(group_by_distinct('gedeeld_met', $gevonden_draden, false));
		$gedeeld_delen = group_by_distinct('forum_id', ForumDelenModel::instance()->getForumDelenById($gedeeld_ids));
		foreach ($gevonden_delen as $forum_id => $deel) {
			foreach ($gevonden_draden as $draad_id => $draad) {
				// if binnen foreach draad vanwege check op draad gedeeld met
				if (!$deel->magLezen() AND ! ($draad->gedeeld_met AND $gedeeld_delen[$draad->gedeeld_met]->magLezen())) {
					if ($draad->forum_id === $deel->forum_id) {
						unset($gevonden_draden[$draad_id]);
					}
				}
			}
		}
		if ($titel !== true) {
			usort($gevonden_draden, array($this, 'sorteren'));
		}
		return array($gevonden_draden, $gevonden_delen + $gedeeld_delen);
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
		return $this->find('draad_id = ? AND uid != ? AND datum_tijd > ?', array($draad->draad_id, LoginModel::getUid(), getDateTime(strtotime(Instellingen::get('forum', 'reageren_tijd')))));
	}

	public function getReagerenVoorDeel(ForumDeel $deel) {
		return $this->find('forum_id = ? AND draad_id = 0 AND uid != ? AND datum_tijd > ?', array($deel->forum_id, LoginModel::getUid(), getDateTime(strtotime(Instellingen::get('forum', 'reageren_tijd')))));
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
	 * @param ForumDraadGelezen $gelezen
	 * @param ForumDraad $draad
	 */
	public function setWanneerGelezenDoorLid(ForumDraad $draad) {
		if (!LoginModel::mag('P_LOGGED_IN')) {
			return;
		}
		$create = false;
		// Haal nieuw object op omdat de view de ongewijzigde nodig heeft
		$gelezen = $this->getWanneerGelezenDoorLid($draad);
		if (!$gelezen) {
			$gelezen = new ForumDraadGelezen();
			$gelezen->draad_id = $draad->draad_id;
			$gelezen->uid = LoginModel::getUid();
			$create = true;
		}
		foreach ($draad->getForumPosts() as $post) {
			if ($post->laatst_gewijzigd) {
				if ($post->laatst_gewijzigd > $gelezen->datum_tijd) {
					$gelezen->datum_tijd = $post->laatst_gewijzigd;
				}
			} else {
				if ($post->datum_tijd > $gelezen->datum_tijd) {
					$gelezen->datum_tijd = $post->datum_tijd;
				}
			}
		}
		if ($create) {
			$this->create($gelezen);
		} else {
			$this->update($gelezen);
		}
	}

	/**
	 * Bereken het percentage lezers dat een post heeft gelezen.
	 * 
	 * @param ForumPost $post
	 * @param array $draad
	 * @return int percentage
	 */
	public function getGelezenPercentage(Forumpost $post, ForumDraad $draad) {
		$lezers = $draad->getLezers();
		$counter = 0;
		foreach ($lezers as $gelezen) {
			if ($post->laatst_gewijzigd) {
				if ($post->laatst_gewijzigd <= $gelezen->datum_tijd) {
					$counter++;
				}
			} else {
				if ($post->datum_tijd <= $gelezen->datum_tijd) {
					$counter++;
				}
			}
		}
		return (int) ($counter * 100 / $draad->getAantalLezers());
	}

	public function getLezersVanDraad(ForumDraad $draad) {
		return $this->find('draad_id = ?', array($draad->draad_id))->fetchAll();
	}

	public function verwijderDraadGelezen(ForumDraad $draad) {
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $gelezen) {
			$this->delete($gelezen);
		}
	}

	public function verwijderDraadGelezenVoorLid($uid) {
		if (!Lid::isValidUid($uid)) {
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
				$rowcount = $this->deleteByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
				if ($rowcount !== 1) {
					throw new Exception('Weer tonen mislukt');
				}
			}
		}
	}

	public function toonAllesVoorLid($uid) {
		if (!Lid::isValidUid($uid)) {
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
				$rowcount = $this->deleteByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
				if ($rowcount !== 1) {
					throw new Exception('Volgen stoppen mislukt');
				}
			}
		}
	}

	public function volgNietsVoorLid($uid) {
		if (!Lid::isValidUid($uid)) {
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

	public function hertellenVoorDeel(ForumDeel $deel) {
		$result = Database::sqlSelect(array('SUM(aantal_posts)'), $this->orm->getTableName(), 'forum_id = ?', array($deel->forum_id));
		$deel->aantal_posts = (int) $result->fetchColumn();
		$deel->aantal_draden = $this->count('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($deel->forum_id));
		// reset laatst gewijzigd
		$last_draad = $this->find('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($deel->forum_id), 'laatst_gewijzigd DESC', null, 1)->fetch();
		$deel->laatste_post_id = $last_draad->laatste_post_id;
		$deel->laatste_wijziging_uid = $last_draad->laatste_wijziging_uid;
		$deel->laatst_gewijzigd = $last_draad->laatst_gewijzigd;
		ForumDelenModel::instance()->update($deel);
	}

	public function zoeken($query, $datum, $ouder, $jaar) {
		$this->per_pagina = (int) LidInstellingen::get('forum', 'zoekresultaten');
		$attributes = array('*', 'MATCH(titel) AGAINST (? IN BOOLEAN MODE) AS score');
		$where = 'wacht_goedkeuring = FALSE AND verwijderd = FALSE AND ';
		if ($datum === 'gemaakt') {
			$order = 'datum_tijd';
		} else {
			$order = 'laatst_gewijzigd';
		}
		$where .= $order;
		$order .= ' DESC, score DESC';
		if ($ouder === 'ouder') {
			$where .= ' < ?';
		} else {
			$where .= ' > ?';
		}
		$where .= 'HAVING score > 0';
		$datum = getDateTime(strtotime('-' . $jaar . ' year'));
		$terms = explode(' ', $query);
		foreach ($terms as $i => $term) {
			if (!endsWith($term, '*')) {
				$terms[$i] .= '*'; // append wildcard
			}
		}
		$query = implode(' +', $terms); // set terms to AND
		$results = Database::sqlSelect($attributes, $this->orm->getTableName(), $where, array($query, $datum), $order, null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		$results->setFetchMode(PDO::FETCH_CLASS, self::orm, array($cast = true));
		return $results;
	}

	public function getPrullenbakVoorDeel(ForumDeel $deel) {
		return $this->find('forum_id = ? AND verwijderd = TRUE', array($deel->forum_id), 'wacht_goedkeuring DESC, laatst_gewijzigd DESC')->fetchAll();
	}

	public function getBelangrijkeForumDradenVoorDeel(ForumDeel $deel) {
		return $this->find('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE AND belangrijk = TRUE', array($deel->forum_id), 'plakkerig DESC, laatst_gewijzigd DESC')->fetchAll();
	}

	public function getForumDradenVoorDeel(ForumDeel $deel) {
		return $this->find('(forum_id = ? OR gedeeld_met = ?) AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($deel->forum_id, $deel->forum_id), 'plakkerig DESC, laatst_gewijzigd DESC', null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina)->fetchAll();
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
	 * @return ForumDraad[] of voor rss: array( ForumDraad[], ForumDeel[] )
	 */
	public function getRecenteForumDraden($aantal, $belangrijk, $rss = false) {
		if (!is_int($aantal)) {
			$aantal = (int) LidInstellingen::get('forum', 'draden_per_pagina');
			$pagina = $this->pagina;
		} else {
			$pagina = 1;
		}
		$delen = ForumDelenModel::instance()->getForumDelenVoorLid($rss);
		$count = count($delen);
		if ($count < 1) {
			if ($rss) {
				return array(array(), array());
			}
			return array();
		}
		$forum_ids_stub = implode(', ', array_fill(0, $count, '?'));
		$forum_ids = array_keys($delen);
		$params = array_merge($forum_ids, $forum_ids);
		$verbergen = ForumDradenVerbergenModel::instance()->find('uid = ?', array(LoginModel::getUid()));
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
		$draden = $this->find('(forum_id IN (' . $forum_ids_stub . ') OR gedeeld_met IN (' . $forum_ids_stub . '))' . $verborgen . ' AND wacht_goedkeuring = FALSE AND verwijderd = FALSE' . $belangrijk, $params, 'laatst_gewijzigd DESC', null, $aantal, ($pagina - 1) * $aantal)->fetchAll();
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
		$rowcount = $this->update($draad);
		if ($rowcount !== 1) {
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
	}

}

class ForumPostsModel extends AbstractForumModel implements Paging {

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
	 * Totaal aantal posts die wachten op goedkeuring
	 * @var int
	 */
	private $aantal_wacht;

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = LidInstellingen::get('forum', 'posts_per_pagina');
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
		$this->setHuidigePagina((int) ceil($count / $this->per_pagina), $gelezen->draad_id);
	}

	public function hertellenVoorDraadEnDeel(ForumDraad $draad, ForumDeel $deel) {
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
			$last_post = $this->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), 'laatst_gewijzigd DESC', null, 1)->fetch();
			$draad->laatste_post_id = $last_post->post_id;
			$draad->laatste_wijziging_uid = $last_post->uid;
			$draad->laatst_gewijzigd = $last_post->laatst_gewijzigd;
			if ($draad->gesloten) {
				ForumDradenVolgenModel::instance()->stopVolgenVoorIedereen($draad);
				ForumDradenReagerenModel::instance()->verwijderReagerenVoorDraad($draad);
			}
		}
		ForumDradenModel::instance()->update($draad);
		ForumDradenModel::instance()->hertellenVoorDeel($deel);
	}

	public function zoeken($query, $datum, $ouder, $jaar) {
		$this->per_pagina = (int) LidInstellingen::get('forum', 'zoekresultaten');
		$attributes = array('*', 'MATCH(tekst) AGAINST (? IN NATURAL LANGUAGE MODE) AS score');
		$where = 'wacht_goedkeuring = FALSE AND verwijderd = FALSE AND ';
		if ($datum === 'gemaakt') {
			$where .= 'datum_tijd';
		} else {
			$where .= 'laatst_gewijzigd';
		}
		if ($ouder === 'ouder') {
			$where .= ' < ?';
		} else {
			$where .= ' > ?';
		}
		$datum = getDateTime(strtotime('-' . $jaar . ' year'));
		$where .= ' HAVING score > 0';
		$results = Database::sqlSelect($attributes, $this->orm->getTableName(), $where, array($query, $datum), 'score DESC', null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
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
		return $this->find('draad_id = ? AND verwijderd = TRUE', array($draad->draad_id), 'post_id ASC')->fetchAll();
	}

	public function getForumPostsVoorDraad(ForumDraad $draad) {
		if (LoginModel::mag('P_FORUM_MOD')) {
			$goedkeuring = '';
		} else {
			$goedkeuring = ' AND wacht_goedkeuring = FALSE';
		}
		$posts = $this->find('draad_id = ?' . $goedkeuring . ' AND verwijderd = FALSE', array($draad->draad_id), 'post_id ASC', null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina)->fetchAll();
		if ($draad->eerste_post_plakkerig AND $this->pagina !== 1) {
			$first_post = $this->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), 'post_id ASC', null, 1)->fetch();
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
	 * @return array( ForumPost[], ForumDraad[] )
	 */
	public function getRecenteForumPostsVanLid($uid, $aantal, $draad_uniek = false) {
		$where = 'uid = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE';
		if ($draad_uniek) {
			$where = 'post_id = (
	SELECT MAX(post_id)
	FROM ' . $this->orm->getTableName() . ' AS zelfde_draad
	WHERE ' . $this->orm->getTableName() . '.draad_id = zelfde_draad.draad_id
	AND ' . $where . '
)';
		}
		$posts = $this->find($where, array($uid), 'post_id DESC', null, $aantal)->fetchAll();
		$draden_ids = array_keys(group_by_distinct('draad_id', $posts, false));
		$draden = ForumDradenModel::instance()->getForumDradenById($draden_ids);
		$delen_ids = array_keys(group_by_distinct('forum_id', $draden, false));
		$delen = ForumDelenModel::instance()->getForumDelenById($delen_ids);
		$gedeeld_ids = array_keys(group_by_distinct('gedeeld_met', $draden, false));
		$gedeeld = group_by_distinct('forum_id', ForumDelenModel::instance()->getForumDelenById($gedeeld_ids));
		foreach ($delen as $forum_id => $deel) {
			foreach ($draden as $draad_id => $draad) {
				// if binnen foreach draad vanwege check op draad gedeeld met
				if (!$deel->magLezen() AND ! ($draad->gedeeld_met AND $gedeeld[$draad->gedeeld_met]->magLezen())) {
					if ($draad->forum_id === $forum_id) {
						foreach ($posts as $i => $post) {
							if ($post->draad_id === $draad_id) {
								unset($posts[$i]);
							}
						}
						unset($draden[$draad_id]);
					}
				}
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

	public function verwijderForumPost(ForumPost $post, ForumDraad $draad, ForumDeel $deel) {
		$post->verwijderd = !$post->verwijderd;
		$rowcount = $this->update($post);
		if ($rowcount !== 1) {
			throw new Exception('Verwijderen mislukt');
		}
		$this->hertellenVoorDraadEnDeel($draad, $deel);
	}

	public function verwijderForumPostsVoorDraad(ForumDraad $draad) {
		Database::sqlUpdate($this->orm->getTableName(), array('verwijderd' => $draad->verwijderd), 'draad_id = :id', array(':id' => $draad->draad_id));
	}

	public function bewerkForumPost($nieuwe_tekst, $reden, ForumPost $post, ForumDraad $draad, ForumDeel $deel) {
		$verschil = levenshtein($post->tekst, $nieuwe_tekst);
		$post->tekst = $nieuwe_tekst;
		$post->laatst_gewijzigd = getDateTime();
		$bewerkt = 'bewerkt door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate]';
		if ($reden !== '') {
			$bewerkt .= ': [tekst]' . CsrBB::escapeUbbOff($reden) . '[/tekst]';
		}
		$bewerkt .= "\n";
		$post->bewerkt_tekst .= $bewerkt;
		$rowcount = $this->update($post);
		if ($rowcount !== 1) {
			throw new Exception('Bewerken mislukt');
		}
		if ($verschil > 3) {
			$draad->laatst_gewijzigd = $post->laatst_gewijzigd;
			$draad->laatste_post_id = $post->post_id;
			$draad->laatste_wijziging_uid = $post->uid;
			$rowcount = ForumDradenModel::instance()->update($draad);
			if ($rowcount !== 1) {
				throw new Exception('Bewerken mislukt');
			}
			$deel->laatst_gewijzigd = $post->laatst_gewijzigd;
			$deel->laatste_post_id = $post->post_id;
			$deel->laatste_wijziging_uid = $post->uid;
			$rowcount = ForumDelenModel::instance()->update($deel);
			if ($rowcount !== 1) {
				throw new Exception('Bewerken mislukt');
			}
		}
	}

	public function verplaatsForumPost(ForumDraad $nieuwDraad, ForumPost $post) {
		$post->draad_id = $nieuwDraad->draad_id;
		$post->laatst_gewijzigd = getDateTime();
		$post->bewerkt_tekst .= 'verplaatst door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate]' . "\n";
		$rowcount = $this->update($post);
		if ($rowcount !== 1) {
			throw new Exception('Verplaatsen mislukt');
		}
	}

	public function offtopicForumPost(ForumPost $post) {
		$post->tekst = '[offtopic]' . $post->tekst . '[/offtopic]';
		$post->laatst_gewijzigd = getDateTime();
		$post->bewerkt_tekst .= 'offtopic door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate]' . "\n";
		$rowcount = $this->update($post);
		if ($rowcount !== 1) {
			throw new Exception('Offtopic mislukt');
		}
	}

	public function tellenEnGoedkeurenForumPost(ForumPost $post, ForumDraad $draad, ForumDeel $deel) {
		if ($post->wacht_goedkeuring) {
			$post->wacht_goedkeuring = false;
			$post->laatst_gewijzigd = getDateTime();
			$post->bewerkt_tekst .= '[prive=P_FORUM_MOD]Goedgekeurd door [lid=' . LoginModel::getUid() . '] [reldate]' . $post->laatst_gewijzigd . '[/reldate][/prive]' . "\n";
			$rowcount = $this->update($post);
			if ($rowcount !== 1) {
				throw new Exception('Goedkeuren mislukt');
			}
		}
		$draad->aantal_posts++;
		$draad->laatst_gewijzigd = $post->laatst_gewijzigd;
		$draad->laatste_post_id = $post->post_id;
		$draad->laatste_wijziging_uid = $post->uid;
		if ($draad->wacht_goedkeuring) {
			$draad->wacht_goedkeuring = false;
			$deel->aantal_draden++;
		}
		$rowcount = ForumDradenModel::instance()->update($draad);
		if ($rowcount !== 1) {
			throw new Exception('Goedkeuren mislukt');
		}
		$deel->aantal_posts++;
		$deel->laatst_gewijzigd = $post->laatst_gewijzigd;
		$deel->laatste_post_id = $post->post_id;
		$deel->laatste_wijziging_uid = $post->uid;
		$rowcount = ForumDelenModel::instance()->update($deel);
		if ($rowcount !== 1) {
			throw new Exception('Goedkeuren mislukt');
		}
	}

	public function citeerForumPost(ForumPost $post) {
		$tekst = CsrBB::filterCommentaar(CsrBB::filterPrive($post->tekst));
		return '[citaat=' . $post->uid . ']' . CsrBB::sluitTags($tekst) . '[/citaat]';
	}

}
