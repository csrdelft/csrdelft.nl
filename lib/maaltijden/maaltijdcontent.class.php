<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/class.maaltijdcontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van maaltijdinschrijving en abonnementen
# -------------------------------------------------------------------


require_once 'maaltijden/maaltijd.class.php';
require_once 'maaltijden/maaltrack.class.php';

class MaaltijdContent extends SimpleHTML {

	private $_maaltrack;

	function __construct($maaltrack) {
		$this->_maaltrack=$maaltrack;
	}
	function getTitel(){ return 'Maaltijdketzer'; }

	function view(){
		$loginlid=LoginLid::instance();

		//de html template in elkaar draaien en weergeven
		$profiel=new Smarty_csr();
		$profiel->caching=false;

		//Dingen ophalen voor....
		//...de eigen aanmeldingen
		$nu=time();
		$aMaal['zelf']['error']=$this->_maaltrack->getError();
		//We halen vanaf $nu-7200 op zodat ook maaltijden die al bezig zijn getoond worden.
		$aMaal['zelf']['maaltijden']=$this->_maaltrack->getMaaltijden($nu-7200, $nu+MAALTIJD_LIJST_MAX_TOT);

		//...de abonnementen
		$aMaal['abo']['abos']=$this->_maaltrack->getAbo();
		$aMaal['abo']['nietAbos']=$geenabo = $this->_maaltrack->getNotAboSoort();

		//...het aanmelden van andere verenigingsleden
		$aMaal['anderen']['error']=$this->_maaltrack->getProxyError();
		$aMaal['anderen']['maaltijden']=$this->_maaltrack->getMaaltijden($nu, $nu+MAALTIJD_PROXY_MAX_TOT);
		//de door het huidige lid aangemelde leden ophalen voor de opgehaalde maaltijden...
		for($i=0; $i<count($aMaal['anderen']['maaltijden']); $i++){
			$maalID=$aMaal['anderen']['maaltijden'][$i]['id'];
			$anderen=$this->_maaltrack->getProxyAanmeldingen($loginlid->getUid(), $maalID);
			if(count($anderen)==0){
				$aMaal['anderen']['maaltijden'][$i]['derden']=false;
			}else{
				$aMaal['anderen']['maaltijden'][$i]['derden']=$anderen;
			}
		}

		//arrays toewijzen en weergeven
		$profiel->assign('maal', $aMaal);
		$profiel->assign('toonLijsten', $loginlid->hasPermission('P_MAAL_MOD') or opConfide());
		$profiel->assign('datumFormaat', '%a %e %b %H:%M');
		$profiel->display('maaltijdketzer/maaltijdketzer.tpl');
	}

	public static function getMaaltijdubbtag($maalid='next'){
		//als de parameter 'next' is dan geven we de eerstvolgende maaltijd weer.
		if($maalid=='next'){
			$maaltijden=Maaltrack::getMaaltijdenRaw();
			if(count($maaltijden)>0){
				$maalid=$maaltijden[0]['id'];
			}else{
				return 'Geen aankomende maaltijd.';
			}
		}

		# bestaat de maaltijd?
		$maaltrack = new MaalTrack();           
		if (!$maaltrack->isMaaltijd($maalid)){
			return '[maaltijd] '.$maaltrack->getError().' (id: '.mb_htmlentities($maalid).')';
		}

		$maaltijd = new Maaltijd((int)$maalid);

		$html='<div class="ubb_block ubb_maaltijd" id="maaltijd'.$maaltijd->getID().'">';
		if(LoginLid::instance()->hasPermission('P_LOGGED_IN')){
			$html.='<div class="aanmelddata">';
			$html.='U komt:  <br />';

			$status=$maaltijd->getStatus();
			switch($status){
				case 'AAN':
					$html.='<em>eten</em>';
				break;
				case 'AUTO':
					if($maaltijd->heeftAbo()){
						$html.='<em>eten (abo)</em>';
						$status='AAN';
						break;
					}

				case 'AF':
				default:
					$html.='<em>niet eten</em>';
			}
			$html.='<br />';
			if($maaltijd->isGesloten()){
				$html.='Gesloten';
			}elseif($maaltijd->isVol()){
				$html.='Vol';
			}else{
				if(LoginLid::instance()->hasPermission('P_MAAL_IK')){
					switch($status){
						case 'AAN':
							$html.='<a href="'.CSR_ROOT.'actueel/maaltijden/index.php?forum&amp;a=af&amp;m='.$maaltijd->getId().'"><strong>af</strong>melden</a>';
						break;
						case 'AF':
						default:
							$html.='<a href="'.CSR_ROOT.'actueel/maaltijden/index.php?forum&amp;a=aan&amp;m='.$maaltijd->getId().'"><strong>aan</strong>melden</a>';
						break;
					}
				}
			}
			$html.='</div>';
		}
		$html.='<h2>';
		if(LoginLid::instance()->hasPermission('P_MAAL_MOD') or opConfide()){
			$html.='<a href="'.CSR_ROOT.'actueel/maaltijden/lijst/'.$maaltijd->getId().'" title="Direct naar de maaltijdlijst">Maaltijd</a>';
		}else{
			$html.='<a href="'.CSR_ROOT.'actueel/maaltijden/index.php">Maaltijd</a>';
		}
		$html.=' van '.strftime('%a %e %B %H:%M', strtotime($maaltijd->getMoment())).'</h2>';
		$html.=$maaltijd->getTekst().'<br />';
		$html.='<span class="small">Inschrijvingen: <em>'.$maaltijd->getAantalAanmeldingen(). '</em> van <em>'.$maaltijd->getMaxAanmeldingen().'</em></span>';
		$html.='</div><br class="clear" />';
		return $html;
	}
}

?>
