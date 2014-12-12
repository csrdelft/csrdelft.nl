<?php

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_LEDEN_MOD,groep:SocCie,groep:NBG')) {
	redirect(CSR_ROOT);
}

//verzamel bestaande gegevens
$sLedenQuery = "
	SELECT
		voornaam, achternaam, tussenvoegsel, uid, soccieID, createTerm, socciesaldo
	FROM
		lid";
$db = MijnSqli::instance();
$rLeden = $db->query($sLedenQuery);
while ($aData = $db->next($rLeden)) {
	$aLeden[(int) $aData['soccieID'] . $aData['createTerm']] = $aData;
}

//lees dump van de laatste import in
$soccieinput = simplexml_load_file('../../data/soccie.xml');

//bepaal sortering
$sorteeropties = array('voornaam', 'achternaam', 'id', 'saldo', 'createTerm');
if (isset($_GET['sorteer']) AND in_array($_GET['sorteer'], $sorteeropties)) {
	$sorteerkey = $_GET['sorteer'];
} else {
	$sorteerkey = false;
}

//splitst bestaande en niet-bestaande accounts
$accounts = array();
$teller['totaal'] = 0;
$teller['inDb'] = 0;
$teller['onbekend'] = 0;

$zero = array('negatief' => 0.0, 'positief' => 0.0);
$saldo['bekend'] = $zero;
$saldo['onbekend'] = $zero;

foreach ($soccieinput as $soccielid) {
	//soccieID i.c.m. createTerm is uniek.
	$key = (int) $soccielid->id . $soccielid->createTerm;

	$account = $soccielid;

	//saldo sommeren
	$polariteit = ($account->saldo < 0 ? 'negatief' : 'positief');

	if (array_key_exists($key, $aLeden)) {
		$filter = 'inDb';

		$account->addChild('uid', $aLeden[$key]['uid']);
		$account->addChild('naam', $aLeden[$key]['voornaam'] . ' ' . $aLeden[$key]['tussenvoegsel'] . ($aLeden[$key]['tussenvoegsel'] ? ' ' : '') . $aLeden[$key]['achternaam']);
		$account->addChild('saldostek', $aLeden[$key]['socciesaldo']);

		//saldo sommmeren
		$saldo['bekend'][$polariteit]+=(float) $account->saldo;
	} else {
		$filter = 'onbekend';

		//saldo sommmeren
		$saldo['onbekend'][$polariteit]+=(float) $account->saldo;
	}

	if ($sorteerkey) {
		$prefix = "";
		if ($sorteerkey == 'id') {
			$prefix .= ($account->id < 10) ? "0" : "";
			$prefix .= ($account->id < 100) ? "0" : "";
		}
		$accounts[$filter][$prefix . $account->$sorteerkey . $key] = $account;
	} else {
		$accounts[$filter][] = $account;
	}
	$teller[$filter] ++;
	$teller['totaal'] ++;
}
//sortering
ksort($accounts['onbekend']);
ksort($accounts['inDb']);

