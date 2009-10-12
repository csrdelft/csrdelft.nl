<?php

# instellingen & rommeltjes
require_once 'include.config.php';

if($loginlid->hasPermission('P_LOGGED_IN')){
	# Let the browser and proxies cache output
	session_cache_limiter('public');

	# Een uur (30, in minutes) cache expiration time for output (maar alleen als we ingelogged zijn, anders levert het vreemde fouten op met inloggen)
	session_cache_expire(30);
}




if($loginlid->hasPermission('P_LEDEN_READ') or $loginlid->hasPermission('P_OUDLEDEN_READ')) {
	# Het middenstuk
	require_once 'lid/class.ledenlijstcontent.php';
	$midden = new LedenlijstContent();

	$form = array();

	# er is een zoekopdracht opgegeven, we gaan nu de parameters bekijken
	# eerst de zoekterm ophalen
	# als 'wat' leeg is, dan wordt er naar alle leden gezocht
	$form['wat'] = (isset($_POST['wat'])) ? $_POST['wat'] : '';

	# in welke kolom van de tabel gezocht wordt...
	# als er niets geldigs is opgegeven, dan op voornaam zoeken
	$kolommen = array('uid', 'naam','nickname','voornaam','achternaam','adres','telefoon','mobiel','email','kring', 'studie', 'gebdatum', 'beroep', 'verticale');
	$form['waar'] = (isset($_POST['waar']) and in_array($_POST['waar'],$kolommen)) ? $_POST['waar'] : 'naam';

	# zoek in een bepaalde moot (0=alle)
	$moten = array('alle','A','B','C','D', 'E', 'F', 'G', 'H');
	if(isset($_POST['verticale']) and in_array($_POST['verticale'],$moten)){
		$form['verticale'] = array_search($_POST['verticale'], $moten);
	}else{
		$form['verticale'] = 'alle';
	}

	# voor gebruikers die leden en oudleden kunnen zoeken
	$zoek_in_type = array('(oud)?leden','leden','oudleden');
	# de VAB mag ook nobodies zoeken
	if($loginlid->hasPermission('P_OUDLEDEN_MOD')) {
		$zoek_in_type[] = 'nobodies';
	}

	# deze optie kan ook via GET, zodoende is te sturen dat er via de (oud)ledenlijst default in (oud)leden
	# gezocht wordt, zonder dat er een POST is verzonden
	if (isset($_GET['status']) and in_array($_GET['status'],$zoek_in_type)) $form['status'] = $_GET['status'];
	else $form['status'] = (isset($_POST['status']) and in_array($_POST['status'],$zoek_in_type)) ? $_POST['status'] : '';

	# kolom waarop gesorteerd wordt
	$kolommen = array('uid', 'voornaam', 'achternaam', 'email', 'adres', 'telefoon', 'mobiel', 'studie', 'gebdatum', 'beroep', 'verticale');
	$form['sort'] = (isset($_POST['sort']) and in_array($_POST['sort'],$kolommen)) ? $_POST['sort'] : 'achternaam';

	# kolommen die afgebeeld kunnen worden
	$kolommen = array('uid', 'pasfoto', 'nickname', 'verticale', 'email', 'adres', 'telefoon', 'mobiel', 'icq', 'msn', 'skype', 'studie', 'gebdatum', 'beroep', 'lidjaar');
	$form['kolom'] = array();
	# kijken of er geldige kolommen zijn opgegeven
	if (isset($_POST['kolom']) and is_array($_POST['kolom']) and count($_POST['kolom']) > 0) {
		$form['kolom'] = array_intersect($_POST['kolom'], $kolommen);
	} else {
		# als er geen enkele geldige waarde was zelf een voorstel doen
		# N.B. naam wordt altijd al afgebeeld
		$form['kolom'] = array('adres', 'email', 'mobiel');
	}

	# zoekwaarden voor het formulier aan het content-object mededelen
	$midden->setForm($form);

	# we gaan kijken of er een zoek-opdracht is gegeven
	# zo ja, dan gaan we die straks uitvoeren, en zetten we de ingevulde waarden ook weer
	# terug in de invulvelden
	if (isset($_POST['a']) and $_POST['a'] == 'zoek') {
		# en zoeken dan maar...
		$aZoekresultaten = Zoeker::zoekLeden($form['wat'], $form['waar'], $form['verticale'], $form['sort'], $form['status']);
		# Als er maar 1 resultaat is redirecten we naar het profiel, en anders geven we een lijst met de resultaten
		if (sizeof($aZoekresultaten) == 1) {
			header('location: '.CSR_ROOT.'communicatie/profiel/'.$aZoekresultaten[0]['uid']);
		} else {
			$midden->setResult($aZoekresultaten);
		}
	}
} else {
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}


$pagina=new csrdelft($midden);

$pagina->addStylesheet('js/datatables/css/datatables_basic.css');
$pagina->addScript('jquery.js');
$pagina->addScript('datatables/jquery.dataTables.min.js');

$pagina->view();

?>
