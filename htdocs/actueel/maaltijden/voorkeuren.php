<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/actueel/maaltijden/voorkeuren.php
# -------------------------------------------------------------------
# Voorkeuren voor maaltijden en corvee opgeven
# -------------------------------------------------------------------

require_once 'include.config.php';

# MaaltijdenSysteem
require_once 'maaltijden/class.maaltrack.php';
require_once 'maaltijden/class.maaltijd.php';
$maaltrack = new MaalTrack();

# Moeten er acties uitgevoerd worden?
$action=getOrPost('a');

# volgende code gejat uit profiel.php:
# Een error-waarde houden we bij om zodadelijk evt. een foutmelding
# te kunnen laden in plaats van de profiel pagina omdat er geen
# toegang wordt verleend voor de actie die gevraagd wordt.
$error = 0;
# 0 -> gaat goed
# 1 -> mag niet, foutpagina afbeelden
# 2 -> er treden (vorm)fouten op in bijv de invoer.

# controleren of we wel mogen doen wat er gevraagd wordt...
$actionsToegestaan=array('', 'editEetwens', 'editCorveewens', 'editCorveevoorkeuren', 'addabo', 'delabo');
if(in_array($action, $actionsToegestaan)){
	if(!$loginlid->hasPermission('P_MAAL_IK')){ $error = 1; }
}else{
	# geen geklooi met andere waarden
	$error = 1;
}

# als er geen error is, dan kunnen we de actie uit gaan voeren
if ($error == 0) switch($action) {
	case 'addabo':
		# kijk of er een abo is opgegeven
		$abo=getOrPost('abo');
		if(!$maaltrack->addabo($abo)){
			$error=2;
		}else{
			header("Location: {$_SERVER['PHP_SELF']}");
			exit;
		}
	break;
	case 'delabo':
		# kijk of er een abo is opgegeven
		$abo=getOrPost('abo');
		if(!$maaltrack->delabo($abo)){
			$error=2;
		}else{
			header("Location: {$_SERVER['PHP_SELF']}");
			exit;
		}
	break;
	case 'editEetwens':
		$lid=$loginlid->getLid();
		$lid->setProperty('eetwens', getOrPost('eetwens'));
		if(!$lid->save()){
			$error=2;
		}else{
			header("Location: {$_SERVER['PHP_SELF']}");
			exit;
		}
	break;
	case 'editCorveevoorkeuren':
		$lid=$loginlid->getLid();
		$voorkeuren_array = getOrPost('corvee_voorkeuren');
		$voorkeuren = (isset($voorkeuren_array['ma_kok']) ? 1 : 0);
		$voorkeuren .= (isset($voorkeuren_array['ma_afwas']) ? 1 : 0);
		$voorkeuren .= (isset($voorkeuren_array['do_kok']) ? 1 : 0);
		$voorkeuren .= (isset($voorkeuren_array['do_afwas']) ? 1 : 0);
		$voorkeuren .= (isset($voorkeuren_array['theedoek']) ? 1 : 0);
		$voorkeuren .= (isset($voorkeuren_array['afzuigkap']) ? 1 : 0);
		$voorkeuren .= (isset($voorkeuren_array['frituur']) ? 1 : 0);
		$voorkeuren .= (isset($voorkeuren_array['keuken']) ? 1 : 0);
		if(!intval($voorkeuren) > 0) {
			$error=2;
		}else{
			$lid->setProperty('corvee_voorkeuren', $voorkeuren);
			if(!$lid->save()){
				$error=2;
			}else{
				header("Location: {$_SERVER['PHP_SELF']}");
				exit;
			}
		}
	break;
}


if ($error == 0  or $error == 2) {
	require_once 'maaltijden/class.maaltijdvoorkeurcontent.php';
	$midden = new MaaltijdVoorkeurContent($maaltrack);
}else{
	require_once 'class.paginacontent.php';
	$midden=new PaginaContent(new Pagina('maaltijden'));
	$midden->setActie('bekijken');
}

$page=new csrdelft($midden);
$page->view();


?>
