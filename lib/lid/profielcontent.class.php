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

		$profhtml['abos']=array();
		require_once 'maaltijden/maaltrack.class.php';
		require_once 'maaltijden/maaltijd.class.php';
		$maaltrack=new Maaltrack();
		$profhtml['abos']=$maaltrack->getAbo($this->lid->getUid());
		$profhtml['recenteMaaltijden']=Maaltijd::getRecenteMaaltijden($this->lid->getUid());

		require_once 'forum/forum.class.php';
		$profhtml['recenteForumberichten']=Forum::getPostsVoorUid($this->lid->getUid());
		$profhtml['berichtCount']=Forum::getUserPostCount($this->lid->getUid());

		//de html template in elkaar draaien en weergeven
		$profiel=new Smarty_csr();

		$profiel->assign('lid', $this->lid);
		$profiel->assign('profhtml', $profhtml);

		require_once 'lid/saldi.class.php';
		if(Saldi::magGrafiekZien($this->lid->getUid())){
			$profiel->assign('saldografiek', Saldi::getDatapoints($this->lid->getUid(), 60));
		}
		
		$profiel->assign('isOudlid', in_array($this->lid->getStatus(), array('S_OUDLID', 'S_ERELID')));

		$loginlid=LoginLid::instance();
		$profiel->assign('magBewerken', ($loginlid->hasPermission('P_PROFIEL_EDIT') AND $loginlid->isSelf($this->lid->getUid())) OR $loginlid->hasPermission('P_LEDEN_EDIT'));
		$profiel->assign('isAdmin', $loginlid->hasPermission('P_ADMIN'));
		$profiel->assign('isLidMod', $loginlid->hasPermission('P_LEDEN_MOD'));
		$profiel->assign('melding', $this->getMelding());

		//eigen profiel niet cachen, dan krijgen we namelijk rare dingen
		//dat we andermans saldo's zien enzo
		if(LoginLid::instance()->isSelf($this->lid->getUid())){
			$profiel->caching=false;
		}
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
?>
