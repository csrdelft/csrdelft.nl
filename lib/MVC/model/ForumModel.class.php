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

	public function getCategorie($id) {
		return $this->retrieveByPrimaryKey(array($id));
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

}

class ForumDraadGelezenModel extends PersistenceModel {

	const orm = 'ForumDraadGelezen';

	protected static $instance;

	/**
	 * Laadt voor elke forumdraad wanneer de gebruiker
	 * deze voor het laatst gelezen heeft.
	 * 
	 * @param ForumDraad[] $draden
	 * @return ForumDraad[]
	 */
	public function loadAlleWanneerGelezen(array &$draden) {
		$draden = array_key_property('draad_id', $draden);
		$in = implode(', ', array_fill(0, count($draden), '?'));
		$values = array_merge(array(LoginLid::instance()->getUid()), array_keys($draden));
		$gelezen = array_key_property('draad_id', $this->find('lid_id = ? AND draad_id IN (' . $in . ')', $values));
		foreach ($draden as $draad) {
			if (array_key_exists($draad->draad_id, $gelezen)) {
				$wanneer = $gelezen[$draad->draad_id]->datum_tijd;
			} else {
				$wanneer = '0000-00-00 00:00:00';
			}
			$draden[$draad->draad_id]->setWanneerGelezen($wanneer);
		}
		return $draden;
	}

	public function getWanneerGelezenDoorLid(ForumDraad $draad) {
		$gelezen = $this->retrieveByPrimaryKey(array($draad->draad_id, LoginLid::instance()->getUid()));
		if (!$gelezen) {
			return '0000-00-00 00:00:00';
		}
		return $gelezen->datum_tijd;
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
	 * Totaal aantal paginas
	 * @var int[]
	 */
	private $aantal_paginas;

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = LidInstellingen::get('forum', 'draden_per_pagina');
		$this->aantal_paginas = array();
	}

	public function getHuidigePagina() {
		return $this->pagina;
	}

	public function setHuidigePagina($number) {
		if ($number > 0) {
			$this->pagina = $number;
		}
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function getAantalPaginas($forum_id) {
		if (!array_key_exists($forum_id, $this->aantal_paginas)) {
			$this->aantal_paginas[$forum_id] = ceil($this->count('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($forum_id)) / $this->per_pagina);
		}
		return $this->aantal_paginas[$forum_id];
	}

	/**
	 * Eager loading of ForumDraadGelezen[].
	 * 
	 * @param int $forum_id
	 * @return ForumDraad[]
	 */
	public function getForumDradenVoorDeel($forum_id) {
		$draden = $this->find('forum_id = ?  AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($forum_id), 'plakkerig DESC, laatst_gewijzigd DESC', $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		ForumDraadGelezenModel::instance()->loadAlleWanneerGelezen($draden);
		return $draden;
	}

	public function getForumDraad($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function niewForumDraad($forum_id) {
		$draad = new ForumDraad();
		$draad->forum_id = $forum_id;
		//TODO:
		$draad->lid_id;
		$draad->titel;
		$draad->datum_tijd;
		$draad->laatst_gewijzigd;
		$draad->laatste_post_id;
		$draad->laatste_lid_id;
		$draad->aantal_posts;
		$draad->gesloten;
		$draad->verwijderd;
		$draad->wacht_goedkeuring;
		$draad->plakkerig;
		$draad->belangrijk;
		$draad->forum_posts;
		$draad->wanneer_gelezen;
		return $draad;
	}

	/**
	 * Wijzig property van forumdraad.
	 * 
	 * @param ForumDraad $draad
	 * @param string $property
	 * @param mixed $value
	 * @return ForumDraad
	 */
	public function wijzigForumDraad(ForumDraad $draad, $property, $value) {
		if (!property_exists($draad, $property)) {
			throw new Exception('Property undefined: ' . $property);
		}
		if ($property === 'forum_id' AND !ForumDelenModel::instance()->bestaatForumDeel($value)) {
			throw new Exception('Forum bestaat niet!');
		}
		$draad->$property = $value;
		$this->update($draad);
		return $draad;
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
	 * Aantal draden per pagina
	 * @var int
	 */
	private $per_pagina;
	/**
	 * Totaal aantal paginas
	 * @var int[]
	 */
	private $aantal_paginas;

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = LidInstellingen::get('forum', 'posts_per_pagina');
		$this->aantal_paginas = array();
	}

	public function getHuidigePagina() {
		return $this->pagina;
	}

	public function setHuidigePagina($number) {
		if ($number > 0) {
			$this->pagina = $number;
		}
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function getAantalPaginas($draad_id) {
		if (!array_key_exists($draad_id, $this->aantal_paginas)) {
			$this->aantal_paginas[$draad_id] = ceil($this->count('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad_id)) / $this->per_pagina);
		}
		return $this->aantal_paginas[$draad_id];
	}

	public function getForumPostsVoorDraad($draad_id) {
		$posts = $this->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad_id), null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
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

	public function getForumPost($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function niewForumPost($draad_id) {
		$post = new ForumPost();
		$post->draad_id = $draad_id;
		return $post;
	}

	public function verwijderForumPost(ForumPost $post) {
		$post->verwijderd = true;
		$this->update($post);
		return $post;
	}

	public function bewerkForumPost($id, $nieuwe_tekst, $reden = null) {
		$post = $this->getForumPost($id);
		$post->tekst = $nieuwe_tekst;
		$post->laatst_bewerkt = getDateTime();
		$bewerkt = 'bewerkt door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate]';
		if ($reden !== null) {
			$bewerkt .= ': ' . $reden;
		}
		$bewerkt .= "\n";
		$post->bewerkt_tekst .= $bewerkt;
		$this->update($post);
		return $post;
	}

	public function offtopicForumPost($id) {
		$post = $this->getForumPost($id);
		$post->tekst = '[offtopic]' . $post->tekst . '[/offtopic]';
		$post->laatst_bewerkt = getDateTime();
		$post->bewerkt_tekst = 'offtopic door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate]' . "\n";
		$this->update($post);
		return $post;
	}

	public function goedkeurenForumPost($id) {
		$post = $this->getForumPost($id);
		if (!$post->wacht_goedkeuring) {
			throw new Exception('Al goedgekeurd!');
		}
		$post->wacht_goedkeuring = false;
		$post->laatst_bewerkt = getDateTime();
		$post->bewerkt_tekst .= '[prive=P_FORUM_MOD]Goedgekeurd door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate][/prive]' . "\n";
		$this->update($post);
		return $post;
	}

}
