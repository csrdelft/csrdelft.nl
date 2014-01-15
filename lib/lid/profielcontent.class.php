<?php

# C.S.R. Delft
# -------------------------------------------------------------------
# class.profielcontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van een ledenprofiel
# -------------------------------------------------------------------

/**
 * Profiel bekijken
 */
class ProfielContent extends TemplateView {

	/** @var Lid  */
	private $lid;

	function __construct($lid) {
		parent::__construct();
		$this->lid = $lid;
	}

	function getTitel() {
		return 'Het profiel van ' . $this->lid->getNaam();
	}

	function view() {
		$profhtml = array();
		foreach ($this->lid->getProfiel() as $key => $value) {
			if (!is_array($value) AND $key != 'changelog') {
				$profhtml[$key] = mb_htmlentities($value);
			} elseif ($key == 'changelog') {
				$profhtml[$key] = $value;
			}
		}

		$woonoord = $this->lid->getWoonoord();
		if ($woonoord instanceof Groep) {
			$profhtml['woonoord'] = '<strong>' . $woonoord->getLink() . '</strong>';
		} else {
			$profhtml['woonoord'] = '';
		}

		require_once('groepen/groepcontent.class.php');
		$profhtml['groepen'] = new GroepenProfielContent($this->lid->getUid());

		if (LoginLid::instance()->getUid() == $this->lid->getUid() || LoginLid::instance()->hasPermission('P_MAAL_MOD')) {
			$profhtml['recenteAanmeldingen'] = AanmeldingenModel::getRecenteAanmeldingenVoorLid($this->lid->getUid());
			$profhtml['abos'] = AbonnementenModel::getAbonnementenVoorLid($this->lid->getUid());
		}

		//de html template in elkaar draaien en weergeven
		$this->smarty->assign('profhtml', $profhtml);

		require_once 'lid/saldi.class.php';
		if (Saldi::magGrafiekZien($this->lid->getUid())) {
			$this->smarty->assign('saldografiek', Saldi::getDatapoints($this->lid->getUid(), 60));
		}

		$this->smarty->assign('corveepunten', $this->lid->getProperty('corvee_punten'));
		$this->smarty->assign('corveebonus', $this->lid->getProperty('corvee_punten_bonus'));
		$this->smarty->assign('corveetaken', $this->lid->getCorveeTaken());
		$this->smarty->assign('corveevoorkeuren', $this->lid->getCorveeVoorkeuren());
		$this->smarty->assign('corveevrijstelling', $this->lid->getCorveeVrijstelling());
		$this->smarty->assign('corveekwalificaties', $this->lid->getCorveeKwalificaties());

		require_once 'bibliotheek/catalogus.class.php';
		$this->smarty->assign('boeken', Catalogus::getBoekenByUid($this->lid->getUid(), 'eigendom'));
		$this->smarty->assign('gerecenseerdeboeken', Catalogus::getBoekenByUid($this->lid->getUid(), 'gerecenseerd'));

		$loginlid = LoginLid::instance();
		$this->smarty->assign('isAdmin', $loginlid->hasPermission('P_ADMIN'));
		//TODO check role vs permission R_BESTUUR
		$this->smarty->assign('isBestuur', $loginlid->hasPermission('R_BESTUUR'));
		$this->smarty->assign('isLidMod', $loginlid->hasPermission('P_LEDEN_MOD'));
		$this->smarty->assign('melding', $this->getMelding());

		//eigen profiel niet cachen, dan krijgen we namelijk rare dingen
		//dat we andermans saldo's zien enzo
		if (LoginLid::instance()->isSelf($this->lid->getUid())) {
			$this->caching = false;
		}

		$this->smarty->assign('profiel', new Profiel($this->lid));

		$template = 'profiel/profiel.tpl';
		$this->smarty->display($template, $this->lid->getUid());
	}

}

/**
 * Profiel bewerken formulierpagina
 */
class ProfielEditContent extends TemplateView {

	/** @var Profiel */
	private $profiel;
	private $actie;

	public function __construct($profiel, $actie) {
		parent::__construct();
		$this->profiel = $profiel;
		$this->actie = $actie;
	}

	public function getTitel() {
		return 'profiel van ' . $this->profiel->getLid()->getNaam() . ' bewerken.';
	}

	public function view() {
		$this->smarty->assign('profiel', $this->profiel);

		$this->smarty->assign('melding', $this->getMelding());
		$this->smarty->assign('actie', $this->actie);
		$this->smarty->display('profiel/bewerken.tpl');
	}

}

/**
 * Lidstatus-wijzigingsformulierpagina
 */
class ProfielStatusContent extends TemplateView {

	/** @var Profiel */
	private $profiel;
	private $actie;

	public function __construct($profiel, $actie) {
		parent::__construct();
		$this->profiel = $profiel;
		$this->actie = $actie;
	}

	public function getTitel() {
		return 'lidstatus van ' . $this->profiel->getLid()->getNaam() . ' aanpassen.';
	}

	public function view() {
		$gelijknamigenovieten = Zoeker::zoekLeden($this->profiel->getLid()->getProperty('voornaam'), 'voornaam', 'alle', 'achternaam', array('S_NOVIET'), array('uid'));
		$gelijknamigeleden = Zoeker::zoekLeden($this->profiel->getLid()->getProperty('achternaam'), 'achternaam', 'alle', 'lidjaar', array('S_LID', 'S_GASTLID'), array('uid'));

		$this->smarty->assign('profiel', $this->profiel);
		$this->smarty->assign('gelijknamigenovieten', $gelijknamigenovieten);
		$this->smarty->assign('gelijknamigeleden', $gelijknamigeleden);

		$this->smarty->assign('melding', $this->getMelding());
		$this->smarty->assign('actie', $this->actie);
		$this->smarty->display('profiel/wijzigstatus.tpl');
	}

}

/**
 * Commissievoorkeuren formulierpagina
 */
class ProfielVoorkeurContent extends TemplateView {

	/** @var Profiel */
	private $profiel;
	private $actie;

	public function __construct($profiel, $actie) {
		parent::__construct();
		$this->profiel = $profiel;
		$this->actie = $actie;
	}

	public function getTitel() {
		return 'voorkeur van ' . $this->profiel->getLid()->getNaam() . ' aanpassen.';
	}

	public function view() {
		$this->smarty->assign('profiel', $this->profiel);
		$this->smarty->assign('melding', $this->getMelding());
		$this->smarty->assign('actie', $this->actie);
		$this->smarty->display('profiel/wijzigvoorkeur.tpl');
	}

}