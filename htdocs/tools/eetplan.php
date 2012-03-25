<?php

# koppel de sjaarsnummers aan de sjaars
#
# 0 oud uid, uit oude lichtingen 0946, 1045, 1053.
# nummers die missen in 11; 1, 4,22,24,31,40,42,60,61,62,64

for ($es=46;$es<=46;$es++) {
	$uid=str_pad($es+900, 4, "0", STR_PAD_LEFT);
	$ks[$uid] = $uid;
}
for ($es=45;$es<=45;$es++) {
	$uid=str_pad($es+1000, 4, "0", STR_PAD_LEFT);
	$ks[$uid] = $uid;
}
for ($es=53;$es<=53;$es++) {
	$uid=str_pad($es+1000, 4, "0", STR_PAD_LEFT);
	$ks[$uid] = $uid;
}
for ($es=1;$es<=65;$es++) {
	if(!in_array($es,array(1,3,4,20,22,24,31,33,40,42,52,56,58,60,61,62,64))){
		$uid=str_pad($es+1100, 4, "0", STR_PAD_LEFT);
		$ks[$uid] = $uid;
	}
}
// $ks['0624']='0624'; dennis Lakerveld


# koppel de huizennummers aan huizen
//$kh = array(0,3,6,7,10,11,13,1,15,16,17,19,21,2,22,8,18,5);
$kh = array(0,1,2,3,5,7,8,9,10,11,12,13,15,16,18,19,20);// 4, 6, 14, 17 missen
$khd = array(
	0 => array('','',''),
	1 => array ('Oranje Boven', 1016, ''),
	2 => array ('Villa Veel Vaudt', 954,''),
	3 => array ("De Ambassade", 6, "2614111"),
	4 => array ('De Zilveren Hinde', 1017, ''),
	5 => array ('Lindenburgh', 58,''),
	6 => array ('Het Roze Nest', 788,''),
	7 => array ("De Molshoop", 34, ''),
	8 => array ("Villa Delphia", 37, '015-2137153'),
	9 => array ('Huize * Asterix', 46, ''),
	10=> array ('t Internaat', 9 ,"2125825"),
	11=> array ("De Koornmarkt",33 ,""),
	12=> array ("Lachai-Roi",57 ,""),
	13=> array ("OD11",14 ,""),
	14=> array ("De Preekstoel", 62,""),
	15=> array ("Sonnenvanck",36 ,''),
	16=> array ("Huize Van Speijk", 39,"2146067"),
	17=> array ("De Tolhuis-Alliantie", 10, '015-2160585'),
	18=> array ("Hotel Vlaams Gaius", 32, '015-2160585'),
	19=> array ("Residentes in Vita Detissima", 494, ''),
	20=> array ("Caesarea", 7, '')

);

# namen opzoeken in de database
require('configuratie.include.php');

ini_set('error_reporting', E_ALL & ~E_NOTICE);

#if (isset($_GET['bron'])) {
#	show_source($_SERVER['SCRIPT_FILENAME']);
#	exit;
#}
echo '<pre>';

$s = (int)$_GET['s']; # aantal sjaars
$h = 17; # $h = (int)$_GET['h']; # aantal huizen
$a = 4;  # $a = (int)$_GET['a']; # aantal avonden
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
# voorbeeld: $visited_sh[$sjaarsuid][$huisuid]=true;
$visited_sh[1138][5]=true;//Martijn van den Berg (De Lindenburgh)
$visited_sh[0946][13]=true;//Niels Brandhorst (OD11)
$visited_sh[1109][13]=true;//Melanie Schippers (OD11)
$visited_sh[1113][15]=true;//Job van Stiphout (SSS)
$visited_sh[1112][10]=true;//Matthias Floor (internaat)
$visited_sh[1045][10]=true;//Vlot sr. (internaat)
$visited_sh[1151][11]=true;//Kirsten Neels (KMT)
$visited_sh[1139][1]=true;//Roos van Riggelen (Oranje Boven)
$visited_sh[1144][12]=true;//Margriet Vlot (Lacha-Roi)

