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
	public function loadAlleWanneerGelezen(array $draden) {
		$draden = array_group_by('draad_id', $draden);
		$keys = '(' . implode(', ', array_keys($draden)) . ')';
		$gelezen = $this->find('draad_id IN ?', array($keys));
		foreach ($gelezen as $draad) {
			$draden[$draad->draad_id]->setWanneerGelezen($draad->datum_tijd);
		}
		return $draden;
	}

	public function getWanneerGelezenDoorLid(ForumDraad $draad) {
		$gelezen = $this->retrieveByPrimaryKey(array($draad->draad_id, LoginLid::instance()->getUid()));
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

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = LidInstellingen::get('forum', 'draden_per_pagina');
	}

	public function setHuidigePagina($number) {
		$this->pagina = $number;
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function getAantalPaginas($forum_id) {
		return ceil($this->count('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($forum_id)) / $this->per_pagina);
	}

	/**
	 * Eager loading of ForumDraadGelezen[].
	 * 
	 * @param int $forum_id
	 * @return ForumDraad[]
	 */
	public function getForumDradenVoorDeel($forum_id) {
		$draden = $this->find('forum_id = ?  AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($forum_id), 'laatst_gewijzigd', $this->per_pagina, $this->pagina * $this->per_pagina);
		ForumDraadGelezenModel::instance()->loadAlleWanneerGelezen($draden);
		return $draden;
	}

	public function getForumDraad($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newForumDraad($forum_id) {
		$draad = new ForumDraad();
		$draad->forum_id = $forum_id;
		return $draad;
	}

	/**
	 * Wijzig property van forumdraad.
	 * 
	 * @param int $id
	 * @param string $property
	 * @param mixed $value
	 * @return ForumDraad
	 */
	public function wijzigForumDraad($id, $property, $value) {
		$draad = $this->getForumDraad($id);
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

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = LidInstellingen::get('forum', 'posts_per_pagina');
	}

	public function setHuidigePagina($number) {
		if ($number > 0) {
			$this->pagina = ceil($number);
		}
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function getAantalPaginas($draad_id) {
		return ceil($this->count('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad_id)) / $this->per_pagina);
	}

	public function getForumPostsVoorDraad($draad_id) {
		return $this->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad_id), null, $this->per_pagina, $this->pagina * $this->per_pagina);
	}

	public function getPaginaVoorPost(ForumPost $post) {
		$count = $this->count('draad_id = ? AND post_id < ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($post->draad_id, $post->post_id));
		return ceil($count / $this->per_pagina);
	}

	public function getForumPost($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newForumPost($draad_id) {
		$post = new ForumPost();
		$post->draad_id = $draad_id;
		return $post;
	}

	public function removeForumPost($id) {
		$post = $this->getForumPost($id);
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
