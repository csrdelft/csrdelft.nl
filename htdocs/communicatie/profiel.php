<?php

# C.S.R. Delft
# Hans van Kranenburg
# sep 2005

# /leden/profiel.php


# instellingen & rommeltjes
require_once('include.config.php');

//$lid vervangen door een subklasse ervan, met functies voor het profiel
require_once('class.profiel.php');
$lid=new Profiel();

# Profiel bekijken
# met P_LOGGED_IN mag een gebruiker zijn eigen profiel bekijken
# met P_LEDEN_READ mag een gebruiker profielen van andere leden bekijken
# met P_PROFIEL_EDIT mag een gebruiker zijn eigen profiel wijzigen
# met P_LEDEN_EDIT mag een gebruiker profielen van anderen wijzigen

# ophalen uid die meegegeven is
# NB uid zit altijd in de URL als het niet de uid van de gebruiker zelf is!
if(isset($_GET['uid'])){ 
	$uid = $_GET['uid'];
}else{
	$uid = $lid->getUid();
}

# we gaan dingen met acties doen...
require_once("class.state.php");
# maak een Status aan, dit ene object wordt overal geraadpleegd. met
# het veranderen van de status binnen dit object verandert de hele
# pagina. State bevat een URL die naar de pagina zelf verwijst en waar
# alleen nog maar a=<actie> aan toegevoegd hoeft te worden.
# daarnaast bevat het dus de actie waar we mee bezig zijn, en die
# in de content-klassen bepaalt wat er wel of niet wordt getoond
$state = new State('none', "/communicatie/profiel/{$uid}");

# zijn we met beheer bezig?
if(isset($_POST['a'])){ 
	$action = $_POST['a'];
}elseif(isset($_GET['a'])){
	$action = $_GET['a'];
}else{
	$action = 'none';
}

# Een error-waarde houden we bij om zodadelijk evt. een foutmelding
# te kunnen laden in plaats van de profiel pagina omdat er geen
# toegang wordt verleend voor de actie die gevraagd wordt.
$error = 0;
# 0 -> gaat goed
# 1 -> mag niet, foutpagina afbeelden
# 2 -> er treden (vorm)fouten op in bijv de invoer.

# controleren of we wel mogen doen wat er gevraagd wordt...
switch ($action) {
	case 'none':
		# Eigen profiel bekijken kan met P_LOGGED_IN, profiel van anderen
		# bekijken kan met P_LEDEN_READ en met P_OUDLEDEN_READ
		# oudleden kunnen dan ook leden bekijken en vice-versa, maar enkel
		# als ze uid's kennen.
		if ( !($lid->hasPermission('P_LOGGED_IN') and $uid == $lid->getUid()) and 
			!($lid->hasPermission('P_LEDEN_READ') or $lid->hasPermission('P_OUDLEDEN_READ') )){
			$error = 1;
		}
		break;
	case 'edit':
	case 'save':
		# wijzigen van spullen kan met P_PROFIEL_EDIT als de gevraagde
		# gebruiker dezelfde is als de ingelogde gebruiker, of met
		# P_LEDEN_EDIT
		
		# FIXME: duidelijkere opzet van statement hieronder. dit is te wazig
		if(!($lid->hasPermission('P_PROFIEL_EDIT') and $uid == $lid->getUid()) and !($lid->hasPermission('P_LEDEN_EDIT')) ){
			$error = 1;
		}
	break;
	case 'wachtwoord': 
		# wachtwoord resetten plus mail sturen, alleen als P_ADMIN
		if(!$lid->hasPermission('P_ADMIN')){
			$error=1;
		}
	break;
	default:
		# geen geklooi met andere waarden
		$error = 1;
}


# als er geen error is, dan kunnen we de actie uit gaan voeren
if ($error == 0){
	switch($action) {
		case 'none':
			# profiel inladen, als dat niet lukt dan mag het niet
			if (!$lid->loadSqlTmpProfile($uid)) $error = 1;
		break;
		case 'edit':
			# profiel inladen, als dat niet lukt dan mag het niet
			if ($lid->loadSqlTmpProfile($uid)) $state->setMyState('edit'); # zodat editvakken getoond worden
			else $error = 1;
		break;
		case 'save':
			# profiel inladen uit db, als dat niet lukt dan mag het niet
			if (!$lid->loadSqlTmpProfile($uid)) {
				$error = 1;
			}else{

				# profiel inladen uit POST, als dat niet lukt kan het zijn dat...
				# $error = 1 -> we een 'dat mag niet' pagina gaan afbeelden
				# $error = 2 -> doorgaan, en naar edit-mode, er moeten eerst fouten opgelost worden
				$error = $lid->loadPOSTTmpProfile();
				switch ($error) {
					case 0:
						# alle invoer was juist, wijzigingen doorvoeren.
						# deze functie doet:
						
						# - wijzigingen in SQL opslaan
						$lid->diff_to_sql();
						
						# - het profiel opnieuw in LDAP opslaan
						$lid->save_ldap();
						
						# om te voorkomen dat een refresh opnieuw een submit doet
						$myurl = $state->getMyUrl();
						header("Location: {$myurl}");
						exit;
					break;
					case 2:
						# er zaten fouten in de invoer, $lid weet welke fouten en
						# profielcontent zal die afbeelden
						$state->setMyState('edit'); 
					break;
					case 1:
						# geen-toegang pagina wordt hieronder ingevuld
					break;
				}
			}//end if $lid->loadSqlTmpProfile($uid)
		break;
		case 'wachtwoord':
			# Wachtwoord resetten, wordt nog geen bevestiging van gegeven.
			if($lid->resetWachtwoord($uid)){
				$_SESSION['melding']='Nieuw wachtwoord met succes verzonden.';
			}else{
				$_SESSION['melding']='Wachtwoord resetten mislukt.';
			}	
			header("Location: ".CSR_ROOT."intern/communicatie/profiel/".$uid); 
			exit;
		break;
	}//end switch $action
}//end if $error==0
# De pagina opbouwen, met profiel, of met foutmelding
switch ($error) {
	case 0:
	case 2:
		require_once('class.profielcontent.php');
		$midden = new ProfielContent($lid, $state);
		
	break;
	default:
		# geen rechten
		require_once 'class.paginacontent.php';
		$pagina=new Pagina('geentoegang');
		$midden = new PaginaContent($pagina);
}	
## zijbalk in elkaar rossen
$zijkolom=new kolom();

## pagina weergeven
	$pagina=new csrdelft($midden);
	$pagina->addStylesheet('profiel.css');
	$pagina->setZijkolom($zijkolom);
	$pagina->view();

?>