//Eetplan semester 1
$visited_sh[0946][7]=true;
$visited_sh[1045][2]=true;
$visited_sh[1053][1]=true;
$visited_sh[1102][3]=true;
$visited_sh[1103][4]=true;
$visited_sh[1105][17]=true;
$visited_sh[1106][11]=true;
$visited_sh[1107][8]=true;
$visited_sh[1108][16]=true;
$visited_sh[1109][4]=true;
$visited_sh[1110][5]=true;
$visited_sh[1111][8]=true;
$visited_sh[1112][10]=true;
$visited_sh[1113][3]=true;
$visited_sh[1114][4]=true;
$visited_sh[1115][15]=true;
$visited_sh[1116][17]=true;
$visited_sh[1117][8]=true;
$visited_sh[1118][12]=true;
$visited_sh[1119][5]=true;
$visited_sh[1120][6]=true;
$visited_sh[1121][6]=true;
$visited_sh[1123][15]=true;
$visited_sh[1125][7]=true;
$visited_sh[1126][10]=true;
$visited_sh[1127][11]=true;
$visited_sh[1128][14]=true;
$visited_sh[1129][5]=true;
$visited_sh[1130][11]=true;
$visited_sh[1132][7]=true;
$visited_sh[1133][12]=true;
$visited_sh[1134][12]=true;
$visited_sh[1135][9]=true;
$visited_sh[1136][13]=true;
$visited_sh[1137][15]=true;
$visited_sh[1138][13]=true;
$visited_sh[1139][13]=true;
$visited_sh[1141][9]=true;
$visited_sh[1143][2]=true;
$visited_sh[1144][10]=true;
$visited_sh[1145][14]=true;
$visited_sh[1146][9]=true;
$visited_sh[1147][1]=true;
$visited_sh[1148][6]=true;
$visited_sh[1149][14]=true;
$visited_sh[1150][16]=true;
$visited_sh[1151][2]=true;
$visited_sh[1153][1]=true;
$visited_sh[1154][3]=true;
$visited_sh[1155][13]=true;
$visited_sh[1156][17]=true;
$visited_sh[1157][2]=true;
$visited_sh[1159][6]=true;
$visited_sh[1163][12]=true;
$visited_sh[1165][5]=true;
$visited_sh[0946][2]=true;
$visited_sh[1045][16]=true;
$visited_sh[1053][15]=true;
$visited_sh[1102][10]=true;
$visited_sh[1103][10]=true;
$visited_sh[1105][16]=true;
$visited_sh[1106][4]=true;
$visited_sh[1107][7]=true;
$visited_sh[1108][10]=true;
$visited_sh[1109][15]=true;
$visited_sh[1110][14]=true;
$visited_sh[1111][11]=true;
$visited_sh[1112][7]=true;
$visited_sh[1113][8]=true;
$visited_sh[1114][17]=true;
$visited_sh[1115][7]=true;
$visited_sh[1116][12]=true;
$visited_sh[1117][9]=true;
$visited_sh[1118][11]=true;
$visited_sh[1119][13]=true;
$visited_sh[1120][17]=true;
$visited_sh[1121][4]=true;
$visited_sh[1123][9]=true;
$visited_sh[1125][13]=true;
$visited_sh[1126][3]=true;
$visited_sh[1127][12]=true;
$visited_sh[1128][6]=true;
$visited_sh[1129][11]=true;
$visited_sh[1130][5]=true;
$visited_sh[1132][12]=true;
$visited_sh[1133][13]=true;
$visited_sh[1134][3]=true;
$visited_sh[1135][8]=true;
$visited_sh[1136][2]=true;
$visited_sh[1137][14]=true;
$visited_sh[1138][17]=true;
$visited_sh[1139][1]=true;
$visited_sh[1141][16]=true;
$visited_sh[1143][8]=true;
$visited_sh[1144][14]=true;
$visited_sh[1145][15]=true;
$visited_sh[1146][3]=true;
$visited_sh[1147][4]=true;
$visited_sh[1148][5]=true;
$visited_sh[1149][1]=true;
$visited_sh[1150][5]=true;
$visited_sh[1151][1]=true;
$visited_sh[1153][2]=true;
$visited_sh[1154][6]=true;
$visited_sh[1155][3]=true;
$visited_sh[1156][11]=true;
$visited_sh[1157][6]=true;
$visited_sh[1159][7]=true;
$visited_sh[1163][10]=true;
$visited_sh[1165][2]=true;
$visited_sh[0946][12]=true;
$visited_sh[1045][17]=true;
$visited_sh[1053][6]=true;
$visited_sh[1102][11]=true;
$visited_sh[1103][8]=true;
$visited_sh[1105][13]=true;
$visited_sh[1106][12]=true;
$visited_sh[1107][3]=true;
$visited_sh[1108][13]=true;
$visited_sh[1109][11]=true;
$visited_sh[1110][1]=true;
$visited_sh[1111][2]=true;
$visited_sh[1112][4]=true;
$visited_sh[1113][16]=true;
$visited_sh[1114][5]=true;
$visited_sh[1115][5]=true;
$visited_sh[1116][1]=true;
$visited_sh[1117][16]=true;
$visited_sh[1118][8]=true;
$visited_sh[1119][11]=true;
$visited_sh[1120][3]=true;
$visited_sh[1121][2]=true;
$visited_sh[1123][13]=true;
$visited_sh[1125][3]=true;
$visited_sh[1126][12]=true;
$visited_sh[1127][2]=true;
$visited_sh[1128][15]=true;
$visited_sh[1129][4]=true;
$visited_sh[1130][14]=true;
$visited_sh[1132][14]=true;
$visited_sh[1133][14]=true;
$visited_sh[1134][15]=true;
$visited_sh[1135][4]=true;
$visited_sh[1136][1]=true;
$visited_sh[1137][6]=true;
$visited_sh[1138][9]=true;
$visited_sh[1139][15]=true;
$visited_sh[1141][5]=true;
$visited_sh[1143][7]=true;
$visited_sh[1144][16]=true;
$visited_sh[1145][8]=true;
$visited_sh[1146][17]=true;
$visited_sh[1147][17]=true;
$visited_sh[1148][7]=true;
$visited_sh[1149][10]=true;
$visited_sh[1150][9]=true;
$visited_sh[1151][9]=true;
$visited_sh[1153][7]=true;
$visited_sh[1154][7]=true;
$visited_sh[1155][10]=true;
$visited_sh[1156][3]=true;
$visited_sh[1157][8]=true;
$visited_sh[1159][9]=true;
$visited_sh[1163][2]=true;
$visited_sh[1165][6]=true;
$visited_sh[0946][15]=true;
$visited_sh[1045][3]=true;
$visited_sh[1053][10]=true;
$visited_sh[1102][16]=true;
$visited_sh[1103][3]=true;
$visited_sh[1105][14]=true;
$visited_sh[1106][16]=true;
$visited_sh[1107][6]=true;
$visited_sh[1108][4]=true;
$visited_sh[1109][13]=true;
$visited_sh[1110][4]=true;
$visited_sh[1111][16]=true;
$visited_sh[1112][5]=true;
$visited_sh[1113][12]=true;
$visited_sh[1114][11]=true;
$visited_sh[1115][17]=true;
$visited_sh[1116][5]=true;
$visited_sh[1117][3]=true;
$visited_sh[1118][14]=true;
$visited_sh[1119][6]=true;
$visited_sh[1120][13]=true;
$visited_sh[1121][11]=true;
$visited_sh[1123][10]=true;
$visited_sh[1125][17]=true;
$visited_sh[1126][13]=true;
$visited_sh[1127][17]=true;
$visited_sh[1128][1]=true;
$visited_sh[1129][15]=true;
$visited_sh[1130][1]=true;
$visited_sh[1132][2]=true;
$visited_sh[1133][15]=true;
$visited_sh[1134][4]=true;
$visited_sh[1135][6]=true;
$visited_sh[1136][7]=true;
$visited_sh[1137][1]=true;
$visited_sh[1138][10]=true;
$visited_sh[1139][2]=true;
$visited_sh[1141][2]=true;
$visited_sh[1143][14]=true;
$visited_sh[1144][7]=true;
$visited_sh[1145][12]=true;
$visited_sh[1146][5]=true;
$visited_sh[1147][7]=true;
$visited_sh[1148][8]=true;
$visited_sh[1149][8]=true;
$visited_sh[1150][12]=true;
$visited_sh[1151][15]=true;
$visited_sh[1153][9]=true;
$visited_sh[1154][13]=true;
$visited_sh[1155][6]=true;
$visited_sh[1156][8]=true;
$visited_sh[1157][7]=true;
$visited_sh[1159][14]=true;
$visited_sh[1163][9]=true;
$visited_sh[1165][16]=true;


