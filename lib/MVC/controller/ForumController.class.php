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
		str_replace('forum/', 'forum', $query);
		str_replace('#post', '#', $query);
		parent::__construct($query);
		$this->action = $this->getParam(1);
		if ($this->hasParam('#')) {
			$this->action = 'post';
		}
		$this->performAction($this->getParams(2));
	}

	protected function hasPermission() {
		return true; // check permissions & valid params in actions
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
	 */
	public function forumdeel($id) {
		$deel = ForumDelenModel::instance()->getForumDeel((int) $id);
		if (!$deel OR !$deel->magLezen()) {
			$this->geentoegang();
		}
		$body = new ForumDeelView($deel);
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('forum.css');
	}

	/**
	 * Forumdraadje laten zien met alle (zichtbare) posts.
	 * 
	 * @param int $id
	 */
	public function forumdraad($id, $pagina = 1) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $id);
		if (!$draad) {
			$this->geentoegang();
		}
		ForumDradenModel::instance()->setHuidigePagina((int) $pagina);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magLezen()) {
			$this->geentoegang();
		}
		$body = new ForumDraadView($draad, $deel);
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('forum.css');
		$this->view->addScript('forum.js');
	}

	/**
	 * Forumdraadje laten zien van de post.
	 * 
	 * @param int $draad_id unused
	 */
	public function forumpost($draad_id = null) {
		$post = ForumPostsModel::instance()->getForumPost((int) $this->getParam('#'));
		if (!$post) {
			$this->geentoegang();
		}
		$pagina = ForumPostsModel::instance()->getPaginaVoorPost($post);
		$this->draad($post->draad_id, $pagina);
	}

//TODO

	public function forumdraadwijzig($id, $property, $value) {
		if (!in_array($property, array('forum_id', 'titel', 'gesloten', 'plakkerig', 'belangrijk'))) {
			$this->geentoegang();
		}
		$this->model->wijzigForumDraad($id, $property, $value);
	}

	public function toevoegen($datum = '', $doorgaan = true) {
		$item = $this->model->newForumItem($datum);
		$this->view = new ForumItemFormView($item, $this->action); // fetches POST values itself
		if ($doorgaan AND $this->view->validate()) {
			$id = $this->model->create($item);
			$item->item_id = (int) $id;
			setMelding('Toegevoegd: ' . $item->titel . ' (' . $item->begin_moment . ')', 1);
			$this->view = new ForumItemMaandView($item);
			return true; // voor doorgaan
		}
	}

	public function doorgaan() {
		$this->action = 'toevoegen';
		if ($this->toevoegen()) {
			$item = $this->view->getModel();
			$_POST['datum_dag'] = date('d', $item->getEindMoment() + 60); // spring naar volgende dag bij 23:59
			$this->toevoegen('', false);
		}
	}

	public function bewerken($aid) {
		$item = $this->model->getForumItem($aid);
		$this->view = new ForumItemFormView($item, $this->action); // fetches POST values itself
		if ($this->view->validate()) {
			$rowcount = $this->model->update($item);
			if ($rowcount > 0) {
				//setMelding('Bijgewerkt', 1);
			} else {
				//setMelding('Geen wijzigingen', 0);
			}
			$this->view = new ForumItemMaandView($item);
		}
	}

	public function verwijderen($aid) {
		if ($this->model->removeForumItem($aid)) {
			//setMelding('Verwijderd', 1);
			$this->view = new ForumItemDeleteView($aid);
		}
	}

}
