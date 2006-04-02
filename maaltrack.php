#!/usr/bin/php5
##!/usr/bin/php5 -c /etc/php5/vhosts/csrdelft/php.ini
<?php

# CLI Maaltijdbeheer C.S.R. Delft
# (c) 2006 PubCie C.S.R. Delft 
# 20-01-2006 Hans van Kranenburg

main();
exit;

function main() {
	session_id('maaltrack-cli');

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

	# login-systeem
	require_once('class.lid.php');
	require_once('class.mysql.php');
	require_once('class.maaltrack.php');
	require_once('class.maaltijd.php');

	$db = new MySQL();
	$lid = new Lid($db);
	$maaltrack = new MaalTrack($lid, $db);

	$error = '';
	print ("CLI Maaltijdbeheer C.S.R. Delft\nTyp ? voor help\n\n");

	# Lees telkens een regel en probeer er achter te komen wat er wordt bedoeld.
	while (!feof(STDIN) and print ("maaltrack> ") and (($input = trim(fgets(STDIN))) != "!")) {
		# niets
		if ($input == "") continue;
		# een maaltijdcommando
		$matches= array();
		# toevoegen van een testmaaltijd
		# tijd is 18:00, sluit om 15:00, tp is x999
		# bijv:
		#  maaltrack> test mon 1 week
		#  maaltrack> test thu 3 weeks
		# http://www.gnu.org/software/tar/manual/html_chapter/tar_7.html#SEC115
		if (preg_match("/^add\s(.*)$/", $input, $matches)) {
			$datum = strtotime("{$matches[1]} 18:00");
			$sluit = strtotime("{$matches[1]} 15:00");
			if (date('w',$datum) == 1) $abosoort = 'A_MAANDAG';
			elseif (date('w',$datum) == 4) $abosoort = 'A_DONDERDAG';
			else $abosoort = '';
			$maaltrack->addMaaltijd($datum, $sluit, 'Test', $abosoort, 'x999') or print($maaltrack->getError()."\n");
		}
		elseif (preg_match("/^strtotime\s+(.+)$/", $input, $matches)) {
			echo date('r',strtotime($matches[1])) . "\n";
		}
		elseif (preg_match("/^adx\s+([^|]+)\|([^|]+)\|([^|]+)\|([^|]+)$/", $input, $matches)) {
			$datum = strtotime("{$matches[1]} 18:00");
			$maaltrack->addMaaltijd($datum, $matches[2], $matches[3], $matches[4]) or print($maaltrack->getError()."\n");
		}
		# laat maaltijden zien vanaf nu
		elseif ($input == 'view') {
			$maaltijden = $maaltrack->getMaaltijden();
			foreach ($maaltijden as $maaltijd) {
				$maaltijd['datum'] = date('r', $maaltijd['datum']);
				echo implode('; ',$maaltijd)."\n";
			}
		}
		elseif (preg_match("/^lijst\s+(\d+)$/", $input, $matches)) {
			$m = new Maaltijd($matches[1],$lid,$db);
			$aan = $m->getAanmeldingenLid();
			foreach ($aan as $aanmelding) print($aanmelding['naam']."\n");
			$af = $m->getAfTijdelijk();
			foreach ($af as $afmelding) print("AF: ".$afmelding['naam']."\n");
			unset($m);
		}
		elseif (preg_match("/^del\s(\d+)$/", $input, $matches)) {
			$maaltrack->removeMaaltijd($matches[1]) or print($maaltrack->getError()."\n");
		}
		elseif (preg_match("/^sluit\s(\d+)$/", $input, $matches)) {
			if (!$maaltrack->isMaaltijd($matches[1])) {
				print ($maaltrack->getError()."\n");
			} else {
				$m = new Maaltijd($matches[1],$lid,$db);
				$m->sluit() and print("Maaltijd {$matches[1]} gesloten \n") or print("Maaltijd {$matches[1]} is al gesloten.\n");
				unset($m);
			}
		}
		elseif (preg_match("/^recount\s(\d+)$/", $input, $matches)) {
			$m = new Maaltijd($matches[1],$lid,$db);
			$m->recount();
			unset($m);
		}
		elseif (preg_match("/^addabo\s(\w+)$/", $input, $matches)) {
			$maaltrack->addAbo($matches[1]) or print($maaltrack->getError()."\n");
		}
		elseif (preg_match("/^viewabo$/", $input, $matches)) {
			$abos = $maaltrack->getAbo();
			foreach ($abos as $abo) {
				print(implode('; ',$abo)."\n");
				print_r($abo);
			}
		}
		elseif (preg_match("/^view!abo$/", $input, $matches)) {
			$geenabo = $maaltrack->getNotAboSoort();
			foreach ($geenabo as $abo) {
				print(implode('; ',$abo)."\n");
				print_r($abo);
			}
		}
		elseif (preg_match("/^viewabosoort$/", $input, $matches)) {
			$abos = $maaltrack->getAboSoort();
			foreach ($abos as $abo) {
				print(implode('; ',$abo)."\n");
				print_r($abo);
			}
		}
		elseif (preg_match("/^delabo\s(\w+)$/", $input, $matches)) {
			$maaltrack->delAbo($matches[1]) or print($maaltrack->getError()."\n");
		}
		elseif (preg_match("/^aan\s(\d+)$/", $input, $matches)) {
			if (!$maaltrack->isMaaltijd($matches[1])) print("Deze maaltijd bestaat niet.\n");
			$maaltijd = new Maaltijd($matches[1], $lid, $db);
			if ($maaltijd->aanmelden())
				print("Aangemeld voor maaltijd {$matches[1]}.\n");
			else print($maaltijd->getError()."\n");
			unset($maaltijd);
		}
		elseif (preg_match("/^af\s(\d+)$/", $input, $matches)) {
			if (!$maaltrack->isMaaltijd($matches[1])) print("Deze maaltijd bestaat niet.\n");
			$maaltijd = new Maaltijd($matches[1], $lid, $db);
			if ($maaltijd->afmelden())
				print("Afgemeld voor maaltijd {$matches[1]}.\n");
			else print($maaltijd->getError()."\n");
			unset($maaltijd);
		}
		elseif (preg_match("/^auto\s(\d+)$/", $input, $matches)) {
			if (!$maaltrack->isMaaltijd($matches[1])) print("Deze maaltijd bestaat niet.\n");
			$maaltijd = new Maaltijd($matches[1], $lid, $db);
			if ($maaltijd->automatisch())
				print("Aan/Afmelding voor maaltijd {$matches[1]} gewist.\n");
			else print($maaltijd->getError()."\n");
			unset($maaltijd);
		}
		elseif (preg_match("/^login\s(\w+)$/", $input, $matches)) {
			$lid->login($matches[1], '{SSHA}quTGihd8M7nUlEzsLaBGmlKVXJBn/KPw') or print("Wisselen van gebruiker mislukt.\n");
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
	
 maaltrack> add ddd n week(s)    # voeg testmaaltijd toe (zie strtotime())
 maaltrack> adx ddd n week(s)|Omschrijving|Abosoort|TP-uid 
 maaltrack> strtotime blah       # toon uitvoer van date('r',strtotime(blah));
 maaltrack> view                 # laat maaltijden zien vanaf nu
 maaltrack> sluit maalid         # sluit inschrijving
 maaltrack> del maalid           # gooi maaltijd weg
 maaltrack> recount maalid       # hertel maaltijd
 maaltrack> addabo abosoort      # zet abo aan voor uid
 maaltrack> viewabo              # laat mijn abos zien
 maaltrack> delabo abosoort      # zet abo uit voor uid
 maaltrack> aan maalid [uid]     # aanmelden uid voor maaltijd
 maaltrack> af maalid            # afmelden uid voor maaltijd
 maaltrack> auto maalid          # aan/afmelding wissen
 maaltrack> login uid            # wisselen naar andere gebruiker

EOT;
}

?>
