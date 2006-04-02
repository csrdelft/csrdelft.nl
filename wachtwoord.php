#!/usr/bin/php
<?php

# CLI Maaltijdbeheer C.S.R. Delft
# (c) 2006 PubCie C.S.R. Delft 
# 20-01-2006 Hans van Kranenburg

main();
exit;

function main() {
	session_id('wachtwoord-cli');

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

	# login-systeem
	require_once('class.lid.php');
	require_once('class.mysql.php');
	
	require_once('class.forum.php');

	$db = new MySQL();
	$lid = new Lid($db);
	$forum = new Forum($lid, $db);

	$error = '';
	print ("CLI wachtwoord reset tool C.S.R. Delft\nTyp ? voor help\n\n");

	# Lees telkens een regel en probeer er achter te komen wat er wordt bedoeld.
	while (!feof(STDIN) and print ("pubcie> ") and (($input = trim(fgets(STDIN))) != "!")) {
		# niets
		if ($input == "") continue;
		
		$matches= array();
		if (preg_match("/^reset (\d{4})$/", $input, $matches)) {
			$uid=$matches[1];
			echo '  --Ga wachtwoord resetten voor: '.$forum->getForumNaam($uid)."...\r\n";
			//ww maken.
			$password=substr(md5(time()), 0, 8);
			$passwordhash=$lid->_makepasswd($password);
			$sQuery="
				UPDATE
					lid
				SET
					password='".$passwordhash."'
				WHERE
					uid='".$uid."'
				LIMIT 1;";
			//wachtwoord hash opslaen
			if($db->query($sQuery)){
				//gelukt.
				echo "  --wachtwoord gereset. Nu nog een mail sturen... \r\n";
			//mail maken
			$mail="
Hallo ".$forum->getForumNaam($uid).",

Je hebt een nieuwe wachtwoord aangevraagd. Je kan nu inloggen met de volgende combinatie:

".$uid."
".$password."

Je kan je wachtwoord wijzigen in je profiel.

Met vriendelijke groet,
Jan Pieter Waagmeester. h.t. Praeses der Pubcie

P.S. Mocht je nog vragen hebben, dan kan je natuurlijk altijd e-posts sturen naar pubcie@csrdelft.nl";
		mail($uid.'@confide.csrdelft.nl, pubcie@csrdelft.nl', 'Nieuw wachtwoord voor de C.S.R.-stek', $mail);
		echo "\r\n--------------------------------------------\r\n".
				$mail.
				"\r\n--------------------------------------------\r\n";
			}else{
				echo mysql_error();
				echo "\r\n  --------------------------------------------\r\n";
			}
				
		}elseif (preg_match("/^zoek (.*)$/", $input, $matches)){
			//zoeken
			$data=$lid->zoekLeden($matches[1], 'voornaam', 'achternaam', array('S_LID', 'S_GASTLID', 'S_NOVIET'));
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
		
		# helpfunctie
		elseif ($input == '?') help();
		else print("Hier begrijp ik geen snars van!\n");		
	}
	print ("\n");
}

# functie: help()
# print help op scherm

function help() {
	print <<<EOT

Je kan met deze tool wachwoorden opnieuw zetten. Zoeken op voornaam is ook mogelijk.	
 	pubcie> reset lidno
	pubcie> zoek voornaam

EOT;
}

?>
