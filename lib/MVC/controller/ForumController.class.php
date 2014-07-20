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

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		if ($this->action === 'rss.xml') {
			$this->action = 'rss';
			header('Content-Disposition: attachment; filename="rss.xml"');
		}
		if (!isset($_SESSION['forum_concept'])) {
			$_SESSION['forum_concept'] = '';
		}
		try {
			parent::performAction($this->getParams(3));
		} catch (Exception $e) {
			setMelding($e->getMessage(), -1);
			$this->action = 'forum';
			parent::performAction(array());
		}
		if (!$this->isPosted() || $this->action == 'wijzigen' || $this->action == 'zoeken') {
			if (LoginLid::mag('P_LOGGED_IN')) {
				$this->view = new CsrLayoutPage($this->getContent());
			} else { // uitgelogd heeft nieuwe layout
				$this->view = new CsrLayout2Page($this->getContent());
			}
			$this->view->addScript('forum.js');
		}
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function hasPermission() {
		switch ($this->action) {
			case 'wijzigen':
			case 'zoeken':
				return true;

			case 'hertellen':
				if (!LoginLid::mag('P_ADMIN')) {
					return false;
				}
			case 'rss':
			case 'recent':
			case 'deel':
			case 'onderwerp':
			case 'reactie':
			case 'wacht':
			case 'herstel':
				return !$this->isPosted();

			case 'aanmaken':
			case 'beheren':
			case 'opheffen':
				if (!LoginLid::mag('P_ADMIN')) {
					return false;
				}
			case 'posten':
			case 'bewerken':
			case 'verwijderen':
			case 'verplaatsen':
			case 'afsplitsen':
			case 'offtopic':
			case 'goedkeuren':
			case 'citeren':
			case 'tekst':
			case 'optout':
			case 'optin':
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
		$this->view = new ForumView(ForumModel::instance()->getForum());
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
		$draden_delen = ForumDelenModel::instance()->getWachtOpGoedkeuring();
		$this->view = new ForumResultatenView($draden_delen[0], $draden_delen[1]);
	}

	/**
	 * Hertellen van het gehele forum.
	 */
	public function hertellen() {
		$categorien = ForumModel::instance()->find();
		foreach ($categorien as $cat) {
			$delen = ForumDelenModel::instance()->getForumDelenVoorCategorie($cat->categorie_id);
			foreach ($delen as $deel) {
				$draden = ForumDradenModel::instance()->getForumDradenVoorDeel($deel->forum_id);
				foreach ($draden as $draad) {
					ForumPostsModel::instance()->hertellenVoorDraadEnDeel($draad, $deel);
				}
			}
		}
		$this->forum();
	}

	/**
	 * Tonen van alle posts die wachten op goedkeuring.
	 * 
	 * @param string $query
	 * @param int $pagina
	 */
	public function zoeken($query = null, $pagina = 1) {
		if ($query === null) {
			$query = filter_input(INPUT_POST, 'zoeken', FILTER_SANITIZE_SPECIAL_CHARS);
		} else {
			$query = urldecode($query);
			$query = filter_var($query, FILTER_SANITIZE_SPECIAL_CHARS);
		}
		ForumPostsModel::instance()->setHuidigePagina((int) $pagina, 0);
		ForumDradenModel::instance()->setHuidigePagina((int) $pagina, 0);
		$draden_delen = ForumDelenModel::instance()->zoeken($query);
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
		$belangrijk = ($belangrijk === 'belangrijk' ? true : null);
		$deel = ForumDelenModel::instance()->getRecent($belangrijk);
		$this->view = new ForumDeelView($deel, $belangrijk);
	}

	/**
	 * Deelforum laten zien met draadjes in tabel.
	 * 
	 * @param int $forum_id
	 * @param int $pagina or 'laatste'
	 */
	public function deel($forum_id, $pagina = 1) {
		$deel = ForumDelenModel::instance()->getForumDeel((int) $forum_id);
		if (!$deel OR ! $deel->magLezen()) { // geen exceptie om bestaan van forumdeel niet te verraden
			$this->geentoegang();
		}
		if ($pagina === 'laatste') {
			ForumDradenModel::instance()->setLaatstePagina($deel->forum_id); // lazy loading ForumDraad[]
		} else {
			ForumDradenModel::instance()->setHuidigePagina((int) $pagina, $deel->forum_id); // lazy loading ForumDraad[]
		}
		$this->view = new ForumDeelView($deel);
	}

	/**
	 * Forumdraadje laten zien met alle (zichtbare) posts.
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
		$gelezen = ForumDradenGelezenModel::instance()->getWanneerGelezenDoorLid($draad);
		if ($gelezen) {
			$draad->setWanneerGelezen($gelezen); // laad gelezen voordat database geupdate wordt
		}
		ForumDradenGelezenModel::instance()->setWanneerGelezenDoorLid($draad); // update database
		if ($pagina === null) {
			$pagina = LidInstellingen::get('forum', 'open_draad_op_pagina');
		}
		if ($pagina === 'ongelezen' AND $gelezen) {
			ForumPostsModel::instance()->setPaginaVoorLaatstGelezen($gelezen);
		} elseif ($pagina === 'laatste') {
			ForumPostsModel::instance()->setLaatstePagina($draad->draad_id);
		} else {
			ForumPostsModel::instance()->setHuidigePagina((int) $pagina, $draad->draad_id);
		}
		$this->view = new ForumDraadView($draad, $deel); // lazy loading ForumPost[]
	}

	/**
	 * Opzoeken forumdraad van forumpost.
	 * 
	 * @param int $post_id
	 */
	public function reactie($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		if ($post->verwijderd) {
			setMelding('Deze reactie is verwijderd', 0);
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
			// ReloadPage
		}
	}

	/**
	 * Forum deel verwijderen.
	 * 
	 * @param int $forum_id
	 */
	public function opheffen($forum_id) {
		if (ForumDradenModel::instance()->exist('forum_id = ?', array((int) $forum_id))) {
			setMelding('Verwijder eerst alle draadjes van dit deelforum uit de database!', -1);
		} else {
			setMelding('Deelforum verwijderd', 1);
			ForumDelenModel::instance()->verwijderForumDeel((int) $forum_id);
		}
		// ReloadPage
	}

	/**
	 * Forum draad verbergen in zijbalk.
	 * 
	 * @param int $draad_id
	 */
	public function optout($draad_id) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
		if (!$draad->magVerbergen()) {
			throw new Exception('Kan niet verbergen: onderwerp is aangemerkt als belangrijk');
		}
		if ($draad->isVerborgen()) {
			throw new Exception('Onderwerp is al verborgen');
		}
		ForumDradenVerbergenModel::instance()->setVerbergenVoorLid($draad);
		$this->view = new ForumDraadVerbergenView($draad->draad_id);
	}

	/**
	 * Forum draad tonen in zijbalk.
	 * 
	 * @param int $draad_id
	 */
	public function optin($draad_id) {
		$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
		if (!$draad->isVerborgen()) {
			throw new Exception('Onderwerp is niet verborgen');
		}
		ForumDradenVerbergenModel::instance()->setVerbergenVoorLid($draad, false);
		// ReloadPage
	}

	/**
	 * Forum draden die verborgen waren door lid weer tonen.
	 */
	public function herstel() {
		$aantal = ForumDradenVerbergenModel::instance()->getAantalVerborgenVoorLid();
		ForumDradenVerbergenModel::instance()->herstelAlleDradenVoorLid();
		setMelding($aantal . ' onderwerpen worden weer getoond in de zijbalk', 1);
		$this->recent();
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
		ForumDradenModel::instance()->wijzigForumDraad($draad, $property, $value);
		if ($property === 'verwijderd') {
			ForumPostsModel::instance()->verwijderForumPostsVoorDraad($draad, $deel);
		} elseif ($property === 'belangrijk') {
			ForumDradenVerbergenModel::instance()->herstelDraadVoorIedereen($draad);
		}
		if (is_bool($value)) {
			$wijziging = ($value ? 'wel ' : 'niet ') . $property;
		} else {
			$wijziging = $property . ' = ' . $value;
		}
		setMelding('Wijziging geslaagd: ' . $wijziging, 1);
		$this->onderwerp($draad->draad_id);
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
		if (!empty($spamtrap)) {
			invokeRefresh('/forum/deel/' . $deel->forum_id, 'SPAM', -1); //TODO: logging
		}
		$tekst = trim(filter_input(INPUT_POST, 'bericht', FILTER_UNSAFE_RAW));
		$_SESSION['forum_concept'] = $tekst;
		require_once 'simplespamfilter.class.php';
		$filter = new SimpleSpamfilter();
		if ($filter->isSpam($tekst)) {
			invokeRefresh('/forum/deel/' . $deel->forum_id, 'SPAM', -1); //TODO: logging
		}
		// voorkomen dubbelposts
		if (isset($_SESSION['forum_laatste_post_tekst']) AND $_SESSION['forum_laatste_post_tekst'] === $tekst) {
			$_SESSION['forum_concept'] = '';
			invokeRefresh('/forum/deel/' . $deel->forum_id, 'Uw reactie is al geplaatst', 0);
		}
		$email = null;
		$wacht_goedkeuring = false;
		if (!LoginLid::mag('P_LOGGED_IN')) {
			$wacht_goedkeuring = true;
			$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
			if (!email_like($email)) {
				$url = ($draad_id === null ? '/forum/deel/' . $deel->forum_id : '/forum/onderwerp/' . $draad_id);
				invokeRefresh($url, 'U moet een geldig email-adres opgeven!', -1);
			}
			if ($filter->isSpam($email)) {
				invokeRefresh('/forum/deel/' . $deel->forum_id, 'SPAM', -1); //TODO: logging
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
				invokeRefresh('/forum/deel/' . $deel->forum_id, 'U moet een titel opgeven!', -1);
			}
			$draad = ForumDradenModel::instance()->maakForumDraad($deel->forum_id, $titel, $wacht_goedkeuring);
		}
		$post = ForumPostsModel::instance()->maakForumPost($draad->draad_id, $tekst, $_SERVER['REMOTE_ADDR'], $wacht_goedkeuring, $email);
		$_SESSION['forum_laatste_post_tekst'] = $tekst;
		$_SESSION['forum_concept'] = '';
		ForumDradenGelezenModel::instance()->setWanneerGelezenDoorLid($draad);
		if ($wacht_goedkeuring) {
			setMelding('Uw bericht is opgeslagen en zal als het goedgekeurd is geplaatst worden.', 1);
			//bericht sturen naar pubcie@csrdelft dat er een bericht op goedkeuring wacht
			mail('pubcie@csrdelft.nl', 'Nieuw bericht wacht op goedkeuring', "http://csrdelft.nl/forum/wacht#" . $post->post_id . "\r\n" . "\r\nDe inhoud van het bericht is als volgt: \r\n\r\n" . str_replace('\r\n', "\n", $tekst) . "\r\n\r\nEINDE BERICHT", "From: pubcie@csrdelft.nl\nReply-To: " . $email);
			invokeRefresh('/forum/deel/' . $deel->forum_id);
		} else {
			ForumPostsModel::instance()->goedkeurenForumPost($post, $draad, $deel);
		}
		// redirect naar (altijd) juiste pagina
		invokeRefresh('/forum/reactie/' . $post->post_id . '#' . $post->post_id); // , ($draad_id === null ? 'Draad' : 'Post') . ' succesvol toegevoegd', 1
	}

	public function bewerken($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (($deel->magPosten() AND ! $draad->gesloten AND $post->lid_id === LoginLid::instance()->getUid() AND LoginLid::mag('P_LOGGED_IN')) OR $deel->magModereren()) {
			// same if-statement in post_lijst.tpl
		} else {
			$this->geentoegang();
		}
		$tekst = trim(filter_input(INPUT_POST, 'bericht', FILTER_UNSAFE_RAW));
		$reden = trim(filter_input(INPUT_POST, 'reden', FILTER_SANITIZE_STRING));
		$verschil = levenshtein($post->tekst, $tekst);
		$rowcount = ForumPostsModel::instance()->bewerkForumPost($post, $tekst, $reden);
		if ($rowcount !== 1) {
			throw new Exception('Bewerken mislukt');
		}
		if ($verschil > 3) {
			$draad->laatst_gewijzigd = $post->laatst_bewerkt;
			$draad->laatste_post_id = $post->post_id;
			$draad->laatste_lid_id = $post->lid_id;
			$rowcount = ForumDradenModel::instance()->update($draad);
			if ($rowcount !== 1) {
				throw new Exception('Bewerken mislukt');
			}
			$deel->laatst_gewijzigd = $post->laatst_bewerkt;
			$deel->laatste_post_id = $post->post_id;
			$deel->laatste_lid_id = $post->lid_id;
			$rowcount = ForumDelenModel::instance()->update($deel);
			if ($rowcount !== 1) {
				throw new Exception('Bewerken mislukt');
			}
		}
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
		ForumPostsModel::instance()->verplaatsForumPost($post, $nieuw);
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
		$draad = ForumPostsModel::instance()->afsplitsenForumPost($post, $nieuw, $deel->forum_id);
		ForumPostsModel::instance()->goedkeurenForumPost($post, $draad, $deel);
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
		exit;
	}

	public function tekst($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!$deel->magLezen()) {
			$this->geentoegang();
		}
		echo $post->tekst;
		exit;
	}

}
