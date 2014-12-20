<?php
include_once('lib/class.imagemap.php');
include_once('lib/include.common.php');
$studentenleven=new Imagemap('logo_groot', 'plaatjes/logo_groot.jpg');
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
  <title>C.S.R.-Delft OWee | Welkom!</title>
  <meta http-equiv='content-type' content='text/html; charset=UTF-8' />
	<meta name='author' content='PubCie C.S.R.-Delft, Jan Pieter Waagmeester' />
	<meta name='robots' content='index, follow' />
	<link rel='stylesheet' href='plaatjes/default.css' type='text/css' />
</head>
<body>
<div id="omringendeContainer">
	<a href="index.php"><img class="linkerkantPagina" id="logoGroot" src="plaatjes/logo_groot.jpg" alt="OWee logo" /></a>
	<div class="rechterkantPagina">
		<h1 class="titel">Welkom op de C.S.R.-OWee-site!</h1>
		C.S.R. is de grootste Christelijke studentenvereniging van Delft. Met
		een rijke historie, een splinternieuwe soci&euml;teit in de oude
		binnenstad en zo’n 25 verenigingshuizen heeft C.S.R. veel te bieden. Van
		19 tot en met 23 augustus is voor jou de OWee (<strong>O</strong>ntvangst<strong>Wee</strong>k)
		georganiseerd! Natuurlijk ben je ook buiten de OWee van harte welkom op
		een C.S.R.-maaltijd of op een borrel. Bel gerust met het bestuur om een
		afspraak te maken (015-2135681). <br />
		<br />
		<br />
		<?php oweeLinks(); ?>
	</div>
	<div id="onderkantPagina">
		<div class="rechterkantPagina">
			<h2 class="titel">Meer informatie</h2>
			Heb je nog vragen, of wil je nóg meer informatie? Vul dan het
			onderstaande formulier in: <br />
			<br />
			<?php oweeForm('lidworden.php'); ?>
		</div>
		<?php oweeThema(); ?>
	</div>
</div>

<?php oweeCredits(); ?>

</body>
</html>
