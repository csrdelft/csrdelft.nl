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

		require_once('groepen/class.groepcontent.php');
		$profhtml['groepen']=new GroepenProfielContent($this->lid->getUid());

		//soccie saldo
		$profhtml['saldi']='';
		//alleen als men het eigen profiel bekijkt.
		if(LoginLid::instance()->isSelf($this->lid->getUid())){
			$profhtml['saldi']=$this->lid->getSaldi();
		}
		require_once 'lid/class.saldi.php';
		$profhtml['saldografiek']=Saldi::getGrafiektags($this->lid->getUid());
		
		$profhtml['abos']=array();
		require_once 'maaltijden/class.maaltrack.php';
		require_once 'maaltijden/class.maaltijd.php';
		$maaltrack=new Maaltrack();
		$profhtml['abos']=$maaltrack->getAbo($this->lid->getUid());
		$profhtml['recenteMaaltijden']=Maaltijd::getRecenteMaaltijden($this->lid->getUid());

		require_once 'forum/class.forum.php';
		$profhtml['recenteForumberichten']=Forum::getPostsVoorUid($this->lid->getUid());

		//de html template in elkaar draaien en weergeven
		$profiel=new Smarty_csr();

		$profiel->assign('lid', $this->lid);
		$profiel->assign('profhtml', $profhtml);
		
		$profiel->assign('isOudlid', $this->lid->getStatus()=='S_OUDLID');

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
	public function __construct($profiel){
		$this->profiel=$profiel;
	}
	public function view(){
		require_once 'class.formulier.php';
		$profiel=new Smarty_csr();
		$profiel->assign('profiel', $this->profiel);

		$profiel->display('profiel/bewerken.tpl');
	}
}
?>
