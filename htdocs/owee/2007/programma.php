<?php
include_once('lib/class.imagemap.php');
include_once('lib/include.common.php');

$programma=new Imagemap('programma', 'plaatjes/programma.jpg', true);

$programma->addArea('programma', 'OWee-programma bij C.S.R.', '10,8,801,5,800,591,16,587', '
<h1>OWee-programma bij C.S.R.</h1>
<h2>Maandag 20 augustus</h2>
Na je eerste dag in Delft ben je op Confide welkom om een <strong>workshop Intelligent Design</strong> te volgen.<br />
Vervolgens is er een gezellige maaltijd met aansluitend een <strong>gratis kop koffie of thee</strong>. <br />
Na het eten begint een <strong>theaterstuk</strong> door leden van de C.S.R..<br />
<strong>Nuclear Playground</strong> zal na deze happening gaan knallen. Deze band is inmiddels landelijk aan het doorbreken en heeft onder andere opgetreden op het Flevofestival en WinterWonderRock.<br />
Tijdens de <strong>bergen-borrel</strong> kun je onder andere Whisky "on the rocks" drinken. Natuurlijk zal er ook weer ge<strong>barbecue</strong>d worden!<br />
<br />
17:00	Workshop "Intelligent Design"<br />
18:00	Maaltijd - meer dan alleen eten<br />
19:00	Gratis thee en koffie<br />
20:00	Theater (Happening)<br />
21:30	Nuclear Playground<br />
23:00	Bergen-borrel en -BBQ<br />
<br />
<h2>Dinsdag 21 augustus</h2>
Tijdens de Verenigingendag kun je bij ons golfen en de vereniging van binnenuit leren kennen.<br />
De dames zijn voor de maaltijd bijzonder welkom voor een <strong>High-Tea</strong>.<br /> 
Leven met God: Hoe doe je dat? Een <strong>bijbelstudiekring</strong> gaat na de maaltijd bezig met deze vraag.<br />
<strong>Rikkert Zuiderveld</strong> zet een brullend goed programma neer dat een combinatie is van cabaret-sketches, one-liners en muziek.<br />
Hierna begint de <strong>Jungle-borrel</strong>: vlees aan het spit, <em>Jazz in de Jungle</em> en <em>Tropische Taksi</em>.<br />
<br />
17:00	High-Tea (Alleen voor dames!)<br />
18:00	Maaltijd<br />
19:00	Bijbelstudie: Leven met God<br />
20:00	Rikkert Zuiderveld<br />
22:00	<em>Jazz in de Jungle</em>-borrel<br />
23:00	Vlees aan het spit<br />
<br />
<h2>Woensdag 22 augustus</h2>
Bij de <strong>Alphacursus</strong> kan iedereen met vragen over het geloof terecht. Het thema is <em>Wie is Jezus?</em>.<br />
Na de maaltijd is de <strong>bootjesborrel</strong>: in een bootje op de gracht genieten van gratis koffie en thee.<br />
Om 8 uur een lezing van Ewout van Oosten.<br />
Vanavond is de <strong>Pool-borrel</strong>: live-muziek, icetea en andere ijskoude drankjes warmen deze avond op. <br />
Ook vanavond is er weer een <strong>BBQ</strong> en <strong>extra vlees</strong> aan het spit.<br />
<br />
17:00	Alphacursus <em>Wie is Jezus?</em><br />
18:00	Maaltijd<br />
19:00	Bootjesborrel (Gratis koffie en thee)<br />
20:00	Lezing van Ewout van Oosten<br />
22:00	Pool-Borrel<br />
23:00	Smaakverwarring (live-muziek)<br />
23:00	BBQ / vlees aan het spit<br />
<br />
<h2>Donderdag 23 augustus</h2>
Pak die rust bij C.S.R.: een lekkere maaltijd, gratis koffie en thee en vooral veel gezelligheid.<br />
Deze avond veel <strong>spellen</strong>: Poker, Risk, Kolonisten, Machiavelli, Weerwolven en ga zo maar door.<br />
Tegelijkertijd is er een bijzonder relaxte <strong>strand-borrel</strong> met Hawa&iuml;aanse Hamburgers en muziek.
<br />
18:30	Maaltijd<br />
19:00	Gratis thee en koffie drinken en pak die rust<br />
20:00	Gezelschapsspellen spelen met het gezelschapsspellengezelschap<br />
20:00	Strandborrel en Hawa&iuml;aanse Hamburgers<br />
', true, true);
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
  <title>C.S.R.-Delft OWee | Programma</title>
  <meta http-equiv='content-type' content='text/html; charset=UTF-8' />
	<meta name='author' content='PubCie C.S.R.-Delft, Jan Pieter Waagmeester' />
	<meta name='robots' content='index, follow' />
	<link rel='stylesheet' href='plaatjes/default.css' type='text/css' />
	<style type="text/css">
		<?php echo $programma->getCss(); ?>
	</style>
	<script type="text/javascript">
			<?php echo $programma->getJavascript(); ?>
	</script>
	<script type="text/javascript" src="lib/overlib/overlib.js"></script>
	<script type="text/javascript" src="lib/overlib/overlib_cssw3c.js"></script>
</head>
<body>

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
	
	<div id="logo">
		<a href="index.php" onmouseover="return overlib(\'Terug naar het begin\', CSSW3C);" onmouseout="return nd();">
			<img src="plaatjes/logo_klein.gif" id="logoKlein" title="Terug naar het begin" />
		</a>
	</div>
<?php
	echo $programma->getImagemap(''); 
	oweeCredits();
	
?>
</body>
</html>
