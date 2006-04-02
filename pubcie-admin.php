#!/usr/bin/php
<?php

# CLI lidbeheer C.S.R. Delft
# (c) 2006 PubCie C.S.R. Delft 
# 20-01-2006 Hans van Kranenburg

main();
exit;
function sorteer_achternaam($a, $b){
	return strcmp($a['achternaam'], $b['achternaam']);
}

function main() {
	session_id('wachtwoord-cli');

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

	# login-systeem
	require_once('class.lid.php');
	require_once('class.mysql.php');
	require_once('class.lidbeheer.php');
	require_once('class.forum.php');

	$db = new MySQL();
	$lid = new Lid($db);
	$beheer = new LidBeheer($lid, $db);
	
	//standaard zoekgebieden
	$aZoekStatus=array('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL');
	$iWerkCie=0;
	
	$error = '';
	print ("CLI pubcie beheer tool C.S.R. Delft\nTyp ? voor help\n\n");

	# Lees telkens een regel en probeer er achter te komen wat er wordt bedoeld.
	while (!feof(STDIN) and print ("pubcie> ") and (($input = trim(fgets(STDIN))) != "!")) {
		# niets
		if ($input == "") continue;
		
		$matches= array();
/************************************************************************************************
*	Wachtwoord resetten.
************************************************************************************************/
		if (preg_match("/^reset (\d{4}|x\d{3})$/", $input, $matches)) {
			$uid=$matches[1];
			$beheer->resetWachtwoord($uid, $berichten);
			echo $berichten;
		}
/************************************************************************************************
*	Naar Commissies zoeken.
************************************************************************************************/
		elseif(preg_match("/^cie ([a-zA-Z]*)$/", $input, $matches) OR trim($input)=='cie'){
			if($input=='cie'){
				$zoekstring='';
			}else{
				$zoekstring=trim($matches[1]);
			}
			echo $beheer->zoekCie($zoekstring);
			
		}
/************************************************************************************************
*	Commissieleden tonen
************************************************************************************************/
		elseif(
			preg_match("/^cielid (.*)$/", $input, $matches) OR
			preg_match("/^cieleden (.*)$/", $input, $matches) OR
			preg_match("/^comissieleden (.*)$/", $input, $matches) OR
			$input=='cielid'	){
			if($input!='cielid'){
				echo $beheer->toonCieLeden($matches[1]);
			}else{
				if($iWerkCie==0){
					echo "Geen Werkcommissie gezet. \r\n";
				}else{
					echo $beheer->toonCieLeden($iWerkCie);
				}
			}
		}
/************************************************************************************************
*	iemand aan commissie toevoegen
************************************************************************************************/
		elseif(	preg_match("/^cie (\d{1,3}) addlid (\d{4})$/", $input, $matches) OR
			preg_match("/^cie (\d{1,2}) addlid (\d{4}) ([a-zA-Z]*)$/", $input, $matches)){
			if(count($matches)==4){
				$beheer->addToCie($matches[1], $matches[2], $matches[3]);
			}else{
				$beheer->addToCie($matches[1], $matches[2]);
			}
		}elseif(preg_match("/^cie addlid (\d{4})$/", $input, $matches) OR
			preg_match("/^cie addlid (\d{4}) ([a-zA-Z]*)$/", $input, $matches)){
			if($iWerkCie==0){
				echo "Geen werkCie gezet. Gebruik setwerkcie # om een werkCie te zetten\r\n";
			}else{
				if(count($matches)==3){
					$beheer->addToCie($iWerkCie, $matches[1], $matches[2]);
				}else{
					$beheer->addToCie($iWerkCie, $matches[1]);
				}
			}
		}
/************************************************************************************************
*	commissie leegmaken
************************************************************************************************/
		elseif(	preg_match("/^leegcie (\d{1,3})$/", $input, $matches)){
			if($beheer->leegCie($matches[1])){
				echo "Commissie heeft geen leden meer. \r\n";
			}else{
				echo "Commissie legen mislukt.\r\n";
			}
		}
/************************************************************************************************
*	werkcommissie zetten.
************************************************************************************************/
		elseif(	preg_match("/^setwerkcie (\d{1,3})$/", $input, $matches) OR $input=='setwerkcie'){
			if($input!='setwerkcie' ){
				$iWerkCie=$matches[1];
			}
			echo 'WerkCie is nu '.$iWerkCie."\r\n";
		}	
/************************************************************************************************
*	Zoeken in voornaam en achternaam
************************************************************************************************/
		elseif(preg_match("/^zoek (.*)$/", $input, $matches)){
			$dataVoornaam=$lid->zoekLeden($matches[1], 'voornaam', 'achternaam', $aZoekStatus);
			$dataAchternaam=$lid->zoekLeden($matches[1], 'achternaam', 'achternaam', $aZoekStatus);
			$dataUid=$lid->zoekLeden($matches[1], 'uid', 'achternaam', $aZoekStatus);
			$data=array_merge($dataAchternaam, $dataVoornaam, $dataUid);
			
			usort($data, 'sorteer_achternaam');
			if(count($data)!=0){
				foreach($data as $aLid){
					echo $aLid['uid']." - ".$aLid['voornaam']." ";
					if($aLid['tussenvoegsel']!=''){
						echo $aLid['tussenvoegsel']." ";
					}
					echo $aLid['achternaam']." \r\n";
				}
			}else{
				echo "Geen leden gevonden \r\n";
			}
		}
/************************************************************************************************
*	Zoekgebied instellen.
************************************************************************************************/
		elseif(
				preg_match("/^zoek in (.*)$/", $input, $matches) OR
				preg_match("/^zoekin (.*)$/", $input, $matches) OR trim($input)=='zoekin'){
			if($input=='zoekin'){
				echo "Er wordt momenteel gezocht in ".implode($aZoekStatus, ', ')."\r\n";
			}else{
				switch(trim($matches[1])){	
					case 'alles':
						$aZoekStatus=array('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL', 'S_OUDLID');
					break;
					case 'oudleden':
					case 'oud':
					case 'oudlid':
						$aZoekStatus=array('S_OUDLID');
					break;
					case 'gewone leden':
					case 'leden':
						$aZoekStatus=array('S_LID', 'S_GASTLID');
					break;
					case 'sjaars':
						$aZoekStatus=array( 'S_NOVIET');
					case 'geen lid meer':
					case 'S_NOBODY':
					case 'niemand':
					case 'nobody':
						$aZoekStatus=array('S_NOBODY');
					break;
					case '?':
					case 'help':
						echo "Stel met dit commando het zoekgebied in.\r\n Kies uit 'alles, oudleden, leden, sjaars, niemand'\r\n";
					case 'default':
					default:
						$aZoekStatus=array('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL');
					break;
				}//einde switch
				echo "Er wordt momenteel gezocht in ".implode($aZoekStatus, ', ')."\r\n";
			}
		}
/************************************************************************************************
*	Afsluiten.
************************************************************************************************/
		elseif($input=='exit' OR $input=='quit' OR $input=='afsluiten'){
			echo "Later de pater!\r\n";
			exit;
		}
		# helpfunctie
		elseif ($input == '?'){ help(); }
		else print("Hier begrijp ik geen snars van!\n");		
	}
	print ("\n");
}

# functie: help()
# print help op scherm

function help() {
	print <<<EOT

leden:
 pubcie> zoek %naam%    #laat leden zien met %naam% in hun naam
 pubcie> reset %lidno%  #nieuw wachtwoord voor lidnummer
commissies:
 pubcie> cie            #laat alle commissies zien
 pubcie> cie %naam%     #laat commissies zien met %naam in de naam

EOT;
}

?>