<?php
require_once('inc.database.php');
require_once('inc.common.php');

echo '<table border="0" cellpadding="1" cellspacing="0" id="boekenTabel">';

// leden ophalen
$deelNaam = getGET('deelNaam');
$leden = getLeden($deelNaam);

for($i=0; $i < count($leden); $i++) {
	echo '<tr><td>';
	echo '<a href="javascript:uitlenenAan(\''.$leden[$i][0].'\')">'.$leden[$i][1].'</a>';
	echo '</td></tr>';
}

echo '</table>';
?>