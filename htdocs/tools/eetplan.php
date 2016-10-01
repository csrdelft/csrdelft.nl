<?php
$hist_a = 0;

$lichting = 1500;

# koppel de sjaarsnummers aan de sjaars
#
# 3 oud uid (uit oude lichtingen): 0946, 1045, 1053
/* $ks['0946']='0946';
  $ks['1045']='1045';
  $ks['1053']='1053'; */

# nummers die missen in 13:
# eerste semester: 14,42,56,57,58,62,63,68
for ($es = 1; $es <= 60; $es++) {
	if (!in_array($es, array(39,59))) {
		$uid = str_pad($es + $lichting, 4, "0", STR_PAD_LEFT);
		$ks[$uid] = $uid;
	}
}

# datums staan in /lib/model/EetplanModel.class.php
# koppel de huizennummers aan huizen
$kh = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26);
$khd = array(
	0	 => array("NULL",null),
	1	 => array("Huize Den Hertog", 52),
	2	 => array("Paplepel", 2152),
	3	 => array("OD11", 14),
	4	 => array('Huize Ihnshthabhielh', 1438),
	5	 => array("Huize Van Speijk", 39),
	6	 => array("De Gouden Leeuw", 8),
	7	 => array("De Molshoop", 34),
	8	 => array('Huize * Asterix', 46),
	9	 => array("H.U.P.", 2116),
	10	 => array("Verdieping 1", 1683),
	11	 => array("Villa Delphia", 37),
	12	 => array("Boomhut", 2390),
	13	 => array("De Koornmarkt", 33),
	14	 => array("Huize A.D.A.M.", 554),
	15	 => array("Hotel Vlaams Gaius", 32),
	16	 => array('t Internaat', 9),
	17	 => array("De Balpolgroep", 48),
	18	 => array("De Zuidpool", 2120),
	19	 => array("C.C.V.", 1303),
	20	 => array("GGZ", 2323),
	21	 => array("Theloneum", 1441),
	22	 => array("VVV", 954),
	23	 => array("Lachai-Roi", 57),
	24	 => array("De Nachtwacht", 1178),
	25	 => array("Sonnenvanck", 36),
	26	 => array("De Preekstoel", 62)
);

# namen opzoeken in de database
require_once 'configuratie.include.php';

#if (isset($_GET['bron'])) {
#	show_source($_SERVER['SCRIPT_FILENAME']);
#	exit;
#}
echo '<pre>';

$s = 58; //(int)$_GET['s']; # aantal sjaars
$h = 26; // $h = (int)$_GET['h']; # aantal huizen
$a = 3;  // $a = (int)$_GET['a']; # aantal avonden
//$m = (int)floor($s/$h);
//$m = (int)$_GET['m']; # max aantal sjaars per huis per avond
$r = 1;  // $r = (int)$_GET['r']; # wel of niet random

echo "<b>Parameterisatie:</b>\nAantal Sjaars: $s\nAantal Huizen: $h\nAantal Avonden: $a\n\n";

# we moeten onthouden...
# ... in welke huizen een sjaars is geweest ...
$visited = array(); # $visited[huis][] = sjaars
$visited_ah = array(); # $visited_ah[avond][huis][] = sjaars
$visited_sh = array(); # $visited_sh[sjaars][huis] = true
# ... en welke sjaars welke andere al heeft ontmoet
$seen = array(); # $seen[sjaars][] = sjaars
# sjaars die elkaar gezien hebben
$seen[1546][1547] = true; // 3 vriendjes
$seen[1547][1548] = true;
$seen[1546][1548] = true;

$seen[1524][1525] = true; // Sjaarshuis A (3 sjaars)
$seen[1525][1544] = true;
$seen[1524][1544] = true;

$seen[1531][1532] = true; // Sjaarshuis B (5 sjaars)
$seen[1531][1533] = true;
$seen[1531][1534] = true;
$seen[1531][1537] = true;
$seen[1532][1533] = true;
$seen[1532][1534] = true;
$seen[1532][1537] = true;
$seen[1533][1534] = true;
$seen[1533][1537] = true;
$seen[1534][1537] = true;

