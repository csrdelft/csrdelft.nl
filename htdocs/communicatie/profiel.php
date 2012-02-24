<?php

# C.S.R. Delft
# Hans van Kranenburg
# sep 2005

/*
 * Even wat uitleg over het toevoegen van nieuwe leden:
 * Door naar de url http://csrdelft.nl/communicatie/profiel/0000/nieuwLid/ te gaan wordt er een
 * nieuw uid aangemaakt in het huidige jaar. Vervolgens wordt de browser meteen naar het
 * bewerken van het nieuwe profiel gestuurd, waar de gegevens van de noviet ingevoerd kunnen
 * worden. De code daarvoor is gelijk aan die van het bewerken van een bestaand profiel, met
 * een ander tekstje erboven. Ook worden de wachtwoordvelden en het bijnaamveld nog niet
 * weergeven.
 * 
 */

require_once 'configuratie.include.php';

require_once 'lid/profiel.class.php';

if(isset($_GET['uid'])){
	$uid=$_GET['uid'];
}else{
	$uid=$loginlid->getUid();
}

//welke actie gaan we doen?
if(isset($_GET['a'])){
	$actie=$_GET['a'];
	//is er een status opgegeven
	if(isset($_GET['s'])){
		$status=$_GET['s'];
	}else{
		$status=null;
	}
}else{
	//default-actie.
	$actie='view';
}


if(!($loginlid->hasPermission('P_LEDEN_READ') or $loginlid->hasPermission('P_OUDLEDEN_READ'))){
	require_once 'paginacontent.class.php';
	$midden=new PaginaContent(new Pagina('geentoegang'));
	$midden->setActie('bekijken');
}else{
	require_once 'lid/profielcontent.class.php';
	require_once 'lid/profiel.class.php';
	
	switch($actie){
		case 'novietBewerken':
		case 'bewerken':
			$profiel=new ProfielBewerken($uid, $actie);
			
			if($profiel->magBewerken()){
				if($profiel->valid() AND $profiel->save()){
					header('location: '.CSR_ROOT.'communicatie/profiel/'.$uid);
					exit;
				}else{
					$midden=new ProfielEditContent($profiel, $actie);
				}
			}else{
				$midden=new ProfielContent(LidCache::getLid($uid));
			}
		break;
		case 'nieuw':
			if(!
				($loginlid->hasPermission('P_ADMIN,P_LEDEN_MOD') OR
				($status=='noviet' AND $loginlid->hasPermission('groep:novcie')))
			  ){

				// nieuwe leden mogen worden aangemaakt door P_ADMIN,P_LEDEN_MOD,
				// novieten ook door de novcie.
				ProfielContent::invokeRefresh('U mag geen nieuwe leden aanmaken', '/communicatie/profiel/');
			}
			try{
				//maak het nieuwe uid aan.
				$nieuwUid = Lid::createNew($_GET['uid'],$status);

				if($status=='noviet'){
					$bewerkactie = 'novietBewerken';
				}else{
					$bewerkactie = 'bewerken';
				}
				ProfielContent::invokeRefresh(null, '/communicatie/profiel/'.$nieuwUid.'/'.$bewerkactie);
			}catch(Exception $e){
				ProfielContent::invokeRefresh('<h2>Nieuw lidnummer aanmaken mislukt.</h2>'.$e->getMessage(), '/communicatie/profiel/');
			}	
		break;
		case 'wijzigstatus':
			if(!$loginlid->hasPermission('P_ADMIN,P_LEDEN_MOD')){
				ProfielContent::invokeRefresh('U mag lidstatus niet aanpassen', '/communicatie/profiel/');
			}
			$profiel=new ProfielStatus($uid, $actie);

			if($profiel->isPosted() AND $profiel->valid() AND $profiel->save()){
				header('location: '.CSR_ROOT.'communicatie/profiel/'.$uid);
				exit;
			}else{
				$midden=new ProfielStatusContent($profiel, $actie);
			}
		break;
		case 'wachtwoord':
			if($loginlid->hasPermission('P_ADMIN')){
				if(Profiel::resetWachtwoord($uid)){
					$melding='Nieuw wachtwoord met succes verzonden.';
				}else{
					$melding='Wachtwoord resetten mislukt.';
				}
			}
			ProfielContent::invokeRefresh($melding, '/communicatie/profiel/'.$uid);
		break;
		case 'addToGoogleContacts';
			require_once('googlesync.class.php');
			GoogleSync::doRequestToken(CSR_ROOT.'communicatie/profiel/'.$uid.'/addToGoogleContacts');
			
			$gSync=GoogleSync::instance();
			$message=$gSync->syncLid($uid);
			ProfielContent::invokeRefresh('<h2>Opgeslagen in Google Contacts:</h2>'.$message, CSR_ROOT.'communicatie/profiel/'.$uid);
			exit;
		break;
		case 'rssToken':
			if($uid==$loginlid->getUid()){
				$loginlid->getToken();
				header('location: '.CSR_ROOT.'communicatie/profiel/'.$uid.'#forum');
				exit;
			}
		//geen break hier, want als de bovenstaande actie aangevraagd werd voor de
		//niet-huidige gebruiker, doen we gewoon een normale view.
		case 'view':
		default;
			$lid=LidCache::getLid($uid);
			if(!$lid instanceof Lid){
				ProfielContent::invokeRefresh('<h2>Helaas</h2>Dit lid bestaat niet.<br /> U kunt verder zoeken in deze ledenlijst.', '/communicatie/ledenlijst/');
			}
			$midden=new ProfielContent($lid);
		break;
		
	}
}

$pagina=new csrdelft($midden);
$pagina->addStylesheet('profiel.css');
$pagina->addStylesheet('js/autocomplete/jquery.autocomplete.css');
$pagina->addScript('profiel.js');

$pagina->addScript('autocomplete/jquery.autocomplete.min.js');
if($actie=='view'){
	$pagina->addScript('flot/jquery.flot.min.js');
	$pagina->addScript('flot/jquery.flot.threshold.min.js');
}
$pagina->view();

?>
