<?php
$hist_a = 0;

$lichting = 1400;

# koppel de sjaarsnummers aan de sjaars
#
# 3 oud uid (uit oude lichtingen): 0946, 1045, 1053
/* $ks['0946']='0946';
  $ks['1045']='1045';
  $ks['1053']='1053'; */

# nummers die missen in 13:
# eerste semester: 14,42,56,57,58,62,63,68
for ($es = 1; $es <= 47; $es++) {
	if (!in_array($es, array())) {
		$uid = str_pad($es + $lichting, 4, "0", STR_PAD_LEFT);
		$ks[$uid] = $uid;
	}
}


# koppel de huizennummers aan huizen
//$kh = array(0,3,6,7,10,11,13,1,15,16,17,19,21,2,22,8,18,5);0,3,5,6,19,20
$kh = array(0, 1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23); // 3,5,6,19 missen
$khd = array(
	0 => array('', '', ''),
	1 => array('Huize Ihnshthabhielh', 1438, ''),
	2 => array("Huize Den Hertog", 52, ''),
	3 => array("De Molshoop", 34, ''),
	4 => array("Sonnenvanck", 36, ''),
	5 => array("De Gouden Leeuw", 8, ''),
	6 => array("C.C.V.", 1303, ''),
	7 => array("H.U.P.", 2116, ''),
	8 => array("Theloneum", 1441, ''),
	9 => array("Huize A.D.A.M. & Villa E.V.A.", 554, ''),
	10 => array("De Preekstoel", 62, ""),
	11 => array("Lachai-Roi", 57, ""),
	12 => array("De Koornmarkt", 33, ""),
	13 => array('t Internaat', 9, ""),
	14 => array("Verdieping 1", 1683, ''),
	15 => array('De Zilveren Hinde', 1017, ''),
	16 => array('Oranje Boven', 1016, ''),
	17 => array("Huize Van Speijk", 39, ""),
	18 => array("Hotel Vlaams Gaius", 32, ''),
	19 => array("De Zuidpool", 2120, ''),
	20 => array("Villa Delphia", 37, ''),
	21 => array('Huize * Asterix', 46, ''),
	22 => array("OD11", 14, ""),
	23 => array("De Balpolgroep", 13)
);

# namen opzoeken in de database
require_once 'configuratie.include.php';

#if (isset($_GET['bron'])) {
#	show_source($_SERVER['SCRIPT_FILENAME']);
#	exit;
#}
echo '<pre>';

$s = 47; //(int)$_GET['s']; # aantal sjaars
$h = 23; // $h = (int)$_GET['h']; # aantal huizen
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
$seen[1411][1445] = true; // Annemijn, Henria (Huisgenoten)


# sjaars die al in huizen wonen alvast rekening mee houden
# voorbeeld: $visited_sh[$sjaarsuid][$huisuid]=true;
$visited_sh[1401][1] = true; // Rico, Instabiel
$visited_sh[1411][16] = true; // Annemijn, Oranje Boven
$visited_sh[1419][9] = true; // Gerianne, E.V.A.
$visited_sh[1415][2] = true; // Jacko, HdH
$visited_sh[1412][22] = true; // Mirjam, OD11
$visited_sh[1444][17] = true; // Daan, van Speijk
$visited_sh[1414][20] = true; // Robin, Villa Delphia
$visited_sh[1424][10] = true; // rupSTER, Preekstoel
$visited_sh[1416][21] = true; // Bert, Asterix
$visited_sh[1423][4] = true; // Bram, Sonnenvanck
$visited_sh[1417][12] = true; // Paul, Koornmarkt
$visited_sh[1441][11] = true; // Thirza, Lachai-Roi
$visited_sh[1427][5] = true; // Wessel, DGL
$visited_sh[1445][16] = true; // Henria, Oranje Boven

# het uiteindelijke rooster
# $sah[sjaars][avond] = huis.. etc...
$sah = array();
$ahs = array();

# !!!!! LET OP: data voor 2e halfjaar verwijderen uit database
//$sql='DELETE FROM `eetplan` WHERE `avond` > 4';
//$result=$db->query($sql);
# data die al in de tabel zit om later feuten toe te kunnen voegen
$sql = 'SELECT avond, huis, GROUP_CONCAT(uid) AS uids FROM `eetplan` GROUP BY avond, huis';
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
}

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
for ($ih = 1; $ih <= $h; $ih++) {
	echo str_pad($ih, 10);
	echo str_pad($khd[$kh[$ih]][0], 28);
	echo str_pad($khd[$kh[$ih]][1], 40);
	echo str_pad($khd[$kh[$ih]][2], 20);
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
	echo "INSERT INTO `eetplanhuis` (`id`,`naam`, groepid,`telefoon`) VALUES ({$ih},'{$khd[$kh[$ih]][0]}',{$khd[$kh[$ih]][1]}, '{$khd[$kh[$ih]][2]}');\n";
}
?>
</pre>
