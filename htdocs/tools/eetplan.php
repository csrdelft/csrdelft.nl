<pre>
<?php

# koppel de sjaarsnummers aan de sjaars
#
# 0601-0623
for ($es=1;$es<=23;$es++) {
	$ks[$es] = str_pad($es+600, 4, "0", STR_PAD_LEFT);
}
for ($es=25;$es<=46;$es++) {
	$ks[$es] = str_pad($es+600, 4, "0", STR_PAD_LEFT);
}

# koppel de huizennummers aan huizen
//$kh = array(0,3,6,7,10,11,13,1,15,16,17,19,21,2,22,8,18,5);
$kh = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18);
$khd = array(
	1 => array ("'t Ailand", 'Derde Werelddreef 75', ''),
	2 => array ("De Tolhuis-Alliantie","Westplantsoen 16/14a","2197748/2197749"),
	3 => array ("De Ambassade","Papsouwselaan 418","2614111"),
	4 => array ('Bras 98', 'Brasserkade 98',''),
	5 => array ("Caesarea","Cesar Franckstraat 176","2576721"),
	6 => array ("De Gouden Leeuw","Cornelis Trompstraat 34","2628231"),
	7 => array ('Huize den Hertog', 'Ternatestraat 5', ''),
	8 => array ("Hotel Vlaams Gaius","Vlamingstraat 26b","2135340"),
	9 => array ("'t Internaat","Ternatestraat 83","2125825"),
	10=> array ("St. Joris","Brasserskade 67","-"),
	11=> array ("De Koornmarkt","Koornmarkt 81c","2139141/nog 9"),
	12=> array ("Lachai-Roi","Isaak Hoornbeekstraat 38","-"),
	13=> array ("De Molshoop","Molstraat 35a","2158504"),
	14=> array ("OD11","Oude Delft 11",""),
	15=> array ("Perron 0","Van Leeuwenhoeksingel 21a","2121897/2190511"),
	16=> array ("Sonnenvanck","Oostsingel 176","2144093"),
	17=> array ("Huize Van Speijk","Trompetstraat 19","2146067"),
	18=> array ("Spoorbijster","Van Leeuwenhoeksingel 14","2124058")
);

# namen opzoeken in de database
require('/srv/www/www.csrdelft.nl/lib/class.mysql.php');

ini_set('error_reporting', E_ALL & ~E_NOTICE);

#if (isset($_GET['bron'])) {
#	show_source($_SERVER['SCRIPT_FILENAME']);
#	exit;
#}

$s = 46; # $s = (int)$_GET['s']; # aantal sjaars
$h = 18; # $h = (int)$_GET['h']; # aantal huizen
$a = 8;  # $a = (int)$_GET['a']; # aantal avonden
#$m = (int)floor($s/$h);
#$m = (int)$_GET['m']; # max aantal sjaars per huis per avond
$r = 1;  # $r = (int)$_GET['r']; # wel of niet random

echo "<b>Parameterisatie:</b>\nAantal Sjaars: $s\nAantal Huizen: $h\nAantal Avonden: $a\n\n";

# we moeten onthouden...
# ... in welke huizen een sjaars is geweest ...
$visited = array(); # $visited[huis][] = sjaars
$visited_ah = array(); # $visited_ah[avond][huis][] = sjaars
$visited_sh = array(); # $visited_sh[sjaars][huis] = true
# ... en welke sjaars welke andere al heeft ontmoet
$seen = array(); # $seen[sjaars][] = sjaars

# sjaars die al in huizen wonen alvast rekening mee houden
//$visited_sh[2][5] = true;   # 0502 Bachet lachairoi
//$visited_sh[21][11] = true; # 0521 Van Leeuwen VLVS
//$visited_sh[3][12] = true;  # 0503 Bas v Berkel Kleipot

$visited_sh[17][16] = true; # 0617 Hemmo Brink Sonnenvanck
$visited_sh[1][2] = true; # 0601 Wim Hendriksen, Alliantie
$visited_sh[20][2] = true; # 0620 Marco van 't Wout, Alliantie
$visited_sh[29][1] = true;  # 0639 Niek Hage, 't Ailand
$visited_sh[12][3] = true; # 0612 Gerdien duijst, Ambassade


# het uiteindelijke rooster
# $sah[sjaars][avond] = huis.. etc...
$sah = array();
$ahs = array();

# huizen laten rondtellen
if ($r == 0) $ih = 1;
else $ih = rand(1,$h);

