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

require_once 'include.config.php';

require_once 'lid/class.profiel.php';

if(isset($_GET['uid'])){
	$uid=$_GET['uid'];
}else{
	$uid=$loginlid->getUid();
}

//welke actie gaan we doen?
if(isset($_GET['a'])){
	$actie=$_GET['a'];
}else{
	//default-actie.
	$actie='view';
}

if(!($loginlid->hasPermission('P_LEDEN_READ') or $loginlid->hasPermission('P_OUDLEDEN_READ'))){
	require_once 'class.paginacontent.php';
	$midden=new PaginaContent(new Pagina('geentoegang'));
	$midden->setActie('bekijken');
}else{
	require_once 'lid/class.profielcontent.php';
	require_once 'lid/class.profiel.php';
	
	switch($actie){
		case 'novietBewerken':
		case 'bewerken':
			$profiel=new Profiel($uid, $actie);
			
			if($profiel->magBewerken()){
				if($profiel->isPosted() AND $profiel->valid() AND $profiel->save()){
					header('location: '.CSR_ROOT.'communicatie/profiel/'.$uid);
				}else{
					$midden=new ProfielEditContent($profiel, $actie);
				}
			}else{
				$midden=new ProfielContent(LidCache::getLid($uid));
			}
		break;
		case 'nieuwlid':
		case 'nieuwLid':
			if($loginlid->hasPermission('P_ADMIN,P_LEDEN_MOD,groep:novcie')){
				try{
					//maak het nieuwe uid aan.
					$nieuwUid=Lid::createNew($_GET['uid']);

					ProfielContent::invokeRefresh(null, '/communicatie/profiel/'.$nieuwUid.'/novietBewerken');
				}catch(Exception $e){
					ProfielContent::invokeRefresh('<h2>Nieuw lidnummer aanmaken mislukt.</h2>'.$e->getMessage());
				}	
			}else{
				ProfielContent::invokeRefresh('U mag geen nieuwe leden aanmaken', '/communicatie/profiel/');
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
			if(isset($_GET['token'])){
				$_SESSION['google_token']=Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
			}
			if(!isset($_SESSION['google_token'])){
				$self=CSR_ROOT.'communicatie/profiel/'.$uid.'/addToGoogleContacts';
				$scope = 'http://www.google.com/m8/feeds';
				header('Location: '.Zend_Gdata_AuthSub::getAuthSubTokenUri($self, $scope, 0, 1));
				exit;
			}
			$gSync=new GoogleSync();
			$gSync->syncLid($uid);
			
			ProfielContent::invokeRefresh('<h2>Opgeslagen in Google Contacts</h2><a href="http://google.com/contacts">Ga naar Google contacts</a>', CSR_ROOT.'communicatie/profiel/'.$uid);
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
			if($lid instanceof Lid){
				$midden=new ProfielContent($lid);
			}else{
				ProfielContent::invokeRefresh('<h2>Helaas</h2>Dit lid bestaat niet.<br /> U kunt verder zoeken in deze ledenlijst.', '/communicatie/ledenlijst/');
			}
		break;
		
	}
}

$pagina=new csrdelft($midden);
$pagina->addStylesheet('profiel.css');
$pagina->addScript('profiel.js');
$pagina->addScript('suggest.js');
$pagina->addScript('jquery.js');
$pagina->addScript('flot/jquery.flot.min.js');
$pagina->addScript('flot/jquery.flot.threshold.min.js');
$pagina->view();

?>
