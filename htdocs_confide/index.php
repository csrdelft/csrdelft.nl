<?php
/*
 * Ding om dingen uit de socciestreeplijst-db te halen.
 */

define('ETC_PATH', '.');
require_once 'common.functions.php';
require_once 'mysql.class.php';
require_once 'streeplijstrapportage.php';

$db=MySql::instance();

//lijstje met artikelen regelen.
$artikelenResult=$db->query("SELECT Naam as naam, Sneltoets as letter, Prijs as prijs FROM Artikelen;");
$artikelen=array();
while($art=$db->next($artikelenResult)){
	$artikelen[$art['letter']]=$art;
}

//lijst met omzetten voor de afgelopen weken regelen.
if(isset($_GET['start'])){
	$start=date('Y-m-d H:i:s', strtotime(((int)$_GET['start']).' months ago'));
}else{
	$start=date('Y-m-d H:i:s', strtotime('4 months ago'));
}
$weekrapportQuery="
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="nl" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>SocCie streeepcomputerrapportage</title>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="PubCie C.S.R. Delft - Jieter" />
	<meta name="robots" content="nfollow" />
	<link rel="stylesheet" href="default.css" type="text/css" />
	<link rel="shortcut icon" href="http://plaetjes.csrdelft.nl/layout/favicon.ico" />
</head>
<body>
<h1>Streeplijstrapportage SocCie</h1>
Let op: omzetcijfers kloppen niet voor de weken  de geschiedenis v&oacute;&oacute;r prijswijzigingen.
<?php
echo '<table class="weken">
		<tr>
			<th>Week</th><th>#bestellingen</th><th>detail</th><th>inleg</th><th>Omzet</th>
		</tr>';

foreach($res as $row){
	$week=$row['week'];
	echo '<tr>';
	echo '<td>'.$week.'</td>';
	echo '<td>'.$row['aantal_bestellingen'].'</td>';

	$bestellingen=parse_bestelstring($row['bestelstring']);

	$inleg=0;
	$omzet=0;

	echo '<td>';

	echo '<a class="handje" onclick="document.getElementById(\'details-'.$week.'\').style.display=\'block\'; this.parentNode.removeChild(this);">Toon details</a>';
	echo '<table id="details-'.$week.'" class="artikelen verborgen"><tr><th>artikel</th><th>aantal</th><th>omzet</th></tr>';
	foreach($bestellingen as $letter => $aantal){
		$artikelomzet=$artikelen[$letter]['prijs']*$aantal;
		echo '<tr><td>'.$artikelen[$letter]['naam'].'</td>';
		echo '<td>'.$aantal.'</td>';
		echo '<td class="euro">'.euro($artikelomzet).'</td>';
		echo '</tr>';
		if($artikelomzet<0){
			$inleg+=$artikelomzet;
		}else{
			$omzet+=$artikelomzet;
		}

	}
	echo '</table></td>';
	echo '<td>'.euro(0-$inleg).'</td>';
		echo '<td>'.euro($omzet).'</td>';


	echo '</tr>';

}
?>
</table>
</body>
</html>