$seen[1529][1542] = true; // Kaartenhuis huisgenoten

$seen[1501][1502] = true; // GGZ sjaars
$seen[1501][1504] = true;
$seen[1501][1510] = true;
$seen[1501][1530] = true;
$seen[1502][1504] = true;
$seen[1502][1510] = true;
$seen[1502][1530] = true;
$seen[1504][1510] = true;
$seen[1504][1530] = true;
$seen[1510][1530] = true;

# sjaars die al in huizen wonen alvast rekening mee houden
# voorbeeld: $visited_sh[$sjaarsuid][$huisuid]=true;
$visited_sh[1526][4] = true; // Marco, Instabiel
$visited_sh[1503][9] = true; // Anne, HUP
$visited_sh[1506][13] = true; // Pip, KMT
$visited_sh[1516][15] = true; // Louis, HVG
$visited_sh[1504][20] = true; // Christine, GGZ
$visited_sh[1502][20] = true; // Jette, GGZ
$visited_sh[1501][20] = true; // Chiel, GGZ
$visited_sh[1530][20] = true; // Reon, GGZ
$visited_sh[1510][20] = true; // Desta, GGZ

# het uiteindelijke rooster
# $sah[sjaars][avond] = huis.. etc...
$sah = array();
$ahs = array();

# !!!!! LET OP: data voor 2e halfjaar verwijderen uit database
//$sql='DELETE FROM `eetplan` WHERE `avond` > 4';
//$result=$db->query($sql);
# data die al in de tabel zit om later feuten toe te kunnen voegen
/* $sql = 'SELECT avond, huis, GROUP_CONCAT(uid) AS uids FROM `eetplan` GROUP BY avond, huis';
  $db = MijnSqli::instance();
  $result = $db->query($sql);
  while ($rij = $db->next($result)) {
  $sjaarsen = explode(',', $rij['uids']);

  foreach ($sjaarsen as $foo => $sjaars) {
  $visited[$rij['huis']][] = $sjaars;
  $visited_sh[$sjaars][$rij['huis']] = true;
  $visited_ah[$rij['avond']][$rij['huis']][] = $sjaars;
  $sah[$sjaars][$rij['avond']] = $rij['huis'];
  $sah[$rij['avond']][$rij['huis']] = $sjaars;

  foreach ($sjaarsen as $subsjaars) {
  $seen[$sjaars][$subsjaars] = true;
  }
  }
  } */

# huizen laten rondtellen
if ($r == 0)
	$ih = 1;
else
	$ih = rand(1, $h);

# de avonden langslopen
for ($ia = 1 + $hist_a; $ia <= $a + $hist_a; $ia++) {
	# de sjaars langslopen
	#for ($is = 1; $is <= $s; $is++) {
	foreach ($ks as $is => $foo) {
		# wat foutmeldingen voorkomen
		if (!isset($ahs[$ia][$ih]))
			$ahs[$ia][$ih] = array();
		if (!isset($seen[$is]))
			$seen[$is] = array();
		# we hebben nu een avond en een sjaars, nu nog een huis voor m vinden...
		# zolang
		# - deze sjaars dit huis al bezocht heeft, of
		# - in het huidige huis (ih) een sjaars zit die deze sjaars (is) al ontmoet heeft
		# - het huis nog niet aan zn max sjaars is voor deze avond
		# nemen we het volgende huis
		$startih = $ih;
		# nieuw: begin met het max aantal sjaars per huis net iets te laag in te stellen, zodat
		# de huizen eerst goed vol komen, en daarna pas extra sjaars bij huizen
		$m = (int) floor($s / $h);
		$nofm = 0; # aantal huizen dat aan de max zit.
		while (isset($visited_sh[$is][$ih])
			or count(array_intersect($ahs[$ia][$ih], $seen[$is])) > 0
			or count($visited_ah[$ia][$ih]) >= $m) {
			$ih = $ih % $h + 1;
			if ($ih == $startih) {
				$m++; #die ('whraagh!!!');
			}
			if (!isset($ahs[$ia][$ih])) {
				$ahs[$ia][$ih] = array();
			}

			# nieuw: als alle huizen zijn langsgelopen en ze allemaal max sjaars hebben
			# dan de max ophogen
			if (count($visited_ah[$ia][$ih]) == $m)
				$nofm++;
			if ($nofm == $h)
				$m++;
		}

		# deze sjaars heeft op deze avond een huis gevonden
		$sah[$is][$ia] = $ih;
		# en gaat alle sjaars die op deze avond in dit huis zitten dat melden
		foreach ($ahs[$ia][$ih] as $sjaars) {
			$seen[$is][] = $sjaars; # alle sjaars in mijn seen
			$seen[$sjaars][] = $is; # ik in alle sjaars' seen
		}
		$ahs[$ia][$ih][] = $is;
		# de sjaars heeft het huis bezocht
		$visited[$ih][] = $is;
		$visited_sh[$is][$ih] = true;
		$visited_ah[$ia][$ih][] = $is;

		# huis ophogen
		if ($r == 0)
			$ih = $ih % $h + 1;
		else
			$ih = rand(1, $h);
	}
}

