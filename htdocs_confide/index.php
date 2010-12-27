<?php
/*
 * Ding om dingen uit de socciestreeplijst-db te halen.
 */


define('ETC_PATH', '.');
require_once 'common.functions.php';
require_once 'mysql.class.php';
require_once 'simplehtml.class.php';

function parse_bestelstring($string){
	$ptr=0;
	$return=array();

	$multiplier=1;

	while($ptr<strlen($string)){

		$current=substr($string, $ptr, 1);
		echo $ptr.' - '.$current.'<br />';
		if($current=='h'){
			$multiplier=0.5;
		}elseif(is_numeric($current)){
			$numptr=$ptr+1;
			$num=$current;
			while(is_numeric(substr($string, $numptr, 1))){
				$num.=substr($string, $numptr, 1);
				$numptr++;
			}
			$multiplier=$num;
			//sla het gelezen getal over.
			$ptr=$numptr-1;
		}else{
			$add=($multiplier*1);
			if(isset($return[$current])){
				$return[$current]+=$add;
			}else{
				$return[$current]=$add;
			}
			$multiplier=1;
		}
		$ptr++;
	}
	return $return;
}
pr(parse_bestelstring('30bbhihi'));
exit;
$db=MySql::instance();


$start=date('Y-m-d H:i:s', strtotime('2 months ago'));

$query="
	SELECT week(Tijdstip) as week, SUM(Bedrag) as omzet, count(*) as aantal_bestellingen, GROUP_CONCAT(Artikelen SEPARATOR '') as bestelstring
	FROM Bestellingen
	WHERE Tijdstip>'".$start."' AND Bedrag!=0
	GROUP BY week
	ORDER BY week DESC;";

$res=array(
array(
	'week' => "49",
	'omzet' => "157.65",
	'aantal_bestellingen' => "198",
	'bestelstring'=>"rbbbblbbbflllffffbbbbblllllbbbbllfxllllllfflflllllfmlmlmlmlmfllmlliifb4b3bfbf3fffb4ffffffbbblll5blsffbbb4bibllfl4bllbfmgmffbbbbbbfbbffbfffflllll6ilbfbbbbbllmmffbbbbbiibbliiiihihibbbfllffblllbbbflbbppfllfbbssggpffbbffbbbbbbbfllbzzbbblbflllfppppbbbfbfflllllliiifsffbbbpflbsssssggpbbbll35bfflfbbxbbllffllllfbbllllfibbbbbbbb5bf4bf4bll4bbfbbbb2l3bllf5b5bbbllfllbbbbblfllliiiillll5bfii500ollbll5bbbll3bllddl6bxssbbllbbbblflllllllbbllzzsfblffblls2b2f2g2lbb2l4bllbbbbbbbzbii3b"),
array(
	'week' => "48",
	'omzet' => "70.00",
	'aantal_bestellingen' => "29",
	'bestelstring' => "zllssssssssssgffxbbxdddlllffxfxgfbbxgfxlgff4bgw6b2f2l3l1b2l"));

echo '<table>
		<tr>
			<th>Week</th><th>Omzet</th><th>#bestellingen</th><th>detail</th>
		</tr>';

foreach($res as $row){
	echo '<tr>';
	echo '<td>'.$row['week'].'</td>';
	echo '<td>'.$row['omzet'].'</td>';
	echo '<td>'.$row['aantal_bestellingen'].'</td>';
	echo '<td>'.parse_bestelstring($row['bestelstring']).'</td>';


	echo '</tr>';

}
echo '</table>';