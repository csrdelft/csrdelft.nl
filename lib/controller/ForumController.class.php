<?php

require_once 'model/ForumModel.class.php';
require_once 'view/ForumView.class.php';

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
	protected function mag($action, $method) {
		switch ($action) {
			case 'zoeken':
				return true;

			// Forum
			case 'titelzoeken':
			case 'rss':
			case 'recent':
			case 'belangrijk':
			case 'deel':
			case 'onderwerp':
			case 'reactie':
			case 'wacht':
				return $method === 'GET';

			// ForumDeel
			case 'aanmaken':
			case 'beheren':
			case 'opheffen':
				if (!LoginModel::mag('P_FORUM_ADMIN')) {
					return false;
				}

			// ForumPost
			case 'citeren':
			case 'bladwijzer':
			case 'concept':
			case 'grafiekdata':
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
				return $method === 'POST';

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
		switch ($this->action) {
			case 'titelzoeken':
			case 'grafiekdata':
			case 'rss':
				return;

			case 'zoeken':
				break;

			default:
				if ($this->isPosted()) {
					return;
				}
		}
		if (LoginModel::mag('P_LOGGED_IN')) {
			$this->view = new CsrLayoutPage($this->view);
			if ($this->action === 'forum') {
				$this->view->addCompressedResources('grafiek');
			}
		} else { // uitgelogd heeft nieuwe layout
			$this->view = new CsrLayout2Page($this->view);
		}
		$this->view->addCompressedResources('forum');
	}

	/**
	 * Overzicht met categorien en forumdelen laten zien.
	 */
	public function forum() {
		$this->view = new ForumOverzichtView();
	}

	public function grafiekdata() {
		$this->view = new FlotDataResponse(ForumPostsModel::instance()->getStats(Instellingen::get('forum', 'grafiek_periode')));
	}

	/**
	 * RSS feed van recente draadjes tonen.
	 */
	public function rss() {
		header('Content-Type: application/rss+xml; charset=UTF-8');
		$draden = ForumDradenModel::instance()->getRecenteForumDraden(null, null, true);
		$this->view = new ForumRssView($draden);
	}

	/**
	 * Tonen van alle posts die wachten op goedkeuring.
	 */
	public function wacht() {
		$draden = ForumDelenModel::instance()->getWachtOpGoedkeuring();
		$this->view = new ForumResultatenView($draden);
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
			$datum = $values['datumsoort'];
			$ouder = $values['ouderjonger'];
			$jaar = $values['jaaroud'];
		} else {
			$query = urldecode($query);
			$query = filter_var($query, FILTER_SANITIZE_SPECIAL_CHARS);
			$datum = 'laatst_gewijzigd';
			$ouder = 'jonger';
			$jaar = 1;
		}
		$limit = (int) LidInstellingen::get('forum', 'zoekresultaten');
		$draden = ForumDelenModel::instance()->zoeken($query, false, $datum, $ouder, $jaar, $limit);
		$this->view = new ForumResultatenView($draden, $query);
	}

	/**
	 * Draden zoeken op titel voor auto-aanvullen.
	 * 
	 * @param string $query
	 */
	public function titelzoeken() {
		$result = array();
		if ($this->hasParam('q')) {
			$query = $this->getParam('q');
			$datum = 'laatst_gewijzigd';
			$ouder = 'jonger';
			$jaar = null;
			$limit = 5;
			if ($this->hasParam('limit')) {
				$limit = (int) $this->getParam('limit');
			}
			$draden = ForumDelenModel::instance()->zoeken($query, true, $datum, $ouder, $jaar, $limit);
			foreach ($draden as $draad) {
				$url = '/forum/onderwerp/' . $draad->draad_id;
				if (LidInstellingen::get('forum', 'open_draad_op_pagina') == 'ongelezen') {
					$url .= '#ongelezen';
				} elseif (LidInstellingen::get('forum', 'open_draad_op_pagina') == 'laatste') {
					$url .= '#reageren';
				}
				if ($draad->gesloten) {
					$icon = '<img src="/plaetjes/famfamfam/lock.png" width="16" height="16" alt="slotje" title="Dit onderwerp is gesloten, u kunt niet meer reageren" class="icon"> ';
				} elseif ($draad->belangrijk) {
					$icon = '<img src="/plaetjes/famfamfam/asterisk_orange.png" width="16" height="16" alt="belangrijk" title="Dit onderwerp is door het bestuur aangemerkt als belangrijk." class="icon"> ';
				} elseif ($draad->plakkerig) {
					$icon = '<img src="/plaetjes/famfamfam/note.png" width="16" height="16" alt="plakkerig" title="Dit onderwerp is plakkerig, het blijft bovenaan." class="icon"> ';
				} else {
					$icon = '';
				}
				$result[] = array(
					'url'	 => $url,
					'value'	 => $icon . $draad->titel . '<span class="lichtgrijs"> - ' . $draad->getForumDeel()->titel . '</span>'
				);
			}
		}
		if (empty($result)) {
			$result[] = array(
				'url'	 => '/forum/zoeken/' . urlencode($query),
				'value'	 => htmlspecialchars($query) . '<span class="lichtgrijs"> - Zoeken in <span class="dikgedrukt">reacties</span></span>'
			);
		}
		$this->view = new JsonResponse($result);
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
		$deel = ForumDelenModel::get((int) $forum_id);
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
	public function onderwerp($draad_id, $pagina = null, $statistiek = null) {
		$draad = ForumDradenModel::get((int) $draad_id);
		if (!$draad->magLezen()) {
			$this->geentoegang();
		}
		$gelezen = $draad->getWanneerGelezen();
		if ($pagina === null) {
			$pagina = LidInstellingen::get('forum', 'open_draad_op_pagina');
		}
		$paging = true;
		if ($pagina === 'ongelezen' AND $gelezen) {
			ForumPostsModel::instance()->setPaginaVoorLaatstGelezen($gelezen);
		} elseif ($pagina === 'laatste') {
			ForumPostsModel::instance()->setLaatstePagina($draad->draad_id);
		} elseif ($pagina === 'prullenbak' AND $draad->magModereren()) {
			$draad->setForumPosts(ForumPostsModel::instance()->getPrullenbakVoorDraad($draad));
			$paging = false;
		} else {
			ForumPostsModel::instance()->setHuidigePagina((int) $pagina, $draad->draad_id);
		}
		if ($statistiek === 'statistiek' AND $draad->magModereren()) {
			$statistiek = true;
		} else {
			$statistiek = false;
		}
		$this->view = new ForumDraadView($draad, $paging, $statistiek); // lazy loading ForumPost[]
	}

	/**
	 * Opzoeken forumdraad van forumpost.
	 * 
	 * @param int $post_id
	 */
	public function reactie($post_id) {
		$post = ForumPostsModel::get((int) $post_id);
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
	 * Forum deel bewerken.
	 * 
	 * @param int $forum_id
	 */
	public function beheren($forum_id) {
		$deel = ForumDelenModel::get((int) $forum_id);
		$form = new ForumDeelForm($deel); // fetches POST values itself
		if ($form->validate()) {
			$rowCount = ForumDelenModel::instance()->update($deel);
			if ($rowCount !== 1) {
				throw new Exception('Forum beheren mislukt!');
			}
			$this->view = new JsonResponse(true);
		} else {
			$this->view = $form;
		}
	}

	/**
	 * Forum deel verwijderen.
	 * 
	 * @param int $forum_id
	 */
	public function opheffen($forum_id) {
		$deel = ForumDelenModel::get((int) $forum_id);
		if (ForumDradenModel::instance()->exist('forum_id = ?', array($deel->forum_id))) {
			setMelding('Verwijder eerst alle draadjes van dit deelforum uit de database!', -1);
		} else {
			ForumDelenModel::instance()->verwijderForumDeel($deel->forum_id);
			setMelding('Deelforum verwijderd', 1);
		}
		$this->view = new JsonResponse(true);
	}

	/**
	 * Forum draad verbergen in zijbalk.
	 * 
	 * @param int $draad_id
	 */
	public function verbergen($draad_id) {
		$draad = ForumDradenModel::get((int) $draad_id);
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
		$draad = ForumDradenModel::get((int) $draad_id);
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
		$draad = ForumDradenModel::get((int) $draad_id);
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
		$draad = ForumDradenModel::get((int) $draad_id);
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
	 * Leg bladwijzer
	 * 
	 * @param int $draad_id
	 */
	public function bladwijzer($draad_id) {
		$draad = ForumDradenModel::get((int) $draad_id);
		$timestamp = (int) filter_input(INPUT_POST, 'timestamp', FILTER_SANITIZE_NUMBER_INT);
		if (ForumDradenGelezenModel::instance()->setWanneerGelezenDoorLid($draad, $timestamp - 1)) {
			echo '<img id="timestamp' . $timestamp . '" src="/plaetjes/famfamfam/tick.png" class="icon" title="Bladwijzer succesvol geplaatst">';
		}
		exit; //TODO: JsonResponse
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
		$draad = ForumDradenModel::get((int) $draad_id);
		// gedeelde moderators mogen dit niet
		if (!$draad->getForumDeel()->magModereren()) {
			$this->geentoegang();
		}
		if (in_array($property, array('verwijderd', 'gesloten', 'plakkerig', 'belangrijk', 'eerste_post_plakkerig', 'pagina_per_post'))) {
			$value = !$draad->$property;
			if ($property === 'belangrijk' AND ! LoginModel::mag('P_FORUM_BELANGRIJK')) {
				$this->geentoegang();
			}
		} elseif ($property === 'forum_id' OR $property === 'gedeeld_met') {
			$value = (int) filter_input(INPUT_POST, $property, FILTER_SANITIZE_NUMBER_INT);
			if ($property === 'forum_id') {
				$deel = ForumDelenModel::get($value);
				if (!$deel->magModereren()) {
					$this->geentoegang();
				}
			} elseif ($value === 0) {
				$value = null;
			}
		} elseif ($property === 'titel') {
			$value = trim(filter_input(INPUT_POST, $property, FILTER_SANITIZE_STRING));
		} else {
			$this->geentoegang();
		}
		ForumDradenModel::instance()->wijzigForumDraad($draad, $property, $value);
		if (is_bool($value)) {
			$wijziging = ($value ? 'wel ' : 'niet ') . $property;
		} else {
			$wijziging = $property . ' = ' . $value;
		}
		setMelding('Wijziging geslaagd: ' . $wijziging, 1);
		if ($property === 'forum_id' OR $property === 'titel' OR $property === 'gedeeld_met') {
			redirect('/forum/onderwerp/' . $draad_id);
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
		$deel = ForumDelenModel::get((int) $forum_id);

		// post in bestaand draadje?
		if ($draad_id !== null) {
			$draad = ForumDradenModel::get((int) $draad_id);

			// check draad in forum deel
			if (!$draad OR $draad->forum_id !== $deel->forum_id OR ! $draad->magPosten()) {
				$this->geentoegang();
			}
			$url = '/forum/onderwerp/' . $draad->draad_id;
			$nieuw = false;
		} else {
			if (!$deel->magPosten()) {
				$this->geentoegang();
			}
			$url = '/forum/deel/' . $deel->forum_id;
			$nieuw = true;

			$titel = trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING));
		}
		$tekst = trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW));

		// spam controle
		require_once 'simplespamfilter.class.php';
		$filter = new SimpleSpamfilter();
		$spamtrap = filter_input(INPUT_POST, 'firstname', FILTER_UNSAFE_RAW);
		if (!empty($spamtrap) OR $filter->isSpam($tekst) OR ( isset($titel) AND $filter->isSpam($titel) )) { //TODO: logging
			setMelding('SPAM', -1);
			$this->geentoegang();
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

		// concept opslaan
		if ($nieuw) {
			ForumDradenReagerenModel::instance()->setConcept($deel, null, $tekst, $titel);
		} else {
			ForumDradenReagerenModel::instance()->setConcept($deel, $draad->draad_id, $tekst);
		}

		// externen checks
		$mailadres = null;
		$wacht_goedkeuring = false;
		if (!LoginModel::mag('P_LOGGED_IN')) {
			$wacht_goedkeuring = true;
			$mailadres = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
			if (!email_like($mailadres)) {
				setMelding('U moet een geldig e-mailadres opgeven!', -1);
				redirect($url);
			}
			if ($filter->isSpam($mailadres)) { //TODO: logging
				setMelding('SPAM', -1);
				$this->geentoegang();
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

			mail('pubcie@csrdelft.nl', 'Nieuw bericht wacht op goedkeuring', CSR_ROOT . "/forum/onderwerp/" . $draad->draad_id . "/wacht#" . $post->post_id . "\n\nDe inhoud van het bericht is als volgt: \n\n" . str_replace('\r\n', "\n", $tekst) . "\n\nEINDE BERICHT", "From: pubcie@csrdelft.nl\r\nReply-To: " . $mailadres);
		} else {

			// direct goedkeuren voor ingelogd
			ForumPostsModel::instance()->goedkeurenForumPost($post);
			$self = LoginModel::getUid();
			foreach ($draad->getVolgers() as $uid) {
				$profiel = ProfielModel::get($uid);
				if ($uid === $self OR ! $profiel) {
					continue;
				}
				$lidnaam = $profiel->getNaam('civitas');
				require_once 'model/entity/Mail.class.php';
				$bericht = "Geachte " . $lidnaam . ",\n\nEr is een nieuwe reactie geplaatst in een draad dat u volgt: [url=" . CSR_ROOT . "/forum/reactie/" . $post->post_id . "#" . $post->post_id . "]" . $draad->titel . "[/url]\n\nDe inhoud van het bericht is als volgt: \n\n" . str_replace('\r\n', "\n", $tekst) . "\n\nEINDE BERICHT";
				$mail = new Mail(array($profiel->getPrimaryEmail() => $lidnaam), 'C.S.R. Forum: nieuwe reactie op ' . $draad->titel, $bericht);
				$mail->send();
			}
			setMelding(($nieuw ? 'Draad' : 'Post') . ' succesvol toegevoegd', 1);

			$url = '/forum/reactie/' . $post->post_id . '#' . $post->post_id;
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

	public function citeren($post_id) {
		$post = ForumPostsModel::get((int) $post_id);
		if (!$post->magCiteren()) {
			$this->geentoegang();
		}
		echo ForumPostsModel::instance()->citeerForumPost($post);
		exit; //TODO: JsonResponse
	}

	public function tekst($post_id) {
		$post = ForumPostsModel::get((int) $post_id);
		if (!$post->magBewerken()) {
			$this->geentoegang();
		}
		echo $post->tekst;
		exit; //TODO: JsonResponse
	}

	public function bewerken($post_id) {
		$post = ForumPostsModel::get((int) $post_id);
		if (!$post->magBewerken()) {
			$this->geentoegang();
		}
		$tekst = trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW));
		$reden = trim(filter_input(INPUT_POST, 'reden', FILTER_SANITIZE_STRING));
		ForumPostsModel::instance()->bewerkForumPost($tekst, $reden, $post);
		ForumDradenGelezenModel::instance()->setWanneerGelezenDoorLid($post->getForumDraad(), strtotime($post->laatst_gewijzigd));
		$this->view = new ForumPostView($post);
	}

	public function verplaatsen($post_id) {
		$post = ForumPostsModel::get((int) $post_id);
		$oudDraad = $post->getForumDraad();
		if (!$oudDraad->magModereren()) {
			$this->geentoegang();
		}
		$nieuw = filter_input(INPUT_POST, 'Draad_id', FILTER_SANITIZE_NUMBER_INT);
		$nieuwDraad = ForumDradenModel::get((int) $nieuw);
		if (!$nieuwDraad->magModereren()) {
			$this->geentoegang();
		}
		ForumPostsModel::instance()->verplaatsForumPost($nieuwDraad, $post);
		ForumPostsModel::instance()->goedkeurenForumPost($post);
		$this->view = new ForumPostDeleteView($post->post_id);
	}

	public function verwijderen($post_id) {
		$post = ForumPostsModel::get((int) $post_id);
		if (!$post->getForumDraad()->magModereren()) {
			$this->geentoegang();
		}
		ForumPostsModel::instance()->verwijderForumPost($post);
		$this->view = new ForumPostDeleteView($post->post_id);
	}

	public function offtopic($post_id) {
		$post = ForumPostsModel::get((int) $post_id);
		if (!$post->getForumDraad()->magModereren()) {
			$this->geentoegang();
		}
		ForumPostsModel::instance()->offtopicForumPost($post);
		$this->view = new ForumPostView($post);
	}

	public function goedkeuren($post_id) {
		$post = ForumPostsModel::get((int) $post_id);
		if (!$post->getForumDraad()->magModereren()) {
			$this->geentoegang();
		}
		ForumPostsModel::instance()->goedkeurenForumPost($post);
		$this->view = new ForumPostView($post);
	}

	/**
	 * Concept bericht opslaan
	 */
	public function concept($forum_id, $draad_id = null) {
		$titel = trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING));
		$concept = trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW));
		$ping = filter_input(INPUT_POST, 'ping', FILTER_SANITIZE_STRING);

		$deel = ForumDelenModel::get((int) $forum_id);
		// bestaand draadje?
		if ($draad_id !== null) {
			$draad = ForumDradenModel::get((int) $draad_id);
			$draad_id = $draad->draad_id;
			// check draad in forum deel
			if (!$draad OR $draad->forum_id !== $deel->forum_id OR ! $draad->magPosten()) {
				$this->geentoegang();
			}
			if (empty($ping)) {
				ForumDradenReagerenModel::instance()->setConcept($deel, $draad_id, $concept);
			} elseif ($ping === 'true') {
				ForumDradenReagerenModel::instance()->setWanneerReagerenDoorLid($deel, $draad_id);
			}
			$reageren = ForumDradenReagerenModel::instance()->getReagerenVoorDraad($draad);
		} else {
			if (!$deel->magPosten()) {
				$this->geentoegang();
			}
			if (empty($ping)) {
				ForumDradenReagerenModel::instance()->setConcept($deel, null, $concept, $titel);
			} elseif ($ping === 'true') {
				ForumDradenReagerenModel::instance()->setWanneerReagerenDoorLid($deel);
			}
			$reageren = ForumDradenReagerenModel::instance()->getReagerenVoorDeel($deel);
		}
		$this->view = new ForumDraadReagerenView($reageren);
	}

}
