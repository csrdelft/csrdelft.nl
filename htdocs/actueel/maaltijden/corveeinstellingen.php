<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/actueel/maaltijden/corveeinstellingen
# -------------------------------------------------------------------
# Hier worden instellingen van corveesysteem weergegeven en aangepast.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'maaltijden/corveeinstellingen.class.php';
require_once 'maaltijden/corveeinstellingencontent.class.php';


// Deze pagina is alleen voor de maalcie bedoeld.
if(!$loginlid->hasPermission('P_MAAL_MOD')){ header('location: '.CSR_ROOT.'actueel/maaltijden/'); exit; }



$meldingresetter = '';
/* 
 * Selecteert en voert acties uit voor de jaarlijkse corveereset
 * 
 * selecteert uit deze acties: controleren, controleren_ongeldigedatum, resetcorveejaar, resetmislukt
 */
if(isset($_POST['resetactie'])){
	if(in_array($_POST['resetactie'], array('controleren','resetcorveejaar'))){
		$actie = $_POST['resetactie'];
		$corveeresetter = new CorveeResetter();

		if(isDatumValid()){
			$corveeresetter->setDatum(getDatum());
			if($actie=='resetcorveejaar'){
				if($corveeresetter->verwijderCorveetaken()){
					msg('Taken verwijderen is gelukt.', 1);
				}else{
					$actie = 'resetmislukt';
				}
				if($corveeresetter->resetCorveeJaar()){
					msg('Corvee- en bonuspunten zijn bijgewerkt.', 1);
				}else{
					$actie = 'resetmislukt';
				}
			}
		}else{
			if($actie=='resetcorveejaar'){
				$meldingresetter .= ' Geen reset uitgevoerd!';
			}
			$actie = 'controleren_ongeldigedatum';
		}

		$resetter = new CorveeresetterContent($corveeresetter);
		$resetter->setMelding($meldingresetter.' '.$corveeresetter->getMelding());
		$resetter->setAction($actie);
		$resetter->view();
	}
	exit;

}else{
	//corveeinstellingen pagina weergeven
	$corveeinstellingen = new Corveeinstellingen();

	if($corveeinstellingen->isPostedFields() AND $corveeinstellingen->validFields() AND $corveeinstellingen->saveFields()){
		msg('Wijzigingen zijn opgeslagen', 1);
	}

	$instellingen = new CorveeinstellingenContent($corveeinstellingen);
	$instellingen->setMelding($corveeinstellingen->getError());

	$page=new csrdelft($instellingen);
	$page->addStylesheet('maaltijd.css');
	$page->addScript('maaltijd.js');
	$page->view();
}
/*
 * Controleert of datumvelden van corveeresetter zijn gepost
 * @return bool geslaagd?
 */
function isDatumPosted(){
	return isset($_POST['eindcorveeperiode_jaar'], $_POST['eindcorveeperiode_maand'], $_POST['eindcorveeperiode_dag']);
}
/*
 * Geeft datum uit de datumvelden van corveeresetter
 * @return string datum uit velden of '1961-01-01'
 */
function getDatum(){
	if(isDatumPosted()){
		return $_POST['eindcorveeperiode_jaar'].'-'.$_POST['eindcorveeperiode_maand'].'-'.$_POST['eindcorveeperiode_dag'];
	}else{
		return '1961-01-01';
	}
}
/*
 * Valideert datum uit datumvelden van corveeresetter
 * @return bool geldige datum?
 */
function isDatumValid(){
	if(isDatumPosted()){
		global $meldingresetter;
		$datum=getDatum();
		if(!preg_match('/^(\d{4})-(\d\d?)-(\d\d?)$/', $datum)){
			$meldingresetter='Ongeldige datum.';
		}elseif(substr($datum, 0, 4)>(date('Y')+3)){
			$meldingresetter='Er kunnen geen data later dan '.(date('Y')+3).' worden weergegeven.';
		}elseif(substr($datum, 0, 4)<(date('Y')-3)){
			$meldingresetter='Er kunnen geen data eerder dan '.(date('Y')-3).' worden weergegeven.';
		}
		if($meldingresetter==''){
			//alles goed
			return true;
		}
	}else{
		$meldingresetter = 'Geen (volledige) datum ingevuld.';
	}
	return false;
}

?>
