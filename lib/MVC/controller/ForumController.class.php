<?php

require_once 'MVC/model/ForumModel.class.php';
require_once 'MVC/view/ForumView.class.php';

/**
 * ForumController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van het forum.
 */
class ForumController extends Controller {

	public function __construct($query) {
		parent::__construct(str_replace('forum/', 'forum', $query));
		$this->action = $this->getParam(1);
		$this->performAction($this->getParams(2));
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function hasPermission() {
		switch ($this->action) {
			case 'forum':
			case 'forumdeel':
			case 'forumdraad':
			case 'forumpost':
				return !$this->isPosted();

			case 'forumdraadwijzigen':
			case 'forumposten':
			case 'forumpostbewerken':
			case 'forumpostverwijderen':
			case 'forumpostofftopic':
			case 'forumpostgoedkeuren':
				return $this->isPosted();

			default:
				$this->action = 'forum';
				return true;
		}
	}

	/**
	 * Overzicht met categorien en forumdelen laten zien.
	 */
	public function forum() {
		$body = new ForumView(ForumModel::instance()->getForum());
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('forum.css');
	}

	/**
	 * Deelforum laten zien met draadjes in tabel.
	 * 
	 * @param int $id
	 * @param int $pagina
	 */
	public function forumdeel($id, $pagina = 1) {
		$deel = ForumDelenModel::instance()->getForumDeel((int) $id);
		if (!$deel OR !$deel->magLezen()) {
			$this->geentoegang();
		}
		$categorie = ForumModel::instance()->getCategorie($deel->categorie_id);
		if (!$categorie->magLezen()) {
			$this->geentoegang();
		}
		ForumDradenModel::instance()->setHuidigePagina((int) $pagina); // lazy loading ForumDraad[]
		$body = new ForumDeelView($deel, $categorie);
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('forum.css');
	}

	/**
	 * Forumdraadje laten zien met alle (zichtbare) posts.
	 * 
	 * @param int $id
	 * @param int $pagina
	 */
	public function forumdraad($id, $pagina = 1) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $id);
		if (!$draad) {
			$this->geentoegang();
		}
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		$categorie = ForumModel::instance()->getCategorie($deel->categorie_id);
		if (!$categorie->magLezen() OR !$deel->magLezen()) {
			$this->geentoegang();
		}
		ForumPostsModel::instance()->setHuidigePagina((int) $pagina); // lazy loading ForumPost[]
		$body = new ForumDraadView($draad, $deel, $categorie);
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('forum.css');
		$this->view->addScript('forum.js');
	}

	/**
	 * Opzoeken forumdraad van forumpost.
	 * 
	 * @param int $id
	 */
	public function forumpost($id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $id);
		if (!$post) {
			throw new Exception('Forumpost bestaat niet!');
		}
		$this->forumdraad($post->draad_id, ForumPostsModel::instance()->getPaginaVoorPost($post));
	}

	public function forumdraadwijzigen($id, $property, $value = null) {
		$draad = $this->getForumDraad((int) $id);
		if (!$draad) {
			throw new Exception('Forumdraad bestaat niet!');
		} elseif (in_array($property, array('verwijderd', 'gesloten', 'plakkerig', 'belangrijk'))) {
			$value = !$draad->$property;
		} elseif ($property === 'forum_id') {
			$value = (int) $value;
			if (!ForumDelenModel::instance()->bestaatForumDeel($value)) {
				throw new Exception('Forum bestaat niet!');
			}
		} else if ($property === 'titel') {
			if (empty($value)) {
				throw new Exception('Ongeldige titel!');
			}
		} else {
			$this->geentoegang();
		}
		ForumDradenModel::instance()->wijzigForumDraad($draad, $property, $value);
	}

	/**
	 * Forum post toevoegen en evt. nieuw draadje aanmaken.
	 * 
	 * @param int $forum_id
	 * @param int $draad_id
	 */
	public function forumposten($forum_id, $draad_id = null) {
		if ($draad_id !== null) {
			$draad = $this->getForumDraad((int) $draad_id);
			if (!$draad) {
				throw new Exception('Forumdraad bestaat niet!');
			}
		} else {
			$draad = ForumDradenModel::instance()->nieuwForumDraad((int) $forum_id);
			$draad->draad_id = (int) ForumDradenModel::instance()->create($draad);
		}

		//TODO: save post & update laatst_gewijzigd

		ForumDradenModel::instance()->update($draad);
	}

	public function forumpostbewerken() {
		//TODO
	}

	public function forumpostverwijderen() {
		//TODO
	}

	public function forumpostofftopic() {
		//TODO
	}

	public function forumpostgoedkeuren() {
		//TODO
	}

	public function forumpostciteren() {
		//TODO
	}

}