# de avonden langslopen
for ($ia = 1; $ia <= $a; $ia++) {
	# de sjaars langslopen
	#for ($is = 1; $is <= $s; $is++) {
	foreach ($ks as $is => $foo) {
		# wat foutmeldingen voorkomen
		if (!isset($ahs[$ia][$ih])) $ahs[$ia][$ih] = array();
		if (!isset($seen[$is])) $seen[$is] = array();
		# we hebben nu een avond en een sjaars, nu nog een huis voor m vinden...
		# zolang
		# - deze sjaars dit huis al bezocht heeft, of
		# - in het huidige huis (ih) een sjaars zit die deze sjaars (is) al ontmoet heeft
		# - het huis nog niet aan zn max sjaars is voor deze avond
		# nemen we het volgende huis
		$startih = $ih;
		# nieuw: begin met het max aantal sjaars per huis net iets te laag in te stellen, zodat
		# de huizen eerst goed vol komen, en daarna pas extra sjaars bij huizen
		$m = (int)floor($s/$h);
		$nofm = 0; # aantal huizen dat aan de max zit.
		while (isset($visited_sh[$is][$ih])
		       or count(array_intersect($ahs[$ia][$ih],$seen[$is])) > 0
		       or count($visited_ah[$ia][$ih]) >= $m ) {
			$ih = $ih % $h + 1; if ($ih == $startih) $m++; #die ('whraagh!!!');
			if (!isset($ahs[$ia][$ih])) $ahs[$ia][$ih] = array();
			
			# nieuw: als alle huizen zijn langsgelopen en ze allemaal max sjaars hebben
			# dan de max ophogen
			#if (count($visited_ah[$ia][$ih]) == $m) $nofm++;
			#if ($nofm == $h) $m++;
			
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
		if ($r == 0) $ih = $ih % $h + 1;
		else $ih = rand(1,$h);

	}
}

echo "<b>Eetplanrooster:</b>\n\n";

echo "        ";
for ($ia = 1; $ia <= $a; $ia++) echo str_pad('['.$ia.']', 10);
echo "\n";

#for ($is = 1; $is <= $s; $is++) {
foreach ($ks as $is => $foo) {
	echo str_pad($ks[$is],8);
	# nu alle avonden
	for ($ia = 1; $ia <= $a; $ia++) {
		echo str_pad($sah[$is][$ia],10);
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
	echo str_pad('S-'.$ks[$is].': ', 10);
	sort($seen[$is]);
	foreach($seen[$is] as $sjaars) {
		echo str_pad($ks[$sjaars],10);
	}
	echo "\n";
}

echo "\n<b>Welke sjaars komen op de huizen:</b>\n\n";
for ($ih = 1; $ih <= $h; $ih++) {
	echo str_pad('H-'.$ih, 10);
	if (!isset($visited[$ih])) $visited[$ih] = array();
	sort($visited[$ih]);
	foreach($visited[$ih] as $sjaars) {
		echo str_pad($ks[$sjaars],10);
	}
	echo "\n";
}

echo "\n<b>Op welke avond in welk huis hoeveel?:</b>\n\n";
for ($ih = 1; $ih <= $h; $ih++) {
	echo str_pad('H-'.$ih, 10);
	for ($ia = 1; $ia <= $a; $ia++) {
		if (!isset($visited_ah[$ia][$ih])) $visited_ah[$ia][$ih] = array();
		echo str_pad(count($visited_ah[$ia][$ih]),10);
	}
	echo "\n";
}

?>

Knorrie's Eetplangenerator
(c) C.S.R. Delft 2005 o/~
Met dank aan het keuvelkanaal #csrdelft voor het testen!

En vanwege het ontbreken van bugs, hier nog wat 'unexpected $': $$$$&euro;&euro;&euro;&euro;&euro;&euro;&euro;&euro;&euro;&euro;&euro;&euro;&euro;&euro;&euro;!!~~!`!~11~ :P

<?
echo "<b># Eetplanrooster SQL:</b>\n\n";
for ($is = 1; $is <= $s; $is++) {
	for ($ia = 1; $ia <= $a; $ia++) {
		if(isset($ks[$is])){
			echo "INSERT INTO `eetplan` (`avond`,`uid`,`huis`) VALUES ({$ia},'{$ks[$is]}',{$sah[$is][$ia]});\n";
		}
	}
	echo "\n";
}

echo "\n# <b>De Woonoorden</b>\n\n";
for ($ih = 1; $ih <= $h; $ih++) {
	echo "INSERT INTO `eetplanhuis` (`id`,`naam`,`adres`,`telefoon`) VALUES ({$ih},'{$khd[$kh[$ih]][0]}','{$khd[$kh[$ih]][1]}', '{$khd[$kh[$ih]][2]}');\n";
}

?>
</pre>
