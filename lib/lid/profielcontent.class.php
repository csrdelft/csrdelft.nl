<?php

# C.S.R. Delft
# -------------------------------------------------------------------
# class.profielcontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van een ledenprofiel
# -------------------------------------------------------------------

class ProfielContent extends TemplateView {

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
		$this->assign('profhtml', $profhtml);

		require_once 'lid/saldi.class.php';
		if (Saldi::magGrafiekZien($this->lid->getUid())) {
			$this->assign('saldografiek', Saldi::getDatapoints($this->lid->getUid(), 60));
		}

		$this->assign('corveepunten', $this->lid->getProperty('corvee_punten'));
		$this->assign('corveebonus', $this->lid->getProperty('corvee_punten_bonus'));
		$this->assign('corveetaken', $this->lid->getCorveeTaken());
		$this->assign('corveevoorkeuren', $this->lid->getCorveeVoorkeuren());
		$this->assign('corveevrijstelling', $this->lid->getCorveeVrijstelling());
		$this->assign('corveekwalificaties', $this->lid->getCorveeKwalificaties());

		require_once 'bibliotheek/catalogus.class.php';
		$this->assign('boeken', Catalogus::getBoekenByUid($this->lid->getUid(), 'eigendom'));
		$this->assign('gerecenseerdeboeken', Catalogus::getBoekenByUid($this->lid->getUid(), 'gerecenseerd'));

		$loginlid = LoginLid::instance();
		$this->assign('isAdmin', $loginlid->hasPermission('P_ADMIN'));
		$this->assign('isBestuur', $loginlid->hasPermission('P_BESTUUR'));
		$this->assign('isLidMod', $loginlid->hasPermission('P_LEDEN_MOD'));
		$this->assign('melding', $this->getMelding());

		//eigen profiel niet cachen, dan krijgen we namelijk rare dingen
		//dat we andermans saldo's zien enzo
		if (LoginLid::instance()->isSelf($this->lid->getUid())) {
			$this->caching = false;
		}

		$this->assign('profiel', new Profiel($this->lid));

		$template = 'profiel/profiel.tpl';
		$this->display($template, $this->lid->getUid());
	}

}

class ProfielEditContent extends TemplateView {

	private $this;
	private $actie;

	public function __construct($this, $actie) {
		parent::__construct();
		$this->profiel = $this;
		$this->actie = $actie;
	}

	public function getTitel() {
		return 'profiel van ' . $this->profiel->getLid()->getNaam() . ' bewerken.';
	}

	public function view() {
		
		$this->assign('profiel', $this->profiel);

		$this->assign('melding', $this->getMelding());
		$this->assign('actie', $this->actie);
		$this->display('profiel/bewerken.tpl');
	}

}

class ProfielStatusContent extends TemplateView {

	private $this;
	private $actie;

	public function __construct($this, $actie) {
		parent::__construct();
		$this->profiel = $this;
		$this->actie = $actie;
	}

	public function getTitel() {
		return 'lidstatus van ' . $this->profiel->getLid()->getNaam() . ' aanpassen.';
	}

	public function view() {


		$gelijknamigenovieten = Zoeker::zoekLeden($this->profiel->getLid()->getProperty('voornaam'), 'voornaam', 'alle', 'achternaam', array('S_NOVIET'), array('uid'));
		$gelijknamigeleden = Zoeker::zoekLeden($this->profiel->getLid()->getProperty('achternaam'), 'achternaam', 'alle', 'lidjaar', array('S_LID', 'S_GASTLID'), array('uid'));

		
		$this->assign('profiel', $this->profiel);
		$this->assign('gelijknamigenovieten', $gelijknamigenovieten);
		$this->assign('gelijknamigeleden', $gelijknamigeleden);

		$this->assign('melding', $this->getMelding());
		$this->assign('actie', $this->actie);
		$this->display('profiel/wijzigstatus.tpl');
	}

}

class ProfielVoorkeurContent extends TemplateView {

	private $this;
	private $actie;

	public function __construct($this, $actie) {
		parent::__construct();
		$this->profiel = $this;
		$this->actie = $actie;
	}

	public function getTitel() {
		return 'voorkeur van ' . $this->profiel->getLid()->getNaam() . ' aanpassen.';
	}

	public function view() {
		
		$this->assign('profiel', $this->profiel);
		$this->assign('melding', $this->getMelding());
		$this->assign('actie', $this->actie);
		$this->display('profiel/wijzigvoorkeur.tpl');
	}

}

?>
