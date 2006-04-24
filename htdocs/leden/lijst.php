<?php

# prevent global namespace poisoning
main();
exit;

function main() {

	# Let the browser and proxies cache output
	session_cache_limiter('public');
	# Een uur (30, in minutes) cache expiration time for output
	session_cache_expire(30);

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

	# login-systeem
	require_once('class.lid.php');
	require_once('class.mysql.php');
	session_start();
	$db = new MySQL();
	$lid = new Lid($db);

	### Pagina-onderdelen ###

	# menu's
	require_once('class.dbmenu.php');
	$homemenu = new DBMenu('home', $lid, $db);
	$infomenu = new DBMenu('info', $lid, $db);
	if ($lid->hasPermission('P_LOGGED_IN')) $ledenmenu = new DBMenu('leden', $lid, $db);
	
	require_once('class.simplehtml.php');
	require_once('class.hok.php');
	$homemenuhok = new Hok($homemenu->getMenuTitel(), $homemenu);
	$infomenuhok = new Hok($infomenu->getMenuTitel(), $infomenu);
	if ($lid->isLoggedIn()) $ledenmenuhok = new Hok($ledenmenu->getMenuTitel(), $ledenmenu);

	require_once('class.loginform.php');
	$loginform = new LoginForm($lid);
	$loginhok = new Hok('Ledenlogin', $loginform);

	# Datum
	require_once('class.includer.php');
	$datum = new Includer('', 'datum.php');
	
	if ($lid->hasPermission('P_LEDEN_READ') or $lid->hasPermission('P_OUDLEDEN_READ')) {
		# Het middenstuk
		require_once('class.ledenlijstcontent.php');
		$midden = new LedenlijstContent($lid);

		# we gaan kijken of er een zoek-opdracht is gegeven
		# zo ja, dan gaan we die straks uitvoeren, en zetten we de ingevulde waarden ook weer
		# terug in de invulvelden
		if (isset($_POST['a']) and $_POST['a'] == 'zoek') {
			$form = array();
			
			# er is een zoekopdracht opgegeven, we gaan nu de parameters bekijken
			# eerst de zoekterm ophalen
			# als 'wat' leeg is, dan wordt er naar alle leden gezocht
			$form['wat'] = (isset($_POST['wat'])) ? $_POST['wat'] : '';
			
			# in welke kolom van de tabel gezocht wordt...
			# als er niets geldigs is opgegeven, dan op voornaam zoeken
			$kolommen = array('nickname','voornaam','achternaam','adres','telefoon','mobiel','email','kring');
			$form['waar'] = (isset($_POST['waar']) and in_array($_POST['waar'],$kolommen)) ? $_POST['waar'] : 'voornaam';

			# zoek in een bepaalde moot (0=alle)
			$moten = array('alle','1','2','3','4');
			$form['moot'] = (isset($_POST['moot']) and in_array($_POST['moot'],$moten)) ? $_POST['moot'] : 'alle';

			# voor gebruikers die leden en oudleden kunnen zoeken
			$zoek_in_type = array('(oud)?leden','leden','oudleden');
			$form['status'] = (isset($_POST['status']) and in_array($_POST['status'],$zoek_in_type)) ? $_POST['status'] : '';

			# kolom waarop gesorteerd wordt
			$kolommen = array('uid','voornaam','achternaam','email','adres','telefoon','mobiel');
			$form['sort'] = (isset($_POST['sort']) and in_array($_POST['sort'],$kolommen)) ? $_POST['sort'] : 'achternaam';
			
			# kolommen die afgebeeld kunnen worden
			$kolommen = array('uid','nickname','moot','email','adres','telefoon','mobiel','icq','msn','skype');
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

			# en zoeken dan maar...
			$midden->setResult($lid->zoekLeden($form['wat'], $form['waar'], $form['moot'], $form['sort'], $form['status']));
		}
	} else {
		# geen rechten
		require_once('class.includer.php');
		$midden = new Includer('', 'geentoegang.html');
	}	

	### Kolommen vullen ###
	require_once('class.column.php');
	$col0 = new Column(COLUMN_MENU);
	$col0->addObject($homemenuhok);
	$col0->addObject($infomenuhok);
	if ($lid->isLoggedIn()) $col0->addObject($ledenmenuhok);
	$col0->addObject($loginhok);
	$col0->addObject($datum);

	$col1 = new Column(COLUMN_MIDDENRECHTS);
	$col1->addObject($midden);

	# Pagina maken met deze twee kolommen
	require_once('class.page.php');
	$page = new Page();
	$page->addColumn($col0);
	$page->addColumn($col1);
	$page->addTitel('ledenlijst');
	$page->view();
	
}

?>
