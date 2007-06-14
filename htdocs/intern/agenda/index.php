<?php

require_once('include.config.php');

# MaaltijdenSysteem
require_once('class.agenda.php');
require_once('class.agendapunt.php');
$agenda = new Agenda($lid, $db);

# volgende code gejat uit profiel.php:
# Een error-waarde houden we bij om zodadelijk evt. een foutmelding
# te kunnen laden in plaats van de profiel pagina omdat er geen
# toegang wordt verleend voor de actie die gevraagd wordt.
$error = 0;
# 0 -> gaat goed
# 1 -> mag niet, foutpagina afbeelden
# 2 -> er treden (vorm)fouten op in bijv de invoer.

if(getOrPost("action") != ''){
	$action = getOrPost("action");
	if($action == "add"){
		if(isset($_POST['datum']) && isset($_POST['tijd']) && isset($_POST['tekst'])){
			if($lid->hasPermission('P_AGENDA_POST')){
				$tijd = strtotime(date('d F Y',$_POST['datum']).' '.$_POST['tijd']);
				if(!$agenda->addAgendaPunt($tijd, $_POST['tekst'])){
					$error = 2;
				}
			}
		}
	}
	elseif($action == "edit"){
		if(isset($_POST['id']) && isset($_POST['datum']) && isset($_POST['tijd']) && isset($_POST['tekst'])){
			if($lid->hasPermission('P_AGENDA_MOD')){
				$tijd = strtotime(date("d F Y",$_POST['datum']).' '.$_POST['tijd']);
				if(!$agenda->editAgendaPunt($_POST['id'], $tijd, $_POST['tekst'])){
					$error = 2;
				}
			}
		}
	}
	elseif($action == "del"){
		if(isset($_GET['id'])){
			if($lid->hasPermission('P_AGENDA_MOD')){
				if(!$agenda->removeAgendaPunt($_GET['id'])){
					$error = 2;
				}
			}
		}
	}
	
	if($error == 2){
		echo "Error: de actie is mislukt.";
	}
	else{
		header("Location: {$_SERVER['PHP_SELF']}");
		exit;
	}
}
	

if(getOrPost("mode") != ''){
	$mode = getOrPost("mode");
	if($mode == "add"){
		if($lid->hasPermission('P_AGENDA_POST')){
			require_once('class.agendatoevoegcontent.php');
			$midden = new AgendaToevoegContent($lid, $agenda);
		}
		else{
			$midden = new Includer('', 'geentoegang.html');
		}
	}
	elseif($mode == "edit"){
		if($lid->hasPermission('P_AGENDA_MOD')){
			require_once('class.agendawijzigcontent.php');
			$midden = new AgendaWijzigContent($lid, $agenda);
		}
		else{
			$midden = new Includer('', 'geentoegang.html');
		}
	}
	else{
		$midden = new Includer('', 'geentoegang.html');
	}
}
else{
	if(!$lid->hasPermission('P_AGENDA_READ')){ $error = 1; }

	# De pagina opbouwen, met mKetzer, of met foutmelding
	if($error == 0  or $error == 2) {
		# Het middenstuk
		require_once('class.agendacontent.php');
		$midden = new AgendaContent($lid, $agenda);
	} else {
		# geen rechten
		$midden = new Includer('', 'geentoegang.html');
	}
}
$zijkolom=new kolom();

$page=new csrdelft($midden, $lid, $db);
$page->setZijkolom($zijkolom);
$page->view();


?>
