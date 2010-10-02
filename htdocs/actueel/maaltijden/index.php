<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/actueel/maaltijden/index.php
# -------------------------------------------------------------------
# Aanmelden en afmelden voor maaltijden.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'paginacontent.class.php';

# MaaltijdenSysteem
require_once 'maaltijden/class.maaltrack.class.php';
require_once 'maaltijden/class.maaltijd.class.php';
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
$actionsToegestaan=array('', 'aan', 'af', 'gasten');
if(in_array($action, $actionsToegestaan)){
	if(!$loginlid->hasPermission('P_MAAL_IK')){ $error = 1; }
}else{
	# geen geklooi met andere waarden
	$error = 1;
}

# als er geen error is, dan kunnen we de actie uit gaan voeren
if ($error == 0) switch($action) {
	case 'aan':
		# kijk of een maaltijd is opgegeven
		$m=getOrPost('m');
		# kijk of er extra permissies nodig zijn als we iemand anders
		# aan willen melden
		$uid=getOrPost('uid');
		if($uid != '' and $uid != $loginlid->getUid() AND !$loginlid->hasPermission('P_MAAL_WIJ') ){
			$error = 1;
		}else{
			# ga maar proberen dan...
			if(!$maaltrack->aanmelden($m, $uid)){
				$error=2;
			}else{
				if(isset($_GET['forum'])){
					header("Location: ".$_SERVER['HTTP_REFERER'].'#maaltijd'.$m);
					exit;
				}
				header("Location: {$_SERVER['PHP_SELF']}");
				exit;
			}
		}

	break;
	case 'af':
		# kijk of een maaltijd is opgegeven
		$m=getOrPost('m');
		# kijk of er extra permissies nodig zijn als we iemand anders
		# af willen melden
		$uid=getOrPost('uid');
		if($uid != '' and $uid != $loginlid->getUid() AND !$loginlid->hasPermission('P_MAAL_WIJ') ){
			$error = 1;
		}else{
			# ga maar proberen dan...
			if(!$maaltrack->afmelden($m, $uid)){
				$error=2;
			}else{
				if(isset($_GET['forum'])){
					header("Location: ".$_SERVER['HTTP_REFERER'].'#maaltijd'.$m);
					exit;
				}
				header("Location: {$_SERVER['PHP_SELF']}");
				exit;
			}
		}
	break;
	case 'gasten':
		# kijk of een maaltijd is opgegeven
		$m=getOrPost('m');
		# gastvariabelen ophalen
		$gasten=getOrPost('gasten');
		$opmerking=getOrPost('opmerking');
		# ga maar proberen dan...
		if(!$maaltrack->gastenAanmelden($m, $gasten, $opmerking)){
			$error=2;
		}else{
			header("Location: {$_SERVER['PHP_SELF']}");
			exit;
		}
	break;
}

# De pagina opbouwen, met mKetzer, of met foutmelding
if($error == 0  or $error == 2) {
	# Het middenstuk
	require_once 'maaltijden/maaltijdcontent.class.php';
	$midden = new MaaltijdContent($maaltrack);
} else {
	# geen rechten
	$pagina=new Pagina('maaltijden');
	$midden=new PaginaContent($pagina);
	$midden->setActie('bekijken');
}

$page=new csrdelft($midden);
$page->view();


?>
