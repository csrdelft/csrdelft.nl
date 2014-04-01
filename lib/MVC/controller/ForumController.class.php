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
		// voorkom dubbelposts
		if (!array_key_exists('forum_laatste_post_tekst', $_SESSION)) {
			$_SESSION['forum_laatste_post_tekst'] = null;
		}
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
			case 'forumpostciteren':
			case 'forumposttekst':
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
		if (!$deel OR !$deel->magLezen()) { // geen exceptie om bestaan van forumdeel niet te verraden
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
		$this->forumdraad($post->draad_id, ForumPostsModel::instance()->getPaginaVoorPost($post));
	}

	/**
	 * Wijzig een eigenschap van een draadje.
	 * 
	 * @param int $id
	 * @param string $property
	 * @param mixed $value
	 * @throws Exception indien forum niet bestaat bij verplaatsen of wijzigen mislukt
	 */
	public function forumdraadwijzigen($id, $property, $value = null) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magModereren()) {
			$this->geentoegang();
		}
		if (in_array($property, array('verwijderd', 'gesloten', 'plakkerig', 'belangrijk'))) {
			$value = !$draad->$property;
		} elseif ($property === 'forum_id') {
			$value = (int) filter_input(INPUT_POST, $property, FILTER_SANITIZE_NUMBER_INT);
		} else if ($property === 'titel') {
			$value = trim(filter_input(INPUT_POST, $property, FILTER_SANITIZE_STRIPPED));
		} else {
			$this->geentoegang();
		}
		$rowcount = ForumDradenModel::instance()->wijzigForumDraad($draad, $property, $value);
		if ($rowcount !== 1) {
			throw new Exception('Wijzigen mislukt');
		}
		$this->forumdraad($draad->draad_id);
	}

	/**
	 * Forum post toevoegen en evt. nieuw draadje aanmaken.
	 * 
	 * @param int $forum_id
	 * @param int $draad_id
	 */
	public function forumposten($forum_id, $draad_id = null) {
		$deel = ForumDelenModel::instance()->getForumDeel((int) $forum_id);
		if (!$deel->magPosten()) {
			$this->geentoegang();
		}
		$tekst = filter_input(INPUT_POST, 'bericht', FILTER_UNSAFE_RAW);
		// voorkom dubbelposts
		if ($_SESSION['forum_laatste_post_tekst'] === $tekst) {
			invokeRefresh('/forum', 'Bericht is al gepost!', 0);
		}
		if ($draad_id !== null) { // post in bestaand draadje
			$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
			if ($draad->gesloten OR $draad->forum_id !== $deel->forum_id) {
				$this->geentoegang();
			}
		} else { // post in nieuw draadje
			$draad = ForumDradenModel::instance()->maakForumDraad($deel->forum_id, trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING)));
		}
		$post = ForumPostsModel::instance()->maakForumPost($draad->draad_id, $tekst, $_SERVER['REMOTE_ADDR']);
		$draad->laatst_gewijzigd = $post->datum_tijd;
		$draad->laatste_post_id = $post->post_id;
		$draad->laatste_lid_id = $post->lid_id;
		ForumDradenModel::instance()->update($draad);
		$_SESSION['forum_laatste_post_tekst'] = $tekst;
		// redirect naar (altijd) juiste pagina
		invokeRefresh('/forumpost/' . $post->post_id . '#post' . $post->post_id); // , ($draad_id === null ? 'Draad' : 'Post') . ' succesvol toegevoegd', 1
	}

	public function forumpostbewerken($id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (($deel->magPosten() AND !$draad->gesloten AND $post->lid_id === LoginLid::instance()->getUid()) OR $deel->magModereren()) {
			// same if-statement in post_lijst.tpl
		} else {
			$this->geentoegang();
		}
		$tekst = filter_input(INPUT_POST, 'bericht', FILTER_UNSAFE_RAW);
		$reden = trim(filter_input(INPUT_POST, 'reden', FILTER_SANITIZE_STRING));
		ForumPostsModel::instance()->bewerkForumPost($post, $tekst, $reden);
		// redirect naar (altijd) juiste pagina
		invokeRefresh('/forumpost/' . $post->post_id . '#post' . $post->post_id); // , 'Post succesvol bewerkt', 1
	}

	public function forumpostverwijderen($id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magModereren()) {
			$this->geentoegang();
		}
		$rowcount = ForumPostsModel::instance()->verwijderForumPost($post);
		if ($rowcount !== 1) {
			throw new Exception('Verwijderen mislukt');
		}
		$this->view = new ForumPostDeleteView($post->post_id);
	}

	public function forumpostofftopic($id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magModereren()) {
			$this->geentoegang();
		}
		$rowcount = ForumPostsModel::instance()->offtopicForumPost($post);
		if ($rowcount !== 1) {
			throw new Exception('Offtopic mislukt');
		}
		$this->view = new ForumPostView($post, $draad, $deel);
	}

	public function forumpostgoedkeuren($id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magModereren()) {
			$this->geentoegang();
		}
		$rowcount = ForumPostsModel::instance()->goedkeurenForumPost($post);
		if ($rowcount !== 1) {
			throw new Exception('Goedkeuren mislukt');
		}
		$this->view = new ForumPostView($post, $draad, $deel);
	}

	public function forumpostciteren($id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magLezen()) {
			$this->geentoegang();
		}
		echo ForumPostsModel::instance()->citeerForumPost($post);
		exit;
	}

	public function forumposttekst($id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magLezen()) {
			$this->geentoegang();
		}
		echo $post->tekst;
		exit;
	}

}
