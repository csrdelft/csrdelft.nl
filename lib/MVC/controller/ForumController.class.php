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

	/**
	 * Check permissions & valid params in actions.
	 *
	 * @param string $action
	 * @return boolean
	 */
	protected function mag($action, $resource) {
		switch ($action) {
			case 'zoeken':
				return true;

			// Forum
			case 'rss':
			case 'recent':
			case 'belangrijk':
			case 'deel':
			case 'onderwerp':
			case 'reactie':
			case 'wacht':
				return !$this->isPosted();

			// ForumDeel
			case 'aanmaken':
			case 'beheren':
			case 'opheffen':
			case 'hertellen':
				if (!LoginModel::mag('P_FORUM_ADMIN')) {
					return false;
				}

			// ForumPost
			case 'citeren':
				if (!LoginModel::mag('P_LOGGED_IN')) {
					return false;
				}

			// ForumDraad
			case 'wijzigen':
			case 'posten':
			case 'bewerken':
			case 'verwijderen':
			case 'verplaatsen':
			case 'offtopic':
			case 'goedkeuren':

			// ForumPost
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

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		if ($this->action === 'rss.xml') {
			$this->action = 'rss';
		}
		try {
			parent::performAction($this->getParams(3));
		} catch (Exception $e) {
			setMelding($e->getMessage(), -1);
			$this->action = 'forum';
			parent::performAction(array());
		}
		if ((!$this->isPosted() OR $this->action == 'zoeken') AND $this->action != 'rss') {
			if (LoginModel::mag('P_LOGGED_IN')) {
				$this->view = new CsrLayoutPage($this->getView());
				$layoutmap = 'layout';
			} else { // uitgelogd heeft nieuwe layout
				$this->view = new CsrLayout2Page($this->getView());
				$layoutmap = 'layout2';
			}
			$this->view->addStylesheet($this->view->getCompressedStyleUrl($layoutmap, 'forum'), true);
			$this->view->addScript($this->view->getCompressedScriptUrl($layoutmap, 'forum'), true);
		}
	}

	/**
	 * Overzicht met categorien en forumdelen laten zien.
	 */
	public function forum() {
		$this->view = new ForumOverzichtView();
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
			if ($draad->gedeeld_met) {
				$gedeeld = ForumDelenModel::instance()->getForumDeel($draad->gedeeld_met);
				if (!$gedeeld->magLezen()) {
					$this->geentoegang();
				}
			} else {
				$this->geentoegang();
			}
		}
		$gelezen = $draad->getWanneerGelezen(); // laad gelezen voordat database geupdate wordt
		if ($pagina === null) {
			$pagina = LidInstellingen::get('forum', 'open_draad_op_pagina');
		}
		if ($draad->pagina_per_post) {
			ForumPostsModel::instance()->setAantalPerPagina(1);
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
			setMelding('Verwijder eerst alle draadjes van dit deelforum uit de database!', -1);
		} else {
			ForumDelenModel::instance()->verwijderForumDeel($deel->forum_id);
			setMelding('Deelforum verwijderd', 1);
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
		setMelding($aantal . ' onderwerp' . ($aantal === 1 ? ' wordt' : 'en worden') . ' weer getoond in de zijbalk', 1);
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
		setMelding($aantal . ' onderwerp' . ($aantal === 1 ? ' wordt' : 'en worden') . ' niet meer gevolgd', 1);
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
		if (in_array($property, array('verwijderd', 'gesloten', 'plakkerig', 'belangrijk', 'eerste_post_plakkerig', 'pagina_per_post'))) {
			$value = !$draad->$property;
			if ($property === 'belangrijk' AND ! LoginModel::mag('P_FORUM_BELANGRIJK')) {
				$this->geentoegang();
			}
		} elseif ($property === 'forum_id' OR $property === 'gedeeld_met') {
			$value = (int) filter_input(INPUT_POST, $property, FILTER_SANITIZE_NUMBER_INT);
			if ($value !== 0 OR $property === 'forum_id') {
				$deel = ForumDelenModel::instance()->getForumDeel($value);
				if (!$deel->magModereren()) {
					$this->geentoegang();
				}
			} else {
				$value = null;
			}
		} elseif ($property === 'titel') {
			$value = trim(filter_input(INPUT_POST, $property, FILTER_SANITIZE_STRING));
		} else {
			$this->geentoegang();
		}
		ForumDradenModel::instance()->wijzigForumDraad($draad, $property, $value);
		ForumDradenModel::instance()->hertellenVoorDraadEnDeel($draad, $deel);
		if (is_bool($value)) {
			$wijziging = ($value ? 'wel ' : 'niet ') . $property;
		} else {
			$wijziging = $property . ' = ' . $value;
		}
		setMelding('Wijziging geslaagd: ' . $wijziging, 1);
		if ($property === 'forum_id' OR $property === 'titel' OR $property === 'gedeeld_met') {
			redirect(CSR_ROOT . '/forum/onderwerp/' . $draad_id);
		} else {
			$this->view = new JsonResponse(true);
		}
	}

	public static function magPosten(ForumDraad $draad, ForumDeel $deel) {
		if ($draad->verwijderd OR $draad->gesloten) {
			return false;
		}
		if ($deel->magPosten()) {
			return true;
		}
		if ($draad->gedeeld_met AND ForumDelenModel::instance()->getForumDeel($draad->gedeeld_met)->magPosten()) {
			return true;
		}
		return false;
	}

	/**
	 * Forum post toevoegen en evt. nieuw draadje aanmaken.
	 * 
	 * @param int $forum_id
	 * @param int $draad_id
	 */
	public function posten($forum_id, $draad_id = null) {
		// mag posten?
		$deel = ForumDelenModel::instance()->getForumDeel((int) $forum_id);
		if (!$deel->magPosten()) {
			$this->geentoegang();
		}

		// post in bestaand draadje?
		if ($draad_id !== null) {
			$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);

			// mag posten?
			if (!$draad OR $draad->forum_id !== $deel->forum_id OR ! ForumController::magPosten($draad, $deel)) {
				$this->geentoegang();
			}

			$url = CSR_ROOT . '/forum/onderwerp/' . $draad->draad_id;
			$nieuw = false;
		} else {
			$url = CSR_ROOT . '/forum/deel/' . $deel->forum_id;
			$nieuw = true;
		}

		// concept opslaan
		$tekst = trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW));
		if ($nieuw) {
			$titel = trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING));
			ForumDradenReagerenModel::instance()->setConcept($deel, null, $tekst, $titel);
		} else {
			ForumDradenReagerenModel::instance()->setConcept($deel, $draad->draad_id, $tekst);
		}

		// spam controle
		require_once 'simplespamfilter.class.php';
		$filter = new SimpleSpamfilter();
		$spamtrap = filter_input(INPUT_POST, 'firstname', FILTER_UNSAFE_RAW);
		if (!empty($spamtrap) OR $filter->isSpam($tekst)) { //TODO: logging
			setMelding('SPAM', -1);
			redirect(CSR_ROOT . '/forum');
		}

		// voorkom dubbelposts
		if (isset($_SESSION['forum_laatste_post_tekst']) AND $_SESSION['forum_laatste_post_tekst'] === $tekst) {
			setMelding('Uw reactie is al geplaatst', 0);

			// concept wissen
			if ($nieuw) {
				ForumDradenReagerenModel::instance()->setConcept($deel);
			} else {
				ForumDradenReagerenModel::instance()->setConcept($deel, $draad->draad_id);
			}

			redirect($url);
		}

		// voorkom ongesloten tags
		$aantal = CsrBB::aantalOngeslotenTags($tekst);
		if ($aantal > 0) {
			setMelding('Uw bericht bevat een fout in de bbcode: ' . $aantal . ' ongesloten tags', -1);
			redirect($url);
		}

		// externen checks
		$mailadres = null;
		$wacht_goedkeuring = false;
		if (!LoginModel::mag('P_LOGGED_IN')) {
			$wacht_goedkeuring = true;
			$mailadres = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
			if (!email_like($mailadres)) {
				setMelding('U moet een geldig email-adres opgeven!', -1);
				redirect($url);
			}
			if ($filter->isSpam($mailadres)) { //TODO: logging
				setMelding('SPAM', -1);
				redirect($url);
			}
		}

		// post in nieuw draadje?
		if ($nieuw) {
			if (empty($titel)) {
				setMelding('U moet een titel opgeven!', -1);
				redirect($url);
			}
			// maak draad
			$draad = ForumDradenModel::instance()->maakForumDraad($deel->forum_id, $titel, $wacht_goedkeuring);
		}

		// maak post
		$post = ForumPostsModel::instance()->maakForumPost($draad->draad_id, $tekst, $_SERVER['REMOTE_ADDR'], $wacht_goedkeuring, $mailadres);

		// bericht sturen naar pubcie@csrdelft dat er een bericht op goedkeuring wacht?
		if ($wacht_goedkeuring) {
			setMelding('Uw bericht is opgeslagen en zal als het goedgekeurd is geplaatst worden.', 1);

			mail('pubcie@csrdelft.nl', 'Nieuw bericht wacht op goedkeuring', "http://csrdelft.nl/forum/onderwerp/" . $draad->draad_id . "/wacht#" . $post->post_id . "\r\n" . "\r\nDe inhoud van het bericht is als volgt: \r\n\r\n" . str_replace('\r\n', "\n", $tekst) . "\r\n\r\nEINDE BERICHT", "From: pubcie@csrdelft.nl\nReply-To: " . $mailadres);

			if ($nieuw) {
				redirect(CSR_ROOT . '/forum/deel/' . $deel->forum_id);
			}
		} else {

			// direct goedkeuren voor ingelogd
			ForumPostsModel::instance()->tellenEnGoedkeurenForumPost($post, $draad, $deel);
			foreach ($draad->getVolgers() as $uid) {
				require_once 'MVC/model/entity/Mail.class.php';
				$bericht = "[url]http://csrdelft.nl/forum/reactie/" . $post->post_id . "#" . $post->post_id . "[/url]\r\n" . "\r\nDe inhoud van het bericht is als volgt: \r\n\r\n" . str_replace('\r\n', "\n", $tekst) . "\r\n\r\nEINDE BERICHT";
				$mail = new Mail(array($uid . '@csrdelft.nl' => Lid::naamLink($uid, 'civitas', 'plain')), 'C.S.R. Forum: nieuwe reactie op ' . $draad->titel, $bericht);
				$mail->setReplyTo('no-reply@csrdelft.nl');
				$mail->send();
			}

			setMelding(($nieuw ? 'Draad' : 'Post') . ' succesvol toegevoegd', 1);

			$url = CSR_ROOT . '/forum/reactie/' . $post->post_id . '#' . $post->post_id;
		}

		// concept wissen
		if ($nieuw) {
			ForumDradenReagerenModel::instance()->setConcept($deel);
		} else {
			ForumDradenReagerenModel::instance()->setConcept($deel, $draad->draad_id);
		}

		// markeer als gelezen
		ForumDradenGelezenModel::instance()->setWanneerGelezenDoorLid($draad);

		// voorkom dubbelposts
		$_SESSION['forum_laatste_post_tekst'] = $tekst;

		// redirect naar post
		redirect($url);
	}

	public static function magForumPostBewerken(ForumPost $post, ForumDraad $draad, ForumDeel $deel) {
		if ($deel->magModereren()) {
			return true;
		}
		if ($draad->verwijderd OR $draad->gesloten OR $post->uid !== LoginModel::getUid() OR ! LoginModel::mag('P_LOGGED_IN')) {
			return false;
		}
		if ($deel->magPosten()) {
			return true;
		}
		if ($draad->gedeeld_met AND ForumDelenModel::instance()->getForumDeel($draad->gedeeld_met)->magPosten()) {
			return true;
		}
		return false;
	}

	public function tekst($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!ForumController::magForumPostBewerken($post, $draad, $deel)) {
			$this->geentoegang();
		}
		echo $post->tekst;
		exit; //TODO: JsonResponse
	}

	public function bewerken($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!ForumController::magForumPostBewerken($post, $draad, $deel)) {
			$this->geentoegang();
		}
		$tekst = trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW));

		// voorkom ongesloten tags
		$aantal = CsrBB::aantalOngeslotenTags($tekst);
		if ($aantal > 0) {
			$this->view = new JsonResponse('Uw bericht bevat een fout in de bbcode: ' . $aantal . ' ongesloten tags', 400);
			return;
		}
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
		$nieuwDraad = ForumDradenModel::instance()->getForumDraad((int) $nieuw);
		$nieuwDeel = ForumDelenModel::instance()->getForumDeel($nieuwDraad->forum_id);
		if (!$nieuwDeel->magModereren()) {
			$this->geentoegang();
		}
		ForumPostsModel::instance()->verplaatsForumPost($nieuwDraad, $post);
		ForumPostsModel::instance()->tellenEnGoedkeurenForumPost($post, $nieuwDraad, $nieuwDeel);
		ForumPostsModel::instance()->hertellenVoorDraadEnDeel($draad, $deel);
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
		ForumPostsModel::instance()->tellenEnGoedkeurenForumPost($post, $draad, $deel);
		$this->view = new ForumPostView($post, $draad, $deel);
	}

	public function citeren($post_id) {
		$post = ForumPostsModel::instance()->getForumPost((int) $post_id);
		$draad = ForumDradenModel::instance()->getForumDraad($post->draad_id);
		$deel = ForumDelenModel::instance()->getForumDeel($draad->forum_id);
		if (!ForumController::magPosten($draad, $deel)) {
			$this->geentoegang();
		}
		echo ForumPostsModel::instance()->citeerForumPost($post);
		exit; //TODO: JsonResponse
	}

	/**
	 * Concept bericht opslaan
	 */
	public function concept($forum_id, $draad_id = null) {
		if (!LoginModel::mag('P_LOGGED_IN')) {
			$this->geentoegang();
		}
		$titel = trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING));
		$concept = trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW));
		$ping = filter_input(INPUT_POST, 'ping', FILTER_SANITIZE_STRING);

		$deel = ForumDelenModel::instance()->getForumDeel((int) $forum_id);
		if ($draad_id === null) {
			if (!$deel->magPosten()) {
				$this->geentoegang();
			}
			if (empty($ping)) {
				ForumDradenReagerenModel::instance()->setConcept($deel, null, $concept, $titel);
			} elseif ($ping === 'true') {
				ForumDradenReagerenModel::instance()->setWanneerReagerenDoorLid($deel);
			}
			$reageren = ForumDradenReagerenModel::instance()->getReagerenVoorDeel($deel);
		} else {
			$draad = ForumDradenModel::instance()->getForumDraad((int) $draad_id);
			$draad_id = $draad->draad_id;
			if (!ForumController::magPosten($draad, $deel)) {
				$this->geentoegang();
			}
			if (empty($ping)) {
				ForumDradenReagerenModel::instance()->setConcept($deel, $draad_id, $concept);
			} elseif ($ping === 'true') {
				ForumDradenReagerenModel::instance()->setWanneerReagerenDoorLid($deel, $draad_id);
			}
			$reageren = ForumDradenReagerenModel::instance()->getReagerenVoorDraad($draad);
		}
		$this->view = new ForumDraadReagerenView($reageren);
	}

}
