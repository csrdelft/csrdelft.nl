#!/usr/bin/php5
<?php
error_reporting(E_ALL);


require_once('/srv/www/www.csrdelft.nl/lib/configuratie.include.php');

define('STATS_DIR', PICS_PATH . '/stats/');

uurstats($db, STATS_DIR . 'uurstats.png');
dagstats($db, STATS_DIR . 'dagstats.png');

uurstatsVoorDag($db, STATS_DIR . 'zondag.png', 1);
uurstatsVoorDag($db, STATS_DIR . 'maandag.png', 2);
uurstatsVoorDag($db, STATS_DIR . 'dinsdag.png', 3);
uurstatsVoorDag($db, STATS_DIR . 'woensdag.png', 4);
uurstatsVoorDag($db, STATS_DIR . 'donderdag.png', 5);
uurstatsVoorDag($db, STATS_DIR . 'vrijdag.png', 6);
uurstatsVoorDag($db, STATS_DIR . 'zaterdag.png', 7);

echo 'Statistieken-grafieken geklust om ' . getDateTime() . "\r\n";

class StatistiekGrafiek {

//	var $_path=PICS_PATH.'/stats/';

	var $_image;
	var $_db;

	function StatistiekGrafiek(&$db) {
		
	}

	function getGdImage() {
		return $this->_image;
	}

	function writeFile() {
		
	}

	function writeBrowser() {
		
	}

}

function dagstats($db, $filename) {
	//get Data from DB
	$chartSQL = "
	  SELECT 
	    LEFT(moment, 7) AS mounth,
	    LEFT(moment, 10) AS day,
	    COUNT(*) AS hits
	  FROM 
	    log
	  WHERE 
	    TO_DAYS(NOW())-TO_DAYS(moment)<=31
	  GROUP BY 
	    LEFT(moment, 10)
	  ORDER BY
	    mounth ASC, day ASC;";
	$chartResult = $db->query($chartSQL);
	$valueCount = $db->numRows($chartResult);
	$maxChartSQL = "
	  SELECT 
	    LEFT(moment, 10) AS hour,
	    COUNT(*) AS hits
	  FROM 
	    log
	  WHERE 
	    TO_DAYS(NOW())-TO_DAYS(moment)<=31
	  GROUP BY 
	    LEFT(moment, 10)
	  ORDER BY
	    hits DESC
	  LIMIT 1;";
	$maxResult = $db->query($maxChartSQL);
	$maxData = $db->next($maxResult);
	$maxValue = $maxData['hits'];

	//demensions
	$chartstartX = 30; //begin van de grafiek.
	$width = $chartstartX + (10 * $valueCount);
	$heigth = 100;
	$chartEndY = 85;

	$im = imagecreate($width, $heigth) or die("Cannot Initialize new GD image stream");

	//colors:
	$backgroundColor = imagecolorallocate($im, 240, 240, 240);
	$chartColorA = imagecolorallocate($im, 233, 14, 91);
	$chartColorB = imagecolorallocate($im, 0, 0, 0);
	$chartBackgroundColor = imagecolorallocate($im, 255, 244, 244);
	$middleLineColor = imagecolorallocate($im, 153, 51, 51);

	//fill background
	imagefill($im, 1, 1, $backgroundColor);

	//chart background:
	imagerectangle($im, $chartstartX, 0, $width, $chartEndY, $chartBackgroundColor);
	imagefill($im, $chartstartX + 1, 2, $chartBackgroundColor);

	imageline($im, $chartstartX, 0, $chartstartX, $heigth, $chartColorB); //left line
	imageLine($im, 0, $chartEndY, $width, $chartEndY, $chartColorB); // bottom line



	imagestring($im, 1, 1, 1, round($maxValue, 0), $chartColorB); //diplay maximum value
	//display a line in the vertical center
	if (strlen(ceil($maxValue)) < 2) {
		$roundValue = 0;
	} else {
		$roundValue = -1;
	}
	$middleLine = round($maxValue / 2, $roundValue);
	if ($maxValue > $middleLine) {
		$fiftyLineX = $chartEndY - ($middleLine * $chartEndY) / $maxValue;
		imageline($im, $chartstartX - 2, $fiftyLineX, $width, $fiftyLineX, $middleLineColor);
		imagestring($im, 1, 1, $fiftyLineX - 2, $middleLine, $chartColorB);
	}

	$y = $chartstartX + 1;
	$hour = 0;
	while ($chartData = mysql_fetch_array($chartResult)) {
		$lineHeight = ($chartData['hits'] * $chartEndY) / $maxValue; //calculate lineHeight
		$lineStartY = $chartEndY - $lineHeight; //calculate y coordinate to start at
		if (($hour % 2) == 1) {//change colors
			$chartColor = $chartColorA;
		} else {
			$chartColor = $chartColorB;
		}
		//paint a line (thickness 4px)
		for ($i = 0; $i < 240 / 24 - 1; $i++) {
			imageline($im, $y + $i, $lineStartY, $y + $i, $chartEndY, $chartColor);
		}
		//give different mounths different colors
		if ((substr($chartData['mounth'], 5, 2) % 2) == 1) {
			$dayColor = $chartColorB;
		} else {
			$dayColor = $chartColorA;
		}
		//hours at the bottom of the image
		imagestringup($im, 1, $y + 1, $chartEndY + 12, substr($chartData['day'], 8, 2), $dayColor);

		$y = $y + (240 / 24);
		$hour++;
	}
	//write messages on image:
	imagestring($im, 2, $chartstartX + 3, 1, "C.S.R. Delft", $chartColorB); //filename
	imagestring($im, 2, $chartstartX + 3, 12, "bezoeken/dag", $chartColorB); //type of chart
	imagepng($im, $filename); //send image to file
	imagedestroy($im); //empty stack
}

