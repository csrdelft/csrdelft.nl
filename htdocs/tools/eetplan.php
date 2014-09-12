<?php
$hist_a = 0;

# koppel de sjaarsnummers aan de sjaars
#
# 3 oud uid (uit oude lichtingen): 0946, 1045, 1053
/* $ks['0946']='0946';
  $ks['1045']='1045';
  $ks['1053']='1053'; */

# nummers die missen in 13:
# eerste semester: 14,42,56,57,58,62,63,68
for ($es = 1; $es <= 67; $es++) {
	if (!in_array($es, array(14, 42, 56, 57, 58, 62, 63, 68))) {
		$uid = str_pad($es + 1300, 4, "0", STR_PAD_LEFT);
		$ks[$uid] = $uid;
	}
}


# koppel de huizennummers aan huizen
//$kh = array(0,3,6,7,10,11,13,1,15,16,17,19,21,2,22,8,18,5);0,3,5,6,19,20
$kh = array(0, 1, 2, 4, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31); // 3,5,6,19 missen
$khd = array(
	0 => array('', '', ''),
	1 => array('Huize Ihnshthabhielh', 1438, ''),
	2 => array("Sonnenvanck", 36, ''),
	3 => array("OD11", 14, ""),
	4 => array("De Koornmarkt", 33, ""),
	5 => array("Lachai-Roi", 57, ""),
	6 => array("De Ambassade", 6, ""),
	7 => array('De Zilveren Hinde', 1017, ''),
	8 => array('Oranje Boven', 1016, ''),
	9 => array('Huize * Asterix', 46, ''),
	10 => array('t Internaat', 9, ""),
	11 => array("De Koornmarkt", 33, ""),
	12 => array("Lachai-Roi", 57, ""),
	13 => array("OD11", 14, ""),
	14 => array("De Preekstoel", 62, ""),
	15 => array("Sonnenvanck", 36, ''),
	16 => array("Huize Van Speijk", 39, ""),
	17 => array("De Tolhuis-Alliantie", 10, ''),
	18 => array("Hotel Vlaams Gaius", 32, ''),
	19 => array("Residentes in Vita Detissima", 494, ''),
	20 => array("Huize A.D.A.M.", 554, ''),
	21 => array("C.C.V.", 1303, ''),
	22 => array("'t Ailand", 5, ''),
	23 => array("Ihnshthabhielh", 1438, ''),
	24 => array("Verdiplein 2012-ers", 1683, ''),
	25 => array("Huize Op Stand", 512, ''),
	26 => array("De Nachtwacht", 1178, ''),
	27 => array("Theloneum", 1441, ''),
	28 => array("Huize Den Hertog", 52, ''),
	29 => array("De Paplepel", 1338, ''),
	30 => array("De Zevende Hemel", 1048, ''),
	31 => array("Hendrik Tollensstraat 386", 1798, '')
);

# namen opzoeken in de database
require_once 'configuratie.include.php';

#if (isset($_GET['bron'])) {
#	show_source($_SERVER['SCRIPT_FILENAME']);
#	exit;
#}
echo '<pre>';