//weergave
echo '<style type="text/css">
		table{	border-collapse: collapse; border: 1px solid black;}
		th{		border: 1px solid black;}
		td{		border: 1px solid #C0C0C0;}
		p{ 		width: 700px;}
	</style>';

echo '<h1>SocCiepc-import controle</h1>';
echo '<p>Controleer onderstaande lijstjes. De onbekende accounts worden 
niet op de webstek weergegeven. Om een onbekend account te koppelen moet 
het soccieID én createTerm (alleen de combinatie is uniek) in het profiel 
van het betreffende lid geplaatst worden. Neem contact op met de NBG om dit 
geautomatiseerd te laten doen.</p>';

echo '<p>Enkele andere controles:';
echo '<ul>';
echo '	<li>query #86: <a href="/tools/query.php?id=86">Novieten, (gast)leden zonder soccieID</a></li>';
echo '	<li>query #85: <a href="/tools/query.php?id=85">Alle oudleden/nobodies/etc die nog in socciepcimport staan</a></li>';
echo '	<li>query #80: <a href="/tools/query.php?id=80">Personen in db met saldo, die ontbreken in socciepcimport</a></li>';
echo '</ul>';
echo '<a href="/saldostatistiekensoccie">Overzichtpagina voor SocCie met opmerkelijke saldi en statistieken</a>...';
echo '</p>';

echo '<p>Onderstaande gegevens zijn van de laatste import uit de socciepc.</p>';
echo 'Totaal aantal accounts: ' . $teller['totaal'] . '<br/>';
echo 'Onbekende accounts: ' . $teller['onbekend'] . ' (weergegeven: ' . count($accounts['onbekend']) . ')<br/>';
echo 'Bekende accounts: ' . $teller['inDb'] . ' (weergegeven: ' . count($accounts['inDb']) . ')<br/>';
echo '<br/>';

$totaalneg = $saldo['bekend']['negatief'] + $saldo['onbekend']['negatief'];
$totaalpos = $saldo['bekend']['positief'] + $saldo['onbekend']['positief'];
echo 'Totaal saldo: € ' . ($totaalpos + $totaalneg) . ' (= +' . $totaalpos . '  ' . $totaalneg . ')<br/>';
echo 'Saldo van bekende accounts: € ' . ($saldo['bekend']['positief'] + $saldo['bekend']['negatief']) . ' (= +' . $saldo['bekend']['positief'] . '  ' . $saldo['bekend']['negatief'] . ')<br/>';
echo 'Saldo van onbekende accounts: € ' . ($saldo['onbekend']['positief'] + $saldo['onbekend']['negatief']) . ' (= +' . $saldo['onbekend']['positief'] . '  ' . $saldo['onbekend']['negatief'] . ')<br/>';


echo '<h4>Mogelijke oorzaken niet-gekoppelde accounts</h4>';
echo '<p>';
echo '<ul>
		<li>Geen id in database. zie ook query #86</li> 
		<li>De combinatie van soccieID en createTerm klopt niet. </li>
	</ul>';
echo 'De PubCie kan individuele leden bijwerken, NBG zorgt voor uitgebreidere koppelacties.';
echo '<br /><br />';
if (LoginModel::mag('P_LEDEN_MOD,groep:NBG')) {
	echo '<div style="background: lightgrey;">';
	echo '<i>Privé opmerking voor PubCie/NBG</i>:<br />';
	echo 'Op de console kun je een koppelscript runnen die alle soccieIDs in database automatisch probeert te updaten.<br />';
	echo '<pre>
	cd lib/
	../bin/socciesaldi-koppelen.php
	</pre>';
	echo '<p>Dit script probeert voor elk account in de soccie-import een 
match te vinden met gastleden, novieten en h.t. leden. Als naam 86% matcht 
wordt socciePC-id in database geupdate.<br />Handmatige aanpassen kan in het 
profiel van een lid.</p>';
	echo '<p>De saldoimport van SocCie-PC naar webstek is elke dag om half 7. 
	De NBG heeft toegang tot de server Wesley op Confide en kan daar 
	handmatig een import doen.</p>';
	echo '</div>';
}
echo '</p>';

echo 'Sorteer tabellen op: ';
foreach ($sorteeropties as $optie) {
	echo '<a href="/tools/soccieimportweergeven.php?sorteer=' . $optie . '">' . $optie . '</a> ';
}

echo '<h3>SocCieaccounts die niet gekoppeld zijn</h3>';
viewTable($accounts['onbekend']);

echo '<h3>SocCieaccounts die gekoppeld zijn aan profielen op de webstek</h3>';
viewTable($accounts['inDb']);

function viewTable($aAccounts) {
	echo '<table>';
	echo '<tr><th>Naam</th><th>ID</th><th>Saldo</th><th>Account gemaakt bij</th><th>naam op webstek</th><th>uid</th><th>Saldo op webstek</th></tr>';
	foreach ($aAccounts as $account) {
		echo '<tr>';
		echo '<td>' . $account->voornaam . ' ' . $account->achternaam . '</td>';
		echo '<td>' . $account->id . '</td>';
		echo '<td>' . $account->saldo . '</td>';
		echo '<td style="border-right-color: black;">' . $account->createTerm . '</td>';
		echo '<td>' . $account->naam . '</td>';
		echo '<td><a href="' . CSR_ROOT . '/profiel/' . $account->uid . '">' . $account->uid . '</a></td>';
		echo '<td>' . $account->saldostek . '</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '<br /><br />';
}