function uurstats($db, $filename) {
	//get Data from DB
	$chartSQL = "
	  SELECT 
	    SUBSTRING(moment, 12, 2) AS hour,
	    ROUND( ( COUNT(*) / 7), 2) AS hits
	  FROM 
	    log
	  WHERE 
	    TO_DAYS(NOW())-TO_DAYS(moment)<=14
	  GROUP BY 
	    SUBSTRING(moment, 12, 2 );";
	$chartResult = $db->query($chartSQL);
	$maxChartSQL = "
	  SELECT 
	    SUBSTRING(moment,12,2) AS hour, 
	    ROUND((COUNT(*)/7), 2) AS hits
	  FROM 
	    log
	  WHERE 
	    TO_DAYS(NOW())-TO_DAYS(moment) <= 14 
	  GROUP BY 
	    SUBSTRING(moment,12,2) 
	  ORDER BY 
	    hits DESC
	  LIMIT 1;";
	$maxResult = $db->query($maxChartSQL);
	$maxData = $db->next($maxResult);
	$maxValue = $maxData['hits'];

	//demensions
	$width = 270;
	$heigth = 100;
	$chartstartX = 30;
	$chartEndY = 85;
	$im = @imagecreate($width, $heigth) or die("Cannot Initialize new GD image stream");

	//colors:
	$backgroundColor = imagecolorallocate($im, 240, 240, 240);
	$chartColorA = imagecolorallocate($im, 233, 14, 91);
	$chartColorB = imagecolorallocate($im, 0, 0, 0);
	$chartBackgroundColor = imagecolorallocate($im, 255, 244, 244);
	$middleLineColor = imagecolorallocate($im, 153, 51, 51);

	imagefill($im, 1, 1, $backgroundColor); //fill background
	//chart background:
	imagerectangle($im, $chartstartX, 0, $width, $chartEndY, $chartBackgroundColor);
	imagefill($im, $chartstartX + 1, 2, $chartBackgroundColor);

	imageline($im, $chartstartX, 0, $chartstartX, $heigth, $chartColorB); //left line
	imageLine($im, 0, $chartEndY, $width, $chartEndY, $chartColorB); // bottom line
	//write messages on image:
	imagestring($im, 2, $chartstartX + 3, 1, "C.S.R. Delft", $chartColorA); //filename
	imagestring($im, 2, $chartstartX + 3, 12, "uurgemiddelde", $chartColorB); //type of chart

	imagestring($im, 1, 2, 1, round($maxValue, 0), $chartColorB); //diplay maximum value
	//display a line in the vertical center
	if (strlen(round($maxValue, 0)) < 2) {
		$roundValue = 0;
	} else {
		$roundValue = -1;
	}
	$middleLine = round($maxValue / 2, $roundValue);
	if ($maxValue > $middleLine) {
		$fiftyLineX = $chartEndY - ($middleLine * $chartEndY) / $maxValue;
		imageline($im, $chartstartX - 2, $fiftyLineX, $width, $fiftyLineX, $middleLineColor);
		imagestring($im, 1, 2, $fiftyLineX - 2, $middleLine, $chartColorB);
	}

	//put values in an array
	$hour = 0;
	$chartData = mysql_fetch_array($chartResult);
	while ($hour <= 23) {
		if ($chartData['hour'] == $hour) {
			$chartArray[$hour] = $chartData['hits'];
			$chartData = mysql_fetch_array($chartResult);
		} else {
			$chartArray[$hour] = 0;
		}
		$hour++;
	}

	$y = $chartstartX + 1;
	$hour = 0;
	while ($hour <= 23) {
		$lineHeight = ($chartArray[$hour] * $chartEndY) / $maxValue; //calculate lineHeight
		$lineStartY = $chartEndY - $lineHeight; //calculate y coordinate to start at
		if (is_int($hour / 2)) {//change colors
			$chartColor = $chartColorA;
		} else {
			$chartColor = $chartColorB;
		}
		for ($i = 0; $i < 240 / 24 - 1; $i++) {//paint a line (thickness 4px)
			imageline($im, $y + $i, $lineStartY, $y + $i, $chartEndY, $chartColor);
		}
		imagestringup($im, 1, $y + 1, $chartEndY + 12, $hour, $chartColorB); //hours at the bottom of the image
		$y = $y + (240 / 24);
		$hour++;
	}
	imagepng($im, $filename); //send image to browser 
	imagedestroy($im); //empty stack
}

