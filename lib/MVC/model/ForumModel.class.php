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
		if (count($delen) > 0) {
			$cat = new ForumCategorie();
			$cat->titel = 'Zonder categorie';
			$cat->omschrijving = 'onzichtbaar voor gebruikers';
			$cat->rechten_lezen = 'P_FORUM_MOD';
			$cat->setForumDelen($delen[0]);
			$categorien[] = $cat;
		}
		return $categorien;
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
		return ceil($this->count('forum_id = ?', array($forum_id)) / $this->per_pagina);
	}

	public function getForumDradenVoorDeel($forum_id) {
		return $this->find('forum_id = ?', array($forum_id), 'laatst_gepost', $this->per_pagina, $this->pagina * $this->per_pagina);
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
	 * forum_id / titel / gesloten / plakkerig / belangrijk
	 * 
	 * @param int $id
	 * @param string $property
	 * @param mixed $value
	 * @return ForumDraad
	 */
	public function wijzigForumDraad($id, $property, $value) {
		$draad = $this->getForumDraad($id);
		if (!in_array($property, array('forum_id', 'titel', 'gesloten', 'plakkerig', 'belangrijk'))) {
			throw new Exception('Unsupported');
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
		$this->pagina = $number;
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function getAantalPaginas($draad_id) {
		return ceil($this->count('draad_id = ? AND verwijderd = FALSE AND wacht_goedkeuring = FALSE', array($draad_id)) / $this->per_pagina);
	}

	public function getForumPostsVoorDraad($draad_id) {
		return $this->find('draad_id = ?', array($draad_id), null, $this->per_pagina, $this->pagina * $this->per_pagina);
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
