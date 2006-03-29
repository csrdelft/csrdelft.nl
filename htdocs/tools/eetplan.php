<pre>
<?php

# koppel de sjaarsnummers aan de sjaars
#
# 0501-0542
for ($es=1;$es<=25;$es++) $ks[$es] = str_pad($es+500, 4, "0", STR_PAD_LEFT);
# 0526 bestaat niet
for ($es=27;$es<=42;$es++) $ks[$es-1] = str_pad($es+500, 4, "0", STR_PAD_LEFT);

# koppel de huizennummers aan huizen
$kh = array(0,3,6,7,10,11,13,1,15,16,17,19,21,2,22,8,18,5);

$khd = array(
	1 => array ("OD251/11","Oude Delft 251/11","2134612"),
	2 => array ("De Tolhuis-Alliantie","Westplantsoen 16/14a","2197748/2197749"),
	3 => array ("De Ambassade","Papsouwselaan 418","2614111"),
	5 => array ("Caesarea","Cesar Franckstraat 176","2576721"),
	6 => array ("BalPol","Balthasar van de Polweg 334/386/412","2626718/2628648/2146651"),
	7 => array ("Hotel Vlaams","Vlamingstraat 26b","2135340"),
	8 => array ("'t Internaat","Ternatestraat 83","2125825"),
	10 => array ("De Koornmarkt","Koornmarkt 81c","2139141/nog 9"),
	11 => array ("Lachai-Roi","Isaak Hoornbeekstraat 38","-"),
	13 => array ("De Molshoop","Molstraat 35a","2158504"),
	15 => array ("Perron 0","Van Leeuwenhoeksingel 21a","2121897/2190511"),
	16 => array ("Sonnenvanck","Oostsingel 176","2144093"),
	17 => array ("Huize Van Speijk","Trompetstraat 19","2146067"),
	18 => array ("Spoorbijster","Van Leeuwenhoeksingel 14","2124058"),
	19 => array ("Villa Delphia/Lindenburgh","Arnoldstraat 20/VLVS 3","2137153/2141750"),
	21 => array ("De Kleipot","Cornelis Trompstraat 34","2628231"),
	22 => array ("St. Joris","Brasserskade 67","-")
);

# namen opzoeken in de database
require('/srv/www/www.csrdelft.nl/lib/class.mysql.php');

ini_set('error_reporting', E_ALL & ~E_NOTICE);

#if (isset($_GET['bron'])) {
#	show_source($_SERVER['SCRIPT_FILENAME']);
#	exit;
#}

$s = 41; # $s = (int)$_GET['s']; # aantal sjaars
$h = 17; # $h = (int)$_GET['h']; # aantal huizen
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
$visited_sh[2][5] = true;   # 0502 Bachet lachairoi
$visited_sh[21][11] = true; # 0521 Van Leeuwen VLVS
$visited_sh[3][12] = true;  # 0503 Bas v Berkel Kleipot

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
	for ($is = 1; $is <= $s; $is++) {
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

for ($is = 1; $is <= $s; $is++) {
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
for ($is = 1; $is <= $s; $is++) {
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
		echo "INSERT INTO `eetplan` (`avond`,`uid`,`huis`) VALUES ({$ia},'{$ks[$is]}',{$sah[$is][$ia]});\n";
	}
	echo "\n";
}

echo "\n# <b>De Woonoorden</b>\n\n";
for ($ih = 1; $ih <= $h; $ih++) {
	echo "INSERT INTO `eetplanhuis` (`id`,`naam`,`adres`,`telefoon`) VALUES ({$ih},'{$khd[$kh[$ih]][0]}','{$khd[$kh[$ih]][1]}', '{$khd[$kh[$ih]][2]}');\n";
}

?>
</pre>