function uurstatsVoorDag($db, $filename, $dag) {
	$dag = (int) $dag;
	//get Data from DB
	$chartSQL = "
	  SELECT 
	    SUBSTRING(moment, 12, 2) AS hour,
	    ROUND( ( COUNT(*) / 7), 2) AS hits
	  FROM 
	    log
	  WHERE 
	  	DAYOFWEEK(moment)=" . $dag . "
	  GROUP BY 
	    SUBSTRING(moment, 12, 2 );";
	$chartResult = $db->query($chartSQL);
	echo mysql_error();
	$maxChartSQL = "
	  SELECT 
	    SUBSTRING(moment,12,2) AS hour, 
	    ROUND((COUNT(*)/7), 2) AS hits
	  FROM 
	    log
	  WHERE 
	  	DAYOFWEEK(moment)=" . $dag . "
	  GROUP BY 
	    SUBSTRING(moment,12,2) 
	  ORDER BY 
	    hits DESC
	  LIMIT 1;";
	$maxResult = $db->query($maxChartSQL);
	$maxData = $db->next($maxResult);
	$maxValue = $maxData['hits'];

	//demensions
	$width = 270;
	$heigth = 100;
	$chartstartX = 30;
	$chartEndY = 85;
	$im = @imagecreate($width, $heigth) or die("Cannot Initialize new GD image stream");

	//colors:
	$backgroundColor = imagecolorallocate($im, 240, 240, 240);
	$chartColorA = imagecolorallocate($im, 233, 14, 91);
	$chartColorB = imagecolorallocate($im, 0, 0, 0);
	$chartBackgroundColor = imagecolorallocate($im, 255, 244, 244);
	$middleLineColor = imagecolorallocate($im, 153, 51, 51);

	imagefill($im, 1, 1, $backgroundColor); //fill background
	//chart background:
	imagerectangle($im, $chartstartX, 0, $width, $chartEndY, $chartBackgroundColor);
	imagefill($im, $chartstartX + 1, 2, $chartBackgroundColor);

	imageline($im, $chartstartX, 0, $chartstartX, $heigth, $chartColorB); //left line
	imageLine($im, 0, $chartEndY, $width, $chartEndY, $chartColorB); // bottom line
	//write messages on image:
	imagestring($im, 2, $chartstartX + 3, 1, "C.S.R. Delft", $chartColorA); //filename
	$dagen = array('zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag');
	imagestring($im, 2, $chartstartX + 3, 12, $dagen[$dag - 1], $chartColorB); //type of chart
	imagestring($im, 1, 2, 1, round($maxValue, 0), $chartColorB); //diplay maximum value
	//display a line in the vertical center
	if (strlen(round($maxValue, 0)) < 2) {
		$roundValue = 0;
	} else {
		$roundValue = -1;
	}
	$middleLine = round($maxValue / 2, $roundValue);
	if ($maxValue > $middleLine) {
		$fiftyLineX = $chartEndY - ($middleLine * $chartEndY) / $maxValue;
		imageline($im, $chartstartX - 2, $fiftyLineX, $width, $fiftyLineX, $middleLineColor);
		imagestring($im, 1, 2, $fiftyLineX - 2, $middleLine, $chartColorB);
	}

	//put values in an array
	$hour = 0;
	$chartData = mysql_fetch_array($chartResult);
	while ($hour <= 23) {
		if ($chartData['hour'] == $hour) {
			$chartArray[$hour] = $chartData['hits'];
			$chartData = mysql_fetch_array($chartResult);
		} else {
			$chartArray[$hour] = 0;
		}
		$hour++;
	}

	$y = $chartstartX + 1;
	$hour = 0;
	while ($hour <= 23) {
		$lineHeight = ($chartArray[$hour] * $chartEndY) / $maxValue; //calculate lineHeight
		$lineStartY = $chartEndY - $lineHeight; //calculate y coordinate to start at
		if (is_int($hour / 2)) {//change colors
			$chartColor = $chartColorA;
		} else {
			$chartColor = $chartColorB;
		}
		for ($i = 0; $i < 240 / 24 - 1; $i++) {//paint a line (thickness 4px)
			imageline($im, $y + $i, $lineStartY, $y + $i, $chartEndY, $chartColor);
		}
		imagestringup($im, 1, $y + 1, $chartEndY + 12, $hour, $chartColorB); //hours at the bottom of the image
		$y = $y + (240 / 24);
		$hour++;
	}
	imagepng($im, $filename); //send image to browser 
	imagedestroy($im); //empty stack
}
?>