$s = 60; //(int)$_GET['s']; # aantal sjaars
$h = 27; // $h = (int)$_GET['h']; # aantal huizen
$a = 4;  // $a = (int)$_GET['a']; # aantal avonden
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
$seen[1208][1203] = true;
$seen[1203][1208] = true;
$seen[1210][1205] = true;
$seen[1210][1238] = true;
$seen[1205][1210] = true;
$seen[1205][1238] = true;
$seen[1238][1210] = true;
$seen[1238][1205] = true;
$seen[1211][1235] = true;
$seen[1235][1211] = true;
$seen[1222][1239] = true;
$seen[1222][1243] = true;
$seen[1239][1222] = true;
$seen[1239][1243] = true;
$seen[1243][1222] = true;
$seen[1243][1239] = true;
$seen[1204][1231] = true;
$seen[1231][1204] = true;
$seen[1213][1229] = true;
$seen[1229][1213] = true;
$seen[1240][1221] = true;
$seen[1240][1219] = true;
$seen[1221][1240] = true;
$seen[1221][1219] = true;
$seen[1219][1240] = true;
$seen[1219][1221] = true;
$seen[1209][1228] = true;
$seen[1209][1234] = true;
$seen[1228][1209] = true;
$seen[1228][1234] = true;
$seen[1234][1209] = true;
$seen[1234][1228] = true;
$seen[1215][1226] = true;
$seen[1215][1212] = true;
$seen[1226][1215] = true;
$seen[1226][1212] = true;
$seen[1212][1215] = true;
$seen[1212][1226] = true;
$seen[1206][1227] = true;
$seen[1206][1201] = true;
$seen[1227][1206] = true;
$seen[1227][1201] = true;
$seen[1201][1206] = true;
$seen[1201][1227] = true;
$seen[1237][1223] = true;
$seen[1237][1216] = true;
$seen[1223][1237] = true;
$seen[1223][1216] = true;
$seen[1216][1237] = true;
$seen[1216][1223] = true;
$seen[1236][1220] = true;
$seen[1220][1236] = true;
$seen[1207][1232] = true;
$seen[1207][1233] = true;
$seen[1232][1207] = true;
$seen[1232][1233] = true;
$seen[1233][1207] = true;
$seen[1233][1232] = true;
$seen[1202][1230] = true;
$seen[1202][1214] = true;
$seen[1230][1202] = true;
$seen[1230][1214] = true;
$seen[1214][1202] = true;
$seen[1214][1230] = true;
$seen[1217][1218] = true;
$seen[1217][1225] = true;
$seen[1218][1217] = true;
$seen[1218][1225] = true;
$seen[1225][1217] = true;
$seen[1225][1218] = true;
$seen[1211][1225] = true;
$seen[1211][1220] = true;
$seen[1225][1211] = true;
$seen[1225][1220] = true;
$seen[1220][1211] = true;
$seen[1220][1225] = true;
$seen[1219][1244] = true;
$seen[1244][1219] = true;
$seen[1208][1209] = true;
$seen[1209][1208] = true;
$seen[1234][1235] = true;
$seen[1235][1234] = true;
$seen[1246][1247] = true;
$seen[1247][1246] = true;
$seen[1246][1247] = true;
$seen[1246][1224] = true;
$seen[1246][1242] = true;
$seen[1246][1244] = true;
$seen[1246][1241] = true;
$seen[1247][1246] = true;
$seen[1247][1224] = true;
$seen[1247][1242] = true;
$seen[1247][1244] = true;
$seen[1247][1241] = true;
$seen[1224][1246] = true;
$seen[1224][1247] = true;
$seen[1224][1242] = true;
$seen[1224][1244] = true;
$seen[1224][1241] = true;
$seen[1242][1246] = true;
$seen[1242][1247] = true;
$seen[1242][1224] = true;
$seen[1242][1244] = true;
$seen[1242][1241] = true;
$seen[1244][1246] = true;
$seen[1244][1247] = true;
$seen[1244][1224] = true;
$seen[1244][1242] = true;
$seen[1244][1241] = true;
$seen[1241][1246] = true;
$seen[1241][1247] = true;
$seen[1241][1224] = true;
$seen[1241][1242] = true;
$seen[1241][1244] = true;


# sjaars die al in huizen wonen alvast rekening mee houden
# voorbeeld: $visited_sh[$sjaarsuid][$huisuid]=true;
/* $visited_sh[1138][5]=true;//Martijn van den Berg (De Lindenburgh)
  $visited_sh[0946][11]=true;//Niels Brandhorst (OD11)
  $visited_sh[1109][11]=true;//Melanie Schippers (OD11)
  $visited_sh[1113][13]=true;//Job van Stiphout (SSS)
  $visited_sh[1112][16]=true;//Matthias Floor (internaat)
  $visited_sh[1045][16]=true;//Vlot sr. (internaat)
  $visited_sh[1151][9]=true;//Kirsten Neels (KMT)
  $visited_sh[1139][1]=true;//Roos van Riggelen (Oranje Boven)
  $visited_sh[1144][10]=true;//Margriet Vlot (Lacha-Roi) */
$visited_sh[1329][1] = true;
$visited_sh[1346][17] = true;
$visited_sh[1320][8] = true;
$visited_sh[1311][8] = true;
$visited_sh[1317][11] = true;
$visited_sh[1304][19] = true;
$visited_sh[1311][13] = true;
$visited_sh[1350][13] = true;
$visited_sh[1307][16] = true;
$visited_sh[1310][16] = true;
$visited_sh[1367][16] = true;
$visited_sh[1344][2] = true;
$visited_sh[1365][3] = true;


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
