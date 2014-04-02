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
		parent::__construct($query);
		if (!array_key_exists('forum_concept', $_SESSION)) {
			$_SESSION['forum_concept'] = '';
		}
		$this->action = $this->getParam(2);
		$this->performAction($this->getParams(3));
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function hasPermission() {
		switch ($this->action) {
			case 'wijzigen':
				return true;

			case 'rss':
			case 'recent':
			case 'deel':
			case 'onderwerp':
			case 'reactie':
			case 'wacht':
				return !$this->isPosted();

			case 'zoeken':
			case 'posten':
			case 'bewerken':
			case 'verwijderen':
			case 'offtopic':
			case 'goedkeuren':
			case 'citeren':
			case 'tekst':
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
		if (LoginLid::instance()->hasPermission('P_LOGGED_IN')) {
			$this->view = new CsrLayoutPage($body);
		} else {
			//uitgelogd heeft nieuwe layout
			$this->view = new CsrLayout2Page($body);
		}
		$this->view->addStylesheet('forum.css');
		$this->view->addScript('forum.js');
	}

	/**
	 * RSS feed van recente draadjes tonen.
	 */
	public function rss() {
		header('Content-Type: application/rss+xml; charset=UTF-8');
		$draden_delen = ForumDradenModel::instance()->getRssForumDradenEnDelen();
		$this->view = new ForumRssView($draden_delen[0], $draden_delen[1]);
	}

	/**
	 * Tonen van alle posts die wachten op goedkeuring.
	 */
	public function wacht() {
		$body = new ForumResultatenView(ForumDelenModel::instance()->getWachtOpGoedkeuring(), 'Wacht op goedkeuring');
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('forum.css');
		$this->view->addScript('forum.js');
	}

	/**
	 * Tonen van alle posts die wachten op goedkeuring.
	 */
	public function zoeken() {
		$query = filter_input(INPUT_POST, 'zoeken', FILTER_SANITIZE_SPECIAL_CHARS);
		$body = new ForumResultatenView(ForumModel::instance()->zoeken($query), 'Zoekresultaten voor: ' . $query);
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('forum.css');
		$this->view->addScript('forum.js');
	}

	/**
	 * Recente draadjes laten zien in tabel.
	 */
	public function recent() {
		$deel = ForumDelenModel::instance()->getRecent();
		$body = new ForumDeelView($deel);
		if (LoginLid::instance()->hasPermission('P_LOGGED_IN')) {
			$this->view = new CsrLayoutPage($body);
		} else {
			//uitgelogd heeft nieuwe layout
			$this->view = new CsrLayout2Page($body);
		}
		$this->view->addStylesheet('forum.css');
		$this->view->addScript('forum.js');
	}

	/**
	 * Deelforum laten zien met draadjes in tabel.
	 * 
	 * @param int $id
	 * @param int $pagina
	 */
	public function deel($id, $pagina = 1) {
		$deel = ForumDelenModel::instance()->getForumDeel((int) $id);
		if (!$deel OR !$deel->magLezen()) { // geen exceptie om bestaan van forumdeel niet te verraden
			$this->geentoegang();
		}
		ForumDradenModel::instance()->setHuidigePagina((int) $pagina); // lazy loading ForumDraad[]
		$body = new ForumDeelView($deel);
		if (LoginLid::instance()->hasPermission('P_LOGGED_IN')) {
			$this->view = new CsrLayoutPage($body);
		} else {
			//uitgelogd heeft nieuwe layout
			$this->view = new CsrLayout2Page($body);
		}
		$this->view->addStylesheet('forum.css');
		$this->view->addScript('forum.js');
	}

	/**
	 * Forumdraadje laten zien met alle (zichtbare) posts.
	 * 
	 * @param int $id
	 * @param int $pagina
	 */
	public function onderwerp($id, $pagina = 1) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magLezen()) {
			$this->geentoegang();
		}
		ForumDradenGelezenModel::instance()->setWanneerGelezenDoorLid($draad);
		ForumPostsModel::instance()->setHuidigePagina((int) $pagina); // lazy loading ForumPost[]
		$body = new ForumDraadView($draad, $deel);
		if (LoginLid::instance()->hasPermission('P_LOGGED_IN')) {
			$this->view = new CsrLayoutPage($body);
		} else {
			//uitgelogd heeft nieuwe layout
			$this->view = new CsrLayout2Page($body);
		}
		$this->view->addStylesheet('forum.css');
		$this->view->addScript('forum.js');
	}

	/**
	 * Opzoeken forumdraad van forumpost.
	 * 
	 * @param int $id
	 */
	public function reactie($id) {
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
	public function wijzigen($id, $property, $value = null) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magModereren()) {
			$this->geentoegang();
		}
		if (in_array($property, array('verwijderd', 'gesloten', 'plakkerig', 'belangrijk'))) {
			$value = !$draad->$property;
		} elseif ($property === 'forum_id') {
			$value = (int) filter_input(INPUT_POST, $property, FILTER_SANITIZE_NUMBER_INT);
			if (!ForumDelenModel::instance()->bestaatForumDeel($value)) {
				throw new Exception('Forum bestaat niet!');
			}
		} else if ($property === 'titel') {
			$value = trim(filter_input(INPUT_POST, $property, FILTER_SANITIZE_STRIPPED));
		} else {
			$this->geentoegang();
		}
		$rowcount = ForumDradenModel::instance()->wijzigForumDraad($draad, $property, $value);
		if ($rowcount !== 1) {
			throw new Exception('Wijzigen mislukt');
		} else {
			if (is_bool($value)) {
				$wijziging = ($value ? 'wel ' : 'niet ') . $property;
			} else {
				$wijziging = $property . ' = ' . $value;
			}
			setMelding('Wijziging geslaagd: ' . $wijziging, 1);
		}
		if ($property === 'verwijderd') {
			ForumDradenModel::instance()->hertellenVoorDeel($deel);
		}
		$this->forumdraad($draad->draad_id);
	}

	/**
	 * Forum post toevoegen en evt. nieuw draadje aanmaken.
	 * 
	 * @param int $forum_id
	 * @param int $draad_id
	 */
	public function posten($forum_id, $draad_id = null) {
		$deel = ForumDelenModel::instance()->getForumDeel((int) $forum_id);
		if (!$deel->magPosten()) {
			$this->geentoegang();
		}
		$tekst = filter_input(INPUT_POST, 'bericht', FILTER_UNSAFE_RAW);
		$_SESSION['forum_concept'] = $tekst;
		require_once 'simplespamfilter.class.php';
		$filter = new SimpleSpamfilter();
		if ($filter->isSpam($tekst)) {
			invokeRefresh('/forum/deel/' . $deel->forum_id, 'SPAM', -1); //TODO: logging
		}
		// voorkomen dubbelposts
		if (array_key_exists('forum_laatste_post_tekst', $_SESSION) AND $_SESSION['forum_laatste_post_tekst'] === $tekst) {
			invokeRefresh('/forum/deel/' . $deel->forum_id, 'Bericht is al gepost!', 0);
		}
		$wacht_goedkeuring = false;
		if (!LoginLid::instance()->hasPermission('P_LOGGED_IN')) {
			$wacht_goedkeuring = true;
			$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
			if ($filter->isSpam($email)) {
				invokeRefresh('/forum/deel/' . $deel->forum_id, 'SPAM', -1); //TODO: logging
			}
			if (!email_like($email)) {
				invokeRefresh('/forum/deel/' . $deel->forum_id, 'U moet een geldig email-adres opgeven!', -1);
			}
		}
		if ($draad_id !== null) { // post in bestaand draadje
			$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
			if ($draad->gesloten OR $draad->forum_id !== $deel->forum_id) {
				$this->geentoegang();
			}
		} else { // post in nieuw draadje
			$draad = ForumDradenModel::instance()->maakForumDraad($deel->forum_id, trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING)));
			$deel->aantal_draden++;
		}
		$post = ForumPostsModel::instance()->maakForumPost($draad->draad_id, $tekst, $_SERVER['REMOTE_ADDR'], $wacht_goedkeuring);
		$_SESSION['forum_laatste_post_tekst'] = $tekst;
		$_SESSION['forum_concept'] = '';
		$draad->aantal_posts++;
		$draad->laatst_gewijzigd = $post->datum_tijd;
		$draad->laatste_post_id = $post->post_id;
		$draad->laatste_lid_id = $post->lid_id;
		ForumDradenModel::instance()->update($draad);
		$deel->aantal_posts++;
		ForumDelenModel::instance()->update($deel);
		if ($wacht_goedkeuring) {
			setMelding('Uw bericht is opgeslagen en zal als het goedgekeurd is geplaatst worden.', 1);
			//bericht sturen naar pubcie@csrdelft dat er een bericht op goedkeuring wacht
			mail('pubcie@csrdelft.nl', 'Nieuw bericht wacht op goedkeuring', "http://csrdelft.nl/forum/wacht#" . $post->post_id . "\r\n" . "\r\nDe inhoud van het bericht is als volgt: \r\n\r\n" . str_replace('\r\n', "\n", $tekst) . "\r\n\r\nEINDE BERICHT", "From: pubcie@csrdelft.nl\nReply-To: " . $email);
			invokeRefresh('/forum/deel/' . $deel->forum_id);
		}
		// redirect naar (altijd) juiste pagina
		invokeRefresh('/forum/reactie/' . $post->post_id . '#' . $post->post_id); // , ($draad_id === null ? 'Draad' : 'Post') . ' succesvol toegevoegd', 1
	}

	public function bewerken($id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (($deel->magPosten() AND !$draad->gesloten AND $post->lid_id === LoginLid::instance()->getUid() AND LoginLid::instance()->hasPermission('P_LOGGED_IN')) OR $deel->magModereren()) {
			// same if-statement in post_lijst.tpl
		} else {
			$this->geentoegang();
		}
		$tekst = filter_input(INPUT_POST, 'bericht', FILTER_UNSAFE_RAW);
		$reden = trim(filter_input(INPUT_POST, 'reden', FILTER_SANITIZE_STRING));
		ForumPostsModel::instance()->bewerkForumPost($post, $tekst, $reden);
		// redirect naar (altijd) juiste pagina
		invokeRefresh('/forum/reactie/' . $post->post_id . '#' . $post->post_id); // , 'Post succesvol bewerkt', 1
	}

	public function verwijderen($id) {
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
		ForumPostsModel::instance()->hertellenVoorDraad($draad);
		$this->view = new ForumPostDeleteView($post->post_id);
	}

	public function offtopic($id) {
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

	public function goedkeuren($id) {
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
		if ($draad->wacht_goedkeuring AND $draad->aantal_posts === 1) {
			$draad->wacht_goedkeuring = false;
			$draad->laatst_gewijzigd = $post->laatst_bewerkt;
			$draad->aantal_posts++;
			$rowcount = ForumDradenModel::instance()->update($draad);
			if ($rowcount !== 1) {
				throw new Exception('Goedkeuren mislukt');
			}
		}
		$this->view = new ForumPostView($post, $draad, $deel);
	}

	public function citeren($id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magLezen()) {
			$this->geentoegang();
		}
		echo ForumPostsModel::instance()->citeerForumPost($post);
		exit;
	}

	public function tekst($id) {
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