# het uiteindelijke rooster
# $sah[sjaars][avond] = huis.. etc...
$sah = array();
$ahs = array();

# data die al in de tabel zit om later feuten toe te kunnen voegen
$sql='SELECT avond, huis, GROUP_CONCAT(uid) AS uids FROM `eetplan` GROUP BY avond, huis';
$result=$db->query($sql);
while($rij=$db->next($result)){
	$sjaarsen=explode(',', $rij['uids']);
	
	foreach($sjaarsen as $foo => $sjaars){
		$visited[$rij['huis']][]=$sjaars;
		$visited_sh[$sjaars][$rij['huis']]=true;
		$visited_ah[$rij['avond']][$rij['huis']][]=$sjaars;
		$sah[$sjaars][$rij['avond']]=$rij['huis'];
		$sah[$rij['avond']][$rij['huis']]=$sjaars;
		
		foreach($sjaarsen as $subsjaars){
			$seen[$sjaars][$subsjaars]=true;
		}
	}
}



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
#for ($is = 1; $is <= $s; $is++) {
foreach ($ks as $is) {
	for ($ia = 1; $ia <= $a; $ia++) {
		if(isset($ks[$is])){
			echo "INSERT INTO `eetplan` (`avond`,`uid`,`huis`) VALUES ({$ia},'{$ks[$is]}',{$sah[$is][$ia]});\n";
		}
	}
	echo "\n";
}

echo "\n# <b>De Woonoorden</b>\n\n";
for ($ih = 1; $ih <= $h; $ih++) {
	echo "INSERT INTO `eetplanhuis` (`id`,`naam`, groepid,`telefoon`) VALUES ({$ih},'{$khd[$kh[$ih]][0]}',{$khd[$kh[$ih]][1]}, '{$khd[$kh[$ih]][2]}');\n";
}

?>
</pre>
