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
		parent::__construct($query, null);
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		if ($this->action === 'rss.xml') {
			$this->action = 'rss';
		}
		if (!isset($_SESSION['forum_concept'])) {
			$_SESSION['forum_concept'] = '';
		}
		try {
			parent::performAction($this->getParams(3));
		} catch (Exception $e) {
			SimpleHTML::setMelding($e->getMessage(), -1);
			$this->action = 'forum';
			parent::performAction(array());
		}
		if (!$this->isPosted() || $this->action == 'wijzigen' || $this->action == 'zoeken') {
			if (LoginModel::mag('P_LOGGED_IN')) {
				$this->view = new CsrLayoutPage($this->getView());
			} else { // uitgelogd heeft nieuwe layout
				$this->view = new CsrLayout2Page($this->getView());
			}
			$this->view->addScript('/layout/js/forum');
		}
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function mag($action) {
		switch ($action) {
			case 'zoeken':
				return true;

			case 'rss':
			case 'recent':
			case 'belangrijk':
			case 'deel':
			case 'onderwerp':
			case 'reactie':
			case 'wacht':
				return !$this->isPosted();

			case 'aanmaken':
			case 'beheren':
			case 'opheffen':
			case 'hertellen':
				if (!LoginModel::mag('P_FORUM_ADMIN')) {
					return false;
				}
			case 'wijzigen':
			case 'posten':
			case 'bewerken':
			case 'verwijderen':
			case 'verplaatsen':
			case 'afsplitsen':
			case 'offtopic':
			case 'goedkeuren':
			case 'citeren':
			case 'tekst':
			case 'verbergen':
			case 'tonen':
			case 'toonalles':
			case 'volgenaan':
			case 'volgenuit':
			case 'volgniets':
			case 'concept':
				return $this->isPosted();

			default:
				$this->action = 'forum';
				return true;
		}
	}

	/**
	 * Concept bericht opslaan
	 */
	public function concept() {
		$_SESSION['forum_concept'] = trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW));
		$this->view = new JsonResponse(true);
	}

	/**
	 * Overzicht met categorien en forumdelen laten zien.
	 */
	public function forum() {
		$this->view = new ForumView(ForumModel::instance()->getForum());
	}

	/**
	 * RSS feed van recente draadjes tonen.
	 */
	public function rss() {
		header('Content-Type: application/rss+xml; charset=UTF-8');
		header('Content-Disposition: attachment; filename="rss.xml"');
		$draden_delen = ForumDradenModel::instance()->getRssForumDradenEnDelen();
		$this->view = new ForumRssView($draden_delen[0], $draden_delen[1]);
	}

	/**
	 * Tonen van alle posts die wachten op goedkeuring.
	 */
	public function wacht() {
		$draden_delen = ForumDelenModel::instance()->getWachtOpGoedkeuring();
		$this->view = new ForumResultatenView($draden_delen[0], $draden_delen[1]);
	}

	/**
	 * Tonen van alle posts die wachten op goedkeuring.
	 * 
	 * @param string $query
	 * @param int $pagina
	 */
	public function zoeken($query = null, $pagina = 1) {
		ForumPostsModel::instance()->setHuidigePagina((int) $pagina, 0);
		ForumDradenModel::instance()->setHuidigePagina((int) $pagina, 0);
		if ($query === null) {
			$zoekform = new ForumZoekenForm();
			$values = $zoekform->getValues();
			$query = $values['zoekopdracht'];
			$titel = $values['alleentitel'];
			$datum = $values['datumsoort'];
			$ouder = $values['ouderjonger'];
			$jaar = $values['jaaroud'];
		} else {
			$query = urldecode($query);
			$query = filter_var($query, FILTER_SANITIZE_SPECIAL_CHARS);
		}
		$draden_delen = ForumDelenModel::instance()->zoeken($query, $titel, $datum, $ouder, $jaar);
		$this->view = new ForumResultatenView($draden_delen[0], $draden_delen[1], $query);
	}

	/**
	 * Recente draadjes laten zien in tabel.
	 * 
	 * @param int $pagina
	 * @param string $belangrijk
	 */
	public function recent($pagina = 1, $belangrijk = null) {
		ForumDradenModel::instance()->setHuidigePagina((int) $pagina, 0);
		if ($belangrijk === 'belangrijk' OR $pagina === 'belangrijk') {
			$belangrijk = true;
		} else {
			$belangrijk = null;
		}
		$deel = ForumDelenModel::instance()->getRecent($belangrijk);
		$this->view = new ForumDeelView($deel, true, $belangrijk);
	}

	/**
	 * Shortcut to /recent/1/belangrijk.
	 * 
	 * @param int $pagina
	 */
	public function belangrijk($pagina = 1) {
		$this->recent($pagina, $this->action);
	}

	/**
	 * Deelforum laten zien met draadjes in tabel.
	 * 
	 * @param int $forum_id
	 * @param int $pagina or 'laatste' or 'prullenbak'
	 */
	public function deel($forum_id, $pagina = 1) {
		$deel = ForumDelenModel::instance()->getForumDeel((int) $forum_id);
		if (!$deel->magLezen()) {
			$this->geentoegang();
		}
		$paging = true;
		if ($pagina === 'laatste') {
			ForumDradenModel::instance()->setLaatstePagina($deel->forum_id);
		} elseif ($pagina === 'prullenbak' AND $deel->magModereren()) {
			$deel->setForumDraden(ForumDradenModel::instance()->getPrullenbakVoorDeel($deel));
			$paging = false;
		} elseif ($pagina === 'belangrijk' AND $deel->magLezen()) {
			$deel->setForumDraden(ForumDradenModel::instance()->getBelangrijkeForumDradenVoorDeel($deel));
			$paging = false;
		} else {
			ForumDradenModel::instance()->setHuidigePagina((int) $pagina, $deel->forum_id);
		}
		$this->view = new ForumDeelView($deel, $paging); // lazy loading ForumDraad[]
	}

	/**
	 * Forumdraadje laten zien met alle zichtbare/verwijderde posts.
	 * 
	 * @param int $draad_id
	 * @param int $pagina or 'laatste' or 'ongelezen'
	 */
	public function onderwerp($draad_id, $pagina = null) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magLezen()) {
			$this->geentoegang();
		}
		$gelezen = $draad->getWanneerGelezen(); // laad gelezen voordat database geupdate wordt
		if ($pagina === null) {
			$pagina = LidInstellingen::get('forum', 'open_draad_op_pagina');
		}
		$paging = true;
		if ($pagina === 'ongelezen' AND $gelezen) {
			ForumPostsModel::instance()->setPaginaVoorLaatstGelezen($gelezen);
		} elseif ($pagina === 'laatste') {
			ForumPostsModel::instance()->setLaatstePagina($draad->draad_id);
		} elseif ($pagina === 'prullenbak' AND $deel->magModereren()) {
			$draad->setForumPosts(ForumPostsModel::instance()->getPrullenbakVoorDraad($draad));
			$paging = false;
		} else {
			ForumPostsModel::instance()->setHuidigePagina((int) $pagina, $draad->draad_id);
		}
		ForumDradenGelezenModel::instance()->setWanneerGelezenDoorLid($draad);
		$this->view = new ForumDraadView($draad, $deel, $paging); // lazy loading ForumPost[]
	}

	/**
	 * Opzoeken forumdraad van forumpost.
	 * 
	 * @param int $post_id
	 */
	public function reactie($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		if ($post->verwijderd) {
			SimpleHTML::setMelding('Deze reactie is verwijderd', 0);
		}
		$this->onderwerp($post->draad_id, ForumPostsModel::instance()->getPaginaVoorPost($post));
	}

	/**
	 * Forum deel aanmaken.
	 */
	public function aanmaken() {
		$deel = ForumDelenModel::instance()->maakForumDeel();
		$this->beheren($deel->forum_id);
	}

	/**
	 * Forum deel wijzigen.
	 * 
	 * @param int $forum_id
	 */
	public function beheren($forum_id) {
		$deel = ForumDelenModel::instance()->getForumDeel((int) $forum_id);
		$this->view = new ForumDeelForm($deel); // fetches POST values itself
		if ($this->view->validate()) {
			$rowcount = ForumDelenModel::instance()->update($deel);
			if ($rowcount !== 1) {
				throw new Exception('Forum beheren mislukt!');
			}
			$this->view = new JsonResponse(true);
		}
	}

	/**
	 * Forum deel verwijderen.
	 * 
	 * @param int $forum_id
	 */
	public function opheffen($forum_id) {
		$deel = ForumDelenModel::instance()->getForumDeel((int) $forum_id);
		if (ForumDradenModel::instance()->exist('forum_id = ?', array($deel->forum_id))) {
			SimpleHTML::setMelding('Verwijder eerst alle draadjes van dit deelforum uit de database!', -1);
		} else {
			ForumDelenModel::instance()->verwijderForumDeel($deel->forum_id);
			SimpleHTML::setMelding('Deelforum verwijderd', 1);
		}
		$this->view = new JsonResponse(true);
	}

	/**
	 * Hertellen van alle berichten en draden in forum deel.
	 */
	public function hertellen($forum_id) {
		$deel = ForumDelenModel::instance()->getForumDeel((int) $forum_id);
		$draden = ForumDradenModel::instance()->find('forum_id = ?', array($deel->forum_id));
		foreach ($draden as $draad) {
			ForumPostsModel::instance()->hertellenVoorDraadEnDeel($draad, $deel);
		}
		$this->view = new JsonResponse(true);
	}

	/**
	 * Forum draad verbergen in zijbalk.
	 * 
	 * @param int $draad_id
	 */
	public function verbergen($draad_id) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
		if (!$draad->magVerbergen()) {
			throw new Exception('Onderwerp mag niet verborgen worden');
		}
		if ($draad->isVerborgen()) {
			throw new Exception('Onderwerp is al verborgen');
		}
		ForumDradenVerbergenModel::instance()->setVerbergenVoorLid($draad);
		$this->view = new JsonResponse(true);
	}

	/**
	 * Forum draad tonen in zijbalk.
	 * 
	 * @param int $draad_id
	 */
	public function tonen($draad_id) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
		if (!$draad->isVerborgen()) {
			throw new Exception('Onderwerp is niet verborgen');
		}
		ForumDradenVerbergenModel::instance()->setVerbergenVoorLid($draad, false);
		$this->view = new JsonResponse(true);
	}

	/**
	 * Forum draden die verborgen zijn door lid weer tonen.
	 */
	public function toonalles() {
		$aantal = ForumDradenVerbergenModel::instance()->getAantalVerborgenVoorLid();
		ForumDradenVerbergenModel::instance()->toonAllesVoorLid(LoginModel::getUid());
		SimpleHTML::setMelding($aantal . ' onderwerp' . ($aantal === 1 ? ' wordt' : 'en worden') . ' weer getoond in de zijbalk', 1);
		$this->view = new JsonResponse(true);
	}

	/**
	 * Forum draad volgen per email.
	 * 
	 * @param int $draad_id
	 */
	public function volgenaan($draad_id) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
		if (!$draad->magVolgen()) {
			throw new Exception('Onderwerp mag niet gevolgd worden');
		}
		if ($draad->isGevolgd()) {
			throw new Exception('Onderwerp wordt al gevolgd');
		}
		ForumDradenVolgenModel::instance()->setVolgenVoorLid($draad);
		$this->view = new JsonResponse(true);
	}

	/**
	 * Forum draad niet meer volgen.
	 * 
	 * @param int $draad_id
	 */
	public function volgenuit($draad_id) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
		if (!$draad->isGevolgd()) {
			throw new Exception('Onderwerp wordt niet gevolgd');
		}
		ForumDradenVolgenModel::instance()->setVolgenVoorLid($draad, false);
		$this->view = new JsonResponse(true);
	}

	/**
	 * Forum draden die gevolgd worden door lid niet meer volgen.
	 */
	public function volgniets() {
		$aantal = ForumDradenVolgenModel::instance()->getAantalVolgenVoorLid();
		ForumDradenVolgenModel::instance()->volgNietsVoorLid(LoginModel::getUid());
		SimpleHTML::setMelding($aantal . ' onderwerp' . ($aantal === 1 ? ' wordt' : 'en worden') . ' niet meer gevolgd', 1);
		$this->view = new JsonResponse(true);
	}

	/**
	 * Wijzig een eigenschap van een draadje.
	 * 
	 * @param int $draad_id
	 * @param string $property
	 * @param mixed $value
	 * @throws Exception indien forum niet bestaat bij verplaatsen of wijzigen mislukt
	 */
	public function wijzigen($draad_id, $property, $value = null) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magModereren()) {
			$this->geentoegang();
		}
		if (in_array($property, array('verwijderd', 'gesloten', 'plakkerig', 'belangrijk', 'eerste_post_plakkerig'))) {
			$value = !$draad->$property;
		} elseif ($property === 'forum_id') {
			$value = (int) filter_input(INPUT_POST, $property, FILTER_SANITIZE_NUMBER_INT);
			if (!ForumDelenModel::instance()->bestaatForumDeel($value)) {
				throw new Exception('Forum bestaat niet!');
			}
		} elseif ($property === 'titel') {
			$value = trim(filter_input(INPUT_POST, $property, FILTER_SANITIZE_STRIPPED));
		} else {
			$this->geentoegang();
		}
		if ($property === 'belangrijk') {
			if (LoginModel::mag('P_FORUM_BELANGRIJK')) {
				ForumDradenVerbergenModel::instance()->toonDraadVoorIedereen($draad);
			} else {
				$this->geentoegang();
			}
		} elseif ($property === 'gesloten') {
			ForumDradenVolgenModel::instance()->stopVolgenVoorIedereen($draad);
		}
		ForumDradenModel::instance()->wijzigForumDraad($draad, $property, $value);
		if ($property === 'verwijderd') {
			ForumDradenVolgenModel::instance()->stopVolgenVoorIedereen($draad);
			ForumDradenVerbergenModel::instance()->toonDraadVoorIedereen($draad);
			ForumDradenGelezenModel::instance()->verwijderDraadGelezen($draad);
			ForumPostsModel::instance()->verwijderForumPostsVoorDraad($draad, $deel); // hertellen
		}
		if (is_bool($value)) {
			$wijziging = ($value ? 'wel ' : 'niet ') . $property;
		} else {
			$wijziging = $property . ' = ' . $value;
		}
		SimpleHTML::setMelding('Wijziging geslaagd: ' . $wijziging, 1);
		if ($property === 'forum_id' OR $property === 'titel') {
			$this->onderwerp($draad_id);
		} else {
			$this->view = new JsonResponse(true);
		}
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
		$spamtrap = filter_input(INPUT_POST, 'firstname', FILTER_UNSAFE_RAW);
		if (!empty($spamtrap)) { //TODO: logging
			SimpleHTML::setMelding('SPAM', -1);
			redirect(CSR_ROOT . '/forum/deel/' . $deel->forum_id);
		}
		$this->concept(); // concept opslaan
		$tekst = $_SESSION['forum_concept'];
		require_once 'simplespamfilter.class.php';
		$filter = new SimpleSpamfilter();
		if ($filter->isSpam($tekst)) { //TODO: logging
			SimpleHTML::setMelding('SPAM', -1);
			redirect(CSR_ROOT . '/forum/deel/' . $deel->forum_id);
		}
		// voorkomen dubbelposts
		if (isset($_SESSION['forum_laatste_post_tekst']) AND $_SESSION['forum_laatste_post_tekst'] === $tekst) {
			$_SESSION['forum_concept'] = '';
			SimpleHTML::setMelding('Uw reactie is al geplaatst', 0);
			redirect(CSR_ROOT . '/forum/deel/' . $deel->forum_id);
		}
		$mailadres = null;
		$wacht_goedkeuring = false;
		if (!LoginModel::mag('P_LOGGED_IN')) {
			$wacht_goedkeuring = true;
			$mailadres = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
			if (!email_like($mailadres)) {
				SimpleHTML::setMelding('U moet een geldig email-adres opgeven!', -1);
				$url = CSR_ROOT . ($draad_id === null ? '/forum/deel/' . $deel->forum_id : '/forum/onderwerp/' . $draad_id);
				redirect($url);
			}
			if ($filter->isSpam($mailadres)) { //TODO: logging
				SimpleHTML::setMelding('SPAM', -1);
				redirect(CSR_ROOT . '/forum/deel/' . $deel->forum_id);
			}
		}
		if ($draad_id !== null) { // post in bestaand draadje
			$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
			if ($draad->gesloten OR $draad->forum_id !== $deel->forum_id) {
				$this->geentoegang();
			}
		} else { // post in nieuw draadje
			$titel = trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING));
			if (empty($titel)) {
				SimpleHTML::setMelding('U moet een titel opgeven!', -1);
				redirect(CSR_ROOT . '/forum/deel/' . $deel->forum_id);
			}
			$draad = ForumDradenModel::instance()->maakForumDraad($deel->forum_id, $titel, $wacht_goedkeuring);
		}
		$post = ForumPostsModel::instance()->maakForumPost($draad->draad_id, $tekst, $_SERVER['REMOTE_ADDR'], $wacht_goedkeuring, $mailadres);
		$_SESSION['forum_laatste_post_tekst'] = $tekst;
		$_SESSION['forum_concept'] = '';
		ForumDradenGelezenModel::instance()->setWanneerGelezenDoorLid($draad);
		if ($wacht_goedkeuring) {
			SimpleHTML::setMelding('Uw bericht is opgeslagen en zal als het goedgekeurd is geplaatst worden.', 1);
			//bericht sturen naar pubcie@csrdelft dat er een bericht op goedkeuring wacht
			mail('pubcie@csrdelft.nl', 'Nieuw bericht wacht op goedkeuring', "http://csrdelft.nl/forum/onderwerp/" . $draad->draad_id . "/wacht#" . $post->post_id . "\r\n" . "\r\nDe inhoud van het bericht is als volgt: \r\n\r\n" . str_replace('\r\n', "\n", $tekst) . "\r\n\r\nEINDE BERICHT", "From: pubcie@csrdelft.nl\nReply-To: " . $mailadres);
			redirect(CSR_ROOT . '/forum/deel/' . $deel->forum_id);
		} else {
			ForumPostsModel::instance()->goedkeurenForumPost($post, $draad, $deel);
			foreach ($draad->getVolgers() as $uid) {
				require_once 'MVC/model/entity/Mail.class.php';
				$bericht = "[url]http://csrdelft.nl/forum/reactie/" . $post->post_id . "#" . $post->post_id . "[/url]\r\n" . "\r\nDe inhoud van het bericht is als volgt: \r\n\r\n" . str_replace('\r\n', "\n", $tekst) . "\r\n\r\nEINDE BERICHT";
				$mail = new Mail(array($uid . '@csrdelft.nl' => Lid::naamLink($uid, 'civitas', 'plain')), 'C.S.R. Forum: nieuwe reactie op ' . $draad->titel, $bericht);
				$mail->setReplyTo('no-reply@csrdelft.nl');
				$mail->send();
			}
			SimpleHTML::setMelding(($draad_id === null ? 'Draad' : 'Post') . ' succesvol toegevoegd', 1);
		}
		// redirect naar (altijd) juiste pagina
		redirect(CSR_ROOT . '/forum/reactie/' . $post->post_id . '#' . $post->post_id);
	}

	public function bewerken($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (($deel->magPosten() AND ! $draad->gesloten AND $post->uid === LoginModel::getUid() AND LoginModel::mag('P_LOGGED_IN')) OR $deel->magModereren()) {
			// same if-statement in post_lijst.tpl
		} else {
			$this->geentoegang();
		}
		$tekst = trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW));
		$reden = trim(filter_input(INPUT_POST, 'reden', FILTER_SANITIZE_STRING));
		ForumPostsModel::instance()->bewerkForumPost($tekst, $reden, $post, $draad, $deel);
		ForumDradenGelezenModel::instance()->setWanneerGelezenDoorLid($draad);
		$this->view = new ForumPostView($post, $draad, $deel);
	}

	public function verplaatsen($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magModereren()) {
			$this->geentoegang();
		}
		$nieuw = filter_input(INPUT_POST, 'Draad_id', FILTER_SANITIZE_NUMBER_INT);
		$nieuw = ForumDradenModel::instance()->getForumDraad($nieuw);
		ForumPostsModel::instance()->verplaatsForumPost($nieuw, $post, $draad, $deel);
		$this->view = new ForumPostDeleteView($post->post_id);
	}

	public function afsplitsen($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magModereren()) {
			$this->geentoegang();
		}
		$nieuw = filter_input(INPUT_POST, 'Naam_van_nieuwe_draad', FILTER_SANITIZE_STRING);
		ForumPostsModel::instance()->afsplitsenForumPost($nieuw, $post, $draad, $deel);
		$this->view = new ForumPostDeleteView($post->post_id);
	}

	public function verwijderen($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magModereren()) {
			$this->geentoegang();
		}
		ForumPostsModel::instance()->verwijderForumPost($post, $draad, $deel);
		$this->view = new ForumPostDeleteView($post->post_id);
	}

	public function offtopic($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magModereren()) {
			$this->geentoegang();
		}
		ForumPostsModel::instance()->offtopicForumPost($post);
		$this->view = new ForumPostView($post, $draad, $deel);
	}

	public function goedkeuren($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magModereren()) {
			$this->geentoegang();
		}
		ForumPostsModel::instance()->goedkeurenForumPost($post, $draad, $deel);
		$this->view = new ForumPostView($post, $draad, $deel);
	}

	public function citeren($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magLezen()) {
			$this->geentoegang();
		}
		echo ForumPostsModel::instance()->citeerForumPost($post);
		exit; //TODO: JsonResponse
	}

	public function tekst($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magLezen()) {
			$this->geentoegang();
		}
		echo $post->tekst;
		exit; //TODO: JsonResponse
	}

}
