<?php
require_once 'configuratie.include.php';

if(!$loginlid->hasPermission('P_LEDEN_MOD,groep:SocCie')){
	header('location: '.CSR_ROOT);
	exit;
}

//verzamel bestaande gegevens
$sLedenQuery="
	SELECT
		voornaam, achternaam, tussenvoegsel, uid, soccieID, createTerm, socciesaldo
	FROM
		lid";
$rLeden=$db->query($sLedenQuery);
while($aData=$db->next($rLeden)){
	$aLeden[(int)$aData['soccieID'].$aData['createTerm']]=$aData;
}

//lees dump van de laatste import in
$soccieinput=simplexml_load_file ('../../data/soccie.xml');

//bepaal sortering
$sorteeropties=array('voornaam', 'achternaam', 'id', 'saldo', 'createTerm');
if(isset($_GET['sorteer']) AND in_array($_GET['sorteer'], $sorteeropties)){
	$sorteerkey=$_GET['sorteer'];
}else{
	$sorteerkey=false;
}

//splitst bestaande en niet-bestaande accounts
$accounts=array();
$teller['totaal']=0;
$teller['inDb']=0;
$teller['onbekend']=0;
foreach($soccieinput as $soccielid){
	//soccieID i.c.m. createTerm is uniek.
	$key = (int)$soccielid->id . $soccielid->createTerm;

	$account=$soccielid;
	if(array_key_exists($key, $aLeden) ){
		$filter='inDb';
		$account->addChild('uid', $aLeden[$key]['uid']);
		$account->addChild('naam', $aLeden[$key]['voornaam'].' '.$aLeden[$key]['tussenvoegsel'].($aLeden[$key]['tussenvoegsel'] ? ' ':'') .$aLeden[$key]['achternaam']);
	}else{
		$filter='onbekend';
	}

	if($sorteerkey){
		$accounts[$filter][(string)$account->$sorteerkey . $key]=$account;
	}else{
		$accounts[$filter][]=$account;
	}
	$teller[$filter]++;
	$teller['totaal']++;
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
echo '</ul></p>';
echo '<p>Onderstaande gegevens zijn van de laatste import uit de socciepc.</p>';
echo 'Totaal aantal accounts: '.$teller['totaal'].'<br/>';
echo 'Onbekende accounts: '.$teller['onbekend'].' (weergegeven: '.count($accounts['onbekend']).')<br/>';
echo 'Bekende accounts: '.$teller['inDb'].' (weergegeven: '.count($accounts['inDb']).')<br/>';


echo 'Sorteer tabellen op: ';
foreach($sorteeropties as $optie){
	echo '<a href="/tools/soccieimportweergeven.php?sorteer='.$optie.'">'.$optie.'</a> ';
}

echo '<h3>SocCieaccounts die niet gekoppeld zijn</h3>';
echo '<p>Mogelijke oorzaken:<ul>
	<li>Geen id in database</li> 
	<li>De combinatie van soccieID en createTerm klopt niet. </li>
</ul>';
viewTable($accounts['onbekend']);

echo '<h3>SocCieaccounts die gekoppeld zijn aan profielen op de webstek</h3>';
viewTable($accounts['inDb']);


function viewTable($aAccounts){
	echo '<table>';
	echo '<tr><th>Naam</th><th>ID</th><th>Saldo</th><th>Account gemaakt bij</th><th>naam op webstek</th><th>uid</th></tr>';
	foreach($aAccounts as $account){
		echo '<tr>';
		echo 	'<td>'.$account->voornaam.' '.$account->achternaam.'</td>';
		echo 	'<td>'.$account->id.'</td>';
		echo 	'<td>'.$account->saldo.'</td>';
		echo 	'<td style="border-right-color: black;">'.$account->createTerm.'</td>';
		echo 	'<td>'.$account->naam.'</td>';
		echo 	'<td><a href="'.CSR_ROOT.'communicatie/profiel/'.$account->uid.'">'.$account->uid.'</a></td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '<br /><br />';
}