echo "<b>Eetplanrooster:</b>\n\n";

echo "        ";
for ($ia = 1 + $hist_a; $ia <= $a + $hist_a; $ia++)
	echo str_pad('[' . $ia . ']', 10);
echo "\n";

#for ($is = 1; $is <= $s; $is++) {
foreach ($ks as $is => $foo) {
	echo str_pad($ks[$is], 8);
	# nu alle avonden
	for ($ia = 1 + $hist_a; $ia <= $a + $hist_a; $ia++) {
		echo str_pad($sah[$is][$ia], 10);
	}
	echo "\n";
}

echo "\n<b>De Woonoorden</b>\n\n";
for ($ih = 0; $ih <= $h; $ih++) {
	echo str_pad($ih, 10);
	echo str_pad($khd[$kh[$ih]][0], 28);
	echo str_pad($khd[$kh[$ih]][1], 40);
	echo "\n";
}

echo "\n<b>Sjaars die elkaar zien:</b>\n\n";
#for ($is = 1; $is <= $s; $is++) {
foreach ($ks as $is => $foo) {
	echo str_pad('S-' . $ks[$is] . ': ', 10);
	sort($seen[$is]);
	foreach ($seen[$is] as $sjaars) {
		echo str_pad($ks[$sjaars], 10);
	}
	echo "\n";
}

echo "\n<b>Welke sjaars komen op de huizen:</b>\n\n";
for ($ih = 1; $ih <= $h; $ih++) {
	echo str_pad('H-' . $ih, 10);
	if (!isset($visited[$ih]))
		$visited[$ih] = array();
	sort($visited[$ih]);
	foreach ($visited[$ih] as $sjaars) {
		echo str_pad($ks[$sjaars], 10);
	}
	echo "\n";
}

echo "\n<b>Op welke avond in welk huis hoeveel?:</b>\n\n";
for ($ih = 1; $ih <= $h; $ih++) {
	echo str_pad('H-' . $ih, 10);
	for ($ia = 1 + $hist_a; $ia <= $a + $hist_a; $ia++) {
		if (!isset($visited_ah[$ia][$ih]))
			$visited_ah[$ia][$ih] = array();
		echo str_pad(count($visited_ah[$ia][$ih]), 10);
	}
	echo "\n";
}

echo "<b># Eetplanrooster SQL:</b>\n\n";
#for ($is = 1; $is <= $s; $is++) {
foreach ($ks as $is) {
	for ($ia = 1 + $hist_a; $ia <= $a + $hist_a; $ia++) {
		if (isset($ks[$is])) {
			echo "INSERT INTO `eetplan` (`avond`,`uid`,`huis`) VALUES ({$ia},'{$ks[$is]}',{$sah[$is][$ia]});";
			//$result=$db->query($sql);
		}
	}
}

echo "\n# <b>De Woonoorden</b>\n\n";
for ($ih = 1; $ih <= $h; $ih++) {
	echo "INSERT INTO `eetplanhuis` (`id`,`naam`, `groepid`) VALUES ({$ih},'{$khd[$kh[$ih]][0]}',{$khd[$kh[$ih]][1]});\n";
}
?>
</pre>
