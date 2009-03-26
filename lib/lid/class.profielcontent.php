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
			if(!is_array($value)){
				$profhtml[$key] = mb_htmlentities($value);
			}
		}

		$profhtml['fullname']=$this->lid->getNaam();


		$woonoord=$this->lid->getWoonoord();
		if($woonoord instanceof Groep){
			$profhtml['woonoord']='<strong>'.$woonoord->getLink().'</strong>';
		}else{
			$profhtml['woonoord']='<br />';
		}

		require_once('groepen/class.groepcontent.php');
		$profhtml['groepen']=new GroepenProfielContent($this->lid->getUid());

		//soccie saldo
		$profhtml['saldi']='';
		//alleen als men het eigen profiel bekijkt.
		if(LoginLid::instance()->isSelf($this->lid->getUid())){
			$profhtml['saldi']=$this->lid->getSaldi();
		}
		require_once 'class.saldi.php';
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

		$profiel->assign('profhtml', $profhtml);
		$profiel->assign('isOudlid', $this->lid->getStatus()=='S_OUDLID');

		$profiel->assign('magBewerken', (LoginLid::instance()->hasPermission('P_PROFIEL_EDIT') AND LoginLid::instance()->isSelf($this->_profiel['uid'])) OR LoginLid::instance()->hasPermission('P_LEDEN_EDIT'));
		$profiel->assign('isAdmin', LoginLid::instance()->hasPermission('P_ADMIN'));
		$profiel->assign('melding', $this->getMelding());

		//eigen profiel niet cachen, dan krijgen we namelijk rare dingen
		//dat we andermans saldo's zien enzo
		if(LoginLid::instance()->isSelf($this->lid->getUid())){
			$profiel->caching=false;
		}
		$template='profiel.tpl';
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
		echo '<h2>Profiel wijzigen</h2>
			Hieronder kunt u uw eigen gegevens wijzigen. Voor enkele velden is het niet mogelijk zelf
			wijzigingen door te voeren. Voor de meeste velden geldt daarnaast dat de ingevulde gegevens
			een geldig formaat moeten hebben. Mochten er fouten in het gedeelte van uw profiel staan,
			dat u niet zelf kunt wijzigen, meld het dan bij de <a href="mailto:vice-abactis@csrdelft.nl">Vice-Abactis</a>. <br /> <br />Als er
			<span class="waarschuwing">tekst in rode letters</span> wordt afgebeeld bij een veld, dan
			betekent dat dat de invoer niet geaccepteerd is, en dat u die zult moeten moeten aanpassen aan het
			gevraagde formaat. Een aantal velden kan leeg gelaten worden als er geen zinvolle informatie voor is.';

		echo '<form action="/communicatie/profiel/'.$this->profiel->getLid()->getUid().'/bewerken/" id="profielForm" method="post">';
		$form=$this->profiel->getFields();
		foreach($form as $field){
			echo $field->view();
		}
		echo '<div class="submit"><label for="submit">&nbsp;</label><input type="submit" value="opslaan" /> <input type="reset" value="reset formulier" /></div>';
		echo '</form>';

		
	}
}
?>
