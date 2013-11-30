<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.profielcontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van een ledenprofiel
# -------------------------------------------------------------------



class ProfielContent extends SimpleHTML {

	private $lid;

	function __construct($lid) {
		$this->lid=$lid;
	}
	function getTitel(){
		return 'Het profiel van '.$this->lid->getNaam();
	}
	function view(){
		$profhtml = array();
		foreach($this->lid->getProfiel() as $key => $value){
			if(!is_array($value) AND $key!='changelog'){
				$profhtml[$key] = mb_htmlentities($value);
			}elseif($key=='changelog'){
				$profhtml[$key] = $value;
			}
		}

		$woonoord=$this->lid->getWoonoord();
		if($woonoord instanceof Groep){
			$profhtml['woonoord']='<strong>'.$woonoord->getLink().'</strong>';
		}

		require_once('groepen/groepcontent.class.php');
		$profhtml['groepen']=new GroepenProfielContent($this->lid->getUid());

		$profhtml['abos'] = \Taken\MLT\AbonnementenModel::getAbonnementenVoorLid($this->lid->getUid());
		$profhtml['recenteMaaltijden'] = \Taken\MLT\AanmeldingenModel::getRecenteMaaltijdenVoorLid($this->lid->getUid());

		//de html template in elkaar draaien en weergeven
		$profiel=new Smarty_csr();

		$profiel->assign('profhtml', $profhtml);

		require_once 'lid/saldi.class.php';
		if(Saldi::magGrafiekZien($this->lid->getUid())){
			$profiel->assign('saldografiek', Saldi::getDatapoints($this->lid->getUid(), 60));
		}

		$profiel->assign('corveetaken', $this->lid->getCorveeTaken());
		$profiel->assign('corveevoorkeuren', $this->lid->getCorveeVoorkeuren());
		$profiel->assign('corveevrijstelling', $this->lid->getCorveeVrijstelling());
		$profiel->assign('corveekwalificaties', $this->lid->getCorveeKwalificaties());

		require_once 'bibliotheek/catalogus.class.php';
		$profiel->assign('boeken', Catalogus::getBoekenByUid($this->lid->getUid(), 'eigendom'));
		$profiel->assign('gerecenseerdeboeken', Catalogus::getBoekenByUid($this->lid->getUid(),'gerecenseerd'));

		$loginlid=LoginLid::instance();
		$profiel->assign('isAdmin', $loginlid->hasPermission('P_ADMIN'));
		$profiel->assign('isBestuur', $loginlid->hasPermission('P_BESTUUR'));
		$profiel->assign('isLidMod', $loginlid->hasPermission('P_LEDEN_MOD'));
		$profiel->assign('melding', $this->getMelding());
		
		//eigen profiel niet cachen, dan krijgen we namelijk rare dingen
		//dat we andermans saldo's zien enzo
		if(LoginLid::instance()->isSelf($this->lid->getUid())){
			$profiel->caching=false;
		}

		$profiel->assign('profiel', new Profiel($this->lid));

		$template='profiel/profiel.tpl';
		$profiel->display($template, $this->lid->getUid());
	}
}


class ProfielEditContent extends SimpleHTML{
	private $profiel;
	private $actie;

	public function __construct($profiel, $actie){
		$this->profiel=$profiel;
		$this->actie=$actie;
	}
	public function getTitel(){
		return 'profiel van '.$this->profiel->getLid()->getNaam().' bewerken.';
	}
	public function view(){
		require_once 'formulier.class.php';
		$profiel=new Smarty_csr();
		$profiel->assign('profiel', $this->profiel);

		$profiel->assign('melding', $this->getMelding());
		$profiel->assign('actie', $this->actie);
		$profiel->display('profiel/bewerken.tpl');
	}
}


class ProfielStatusContent extends SimpleHTML{
	private $profiel;
	private $actie;

	public function __construct($profiel, $actie){
		$this->profiel=$profiel;
		$this->actie=$actie;
	}
	public function getTitel(){
		return 'lidstatus van '.$this->profiel->getLid()->getNaam().' aanpassen.';
	}
	public function view(){


		$gelijknamigenovieten = Zoeker::zoekLeden($this->profiel->getLid()->getProperty('voornaam'), 'voornaam', 'alle', 'achternaam', array('S_NOVIET'), array('uid'));
		$gelijknamigeleden = Zoeker::zoekLeden($this->profiel->getLid()->getProperty('achternaam'), 'achternaam', 'alle', 'lidjaar', array('S_LID', 'S_GASTLID'), array('uid'));

		require_once 'formulier.class.php';
		$profiel=new Smarty_csr();
		$profiel->assign('profiel', $this->profiel);
		$profiel->assign('gelijknamigenovieten', $gelijknamigenovieten);
		$profiel->assign('gelijknamigeleden', $gelijknamigeleden);

		$profiel->assign('melding', $this->getMelding());
		$profiel->assign('actie', $this->actie);
		$profiel->display('profiel/wijzigstatus.tpl');
	}
}

class ProfielVoorkeurContent extends SimpleHTML{
	private $profiel;
	private $actie;

	public function __construct($profiel, $actie){
		$this->profiel=$profiel;
		$this->actie=$actie;
	}
	public function getTitel(){
		return 'voorkeur van '.$this->profiel->getLid()->getNaam().' aanpassen.';
	}
	public function view(){


		require_once 'formulier.class.php';
		$profiel=new Smarty_csr();
		$profiel->assign('profiel', $this->profiel);

		$profiel->assign('melding', $this->getMelding());
		$profiel->assign('actie', $this->actie);
		$profiel->display('profiel/wijzigvoorkeur.tpl');
	}
}
?>
