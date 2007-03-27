<?php

# Let the browser and proxies cache output
session_cache_limiter('public');
# Een uur (30, in minutes) cache expiration time for output
session_cache_expire(30);

# instellingen & rommeltjes
require_once('include.config.php');


if ($lid->hasPermission('P_LEDEN_READ') or $lid->hasPermission('P_OUDLEDEN_READ')) {
	# Het middenstuk
	require_once('class.ledenlijstcontent.php');
	$midden = new LedenlijstContent($lid);

	$form = array();
		
	# er is een zoekopdracht opgegeven, we gaan nu de parameters bekijken
	# eerst de zoekterm ophalen
	# als 'wat' leeg is, dan wordt er naar alle leden gezocht
	$form['wat'] = (isset($_POST['wat'])) ? $_POST['wat'] : '';
		
	# in welke kolom van de tabel gezocht wordt...
	# als er niets geldigs is opgegeven, dan op voornaam zoeken
	$kolommen = array('uid', 'naam','nickname','voornaam','achternaam','adres','telefoon','mobiel','email','kring', 'studie', 'gebdatum');
	$form['waar'] = (isset($_POST['waar']) and in_array($_POST['waar'],$kolommen)) ? $_POST['waar'] : 'naam';

	# zoek in een bepaalde moot (0=alle)
	$moten = array('alle','1','2','3','4');
	$form['moot'] = (isset($_POST['moot']) and in_array($_POST['moot'],$moten)) ? $_POST['moot'] : 'alle';

	# voor gebruikers die leden en oudleden kunnen zoeken
	$zoek_in_type = array('(oud)?leden','leden','oudleden');
	# deze optie kan ook via GET, zodoende is te sturen dat er via de (oud)ledenlijst default in (oud)leden
	# gezocht wordt, zonder dat er een POST is verzonden
	if (isset($_GET['status']) and in_array($_GET['status'],$zoek_in_type)) $form['status'] = $_GET['status'];
	else $form['status'] = (isset($_POST['status']) and in_array($_POST['status'],$zoek_in_type)) ? $_POST['status'] : '';

	# kolom waarop gesorteerd wordt
	$kolommen = array('uid','voornaam','achternaam','email','adres','telefoon','mobiel', 'studie', 'gebdatum');
	$form['sort'] = (isset($_POST['sort']) and in_array($_POST['sort'],$kolommen)) ? $_POST['sort'] : 'achternaam';
		
	# kolommen die afgebeeld kunnen worden
	$kolommen = array('uid', 'pasfoto', 'nickname','moot','email','adres','telefoon','mobiel','icq','msn','skype','studie','gebdatum');
	$form['kolom'] = array();
	# kijken of er geldige kolommen zijn opgegeven
	if (isset($_POST['kolom']) and is_array($_POST['kolom']) and count($_POST['kolom']) > 0) {
		$form['kolom'] = array_intersect($_POST['kolom'], $kolommen);
	} else {
		# als er geen enkele geldige waarde was zelf een voorstel doen
		# N.B. naam wordt altijd al afgebeeld
		$form['kolom'] = array('adres', 'email', 'telefoon', 'mobiel');
	}

	# zoekwaarden voor het formulier aan het content-object mededelen
	$midden->setForm($form);

	# we gaan kijken of er een zoek-opdracht is gegeven
	# zo ja, dan gaan we die straks uitvoeren, en zetten we de ingevulde waarden ook weer
	# terug in de invulvelden
	if (isset($_POST['a']) and $_POST['a'] == 'zoek') {
		# en zoeken dan maar...
		$midden->setResult($lid->zoekLeden($form['wat'], $form['waar'], $form['moot'], $form['sort'], $form['status']));
	}
} else {
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}	

## zijbalk in elkaar rossen
	$zijkolom=new kolom();
	# Komende 10 verjaardagen erbij
	if($lid->hasPermission('P_LOGGED_IN')) {
		require_once('class.verjaardagcontent.php');
		
		$verjaardagcontent=new VerjaardagContent($lid, 'komende10');
		$zijkolom->add($verjaardagcontent);
	}

## pagina weergeven
	$pagina=new csrdelft($midden, $lid, $db);
	$pagina->setZijkolom($zijkolom);
	$pagina->view();


?>
