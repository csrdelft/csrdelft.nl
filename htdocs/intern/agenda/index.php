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
		if($lid->hasPermission('P_AGENDA_MOD'){
			require_once('class.agendabeheercontent.php');
			$midden = new AgendaBeheerContent($lid, $agenda);
		}
		elseif($lid->hasPermission('P_AGENDA_POST'){
			require_once('class.agendabeheercontent.php');
			$midden = new AgendaBeheerContent($lid, $agenda);
		}
		else{
			require_once('class.agendacontent.php');
			$midden = new AgendaContent($lid, $agenda);
		}
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
