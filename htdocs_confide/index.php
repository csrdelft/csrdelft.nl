<?php
/*
 * Ding om dingen uit de socciestreeplijst-db te halen.
 */

define('ETC_PATH', '.');
session_start();

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
$weekResult=$db->query($weekrapportQuery);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="nl" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>SocCie streepcomputerrapportagegenereertool</title>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="PubCie C.S.R. Delft - Jieter" />
	<meta name="robots" content="nfollow" />
	<link rel="stylesheet" href="default.css" type="text/css" />
	<link rel="shortcut icon" href="http://plaetjes.csrdelft.nl/layout/favicon.ico" />
</head>
<body>

<?php
// 'inloggen'
if(!(isset($_SESSION['authenticated']) AND $_SESSION['authenticated'])){
	if(isset($_POST['password']) AND md5($_POST['password'])=='3f83075f710a248c7786cf72d0e501ce'){
		$_SESSION['authenticated']=true;
	}else{
		?>
		<h1>SoccieStreeplijstrapportagegeneratortool login</h1>

		<form method="post">
			<input type="password" name="password" /><input type="submit" value="inloggen" />
		</form>
		<?php

		exit;
	}
}

?>
<h1>Streeplijstrapportage SocCie</h1>
Let op: cijfers kloppen na prijswijzigingen niet voor de weken v&oacute;&oacute;r de wijziging. Weeknummer zijn <a href="http://en.wikipedia.org/wiki/ISO_week_date">ISO-weken</a>: van maandag tot zondag dus...
<a href="?start=6">half jaar</a>
<a href="?start=12">1 jaar</a>
<a href="?start=24">2 jaar</a>
<?php
echo '<table class="weken">
		<tr>
			<th>Week</th><th>#bestellingen</th><th>detail</th><th>inleg</th><th>Omzet</th>
		</tr>';

while($row=$db->next($weekResult)){
	$week=$row['week'];
	echo '<tr>';
	echo '<td>'.$week.'</td>';
	echo '<td>'.$row['aantal_bestellingen'].'</td>';

	$bestellingen=parse_bestelstring($row['bestelstring']);

	$inleg=0;
	$omzet=0;

	echo '<td>';

	echo '<a class="handje knop" onclick="document.getElementById(\'details-'.$week.'\').style.display=\'block\'; this.parentNode.removeChild(this);">&raquo; Toon details</a>';
	echo '<table id="details-'.$week.'" class="artikelen verborgen"><tr><th>artikel</th><th>#</th><th>omzet</th></tr>';
	foreach($bestellingen as $letter => $aantal){
		$artikelomzet=$artikelen[$letter]['prijs']*$aantal;
		echo '<tr><td>'.$artikelen[$letter]['naam'].'</td>';
		echo '<td>'.$aantal.'</td>';
		echo '<td class="euro">'.euro($artikelomzet).'</td>';
		echo '</tr>';
		if($letter=='h'){
			$inleg+=$artikelomzet;
		}else{
			if($artikelomzet<0){
				$inleg+=$artikelomzet;
			}else{
				$omzet+=$artikelomzet;
			}
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