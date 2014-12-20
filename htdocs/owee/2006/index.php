<?php
include_once('lib/class.imagemap.php');
include_once('lib/include.common.php');

$studentenleven=new Imagemap('poster', 'plaatjes/poster.jpg');

?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
  <title>C.S.R.-Delft OWee | Welkom!</title>
  <meta http-equiv='content-type' content='text/html; charset=UTF-8' />
	<meta name='author' content='PubCie C.S.R.-Delft, Jan Pieter Waagmeester' />
	<meta name='robots' content='index, follow' />
	<link rel='stylesheet' href='plaatjes/default.css' type='text/css' />

	<style type="text/css">
		<?php echo $studentenleven->getCss(); ?>
	</style>
	<script type="text/javascript">
			<?php echo $studentenleven->getJavascript(); ?>
		</script>
</head>
<body>
<div id="posterPagina">
	<img class="poster" src="plaatjes/poster.jpg" alt="OWee Poster" />
	<div id="posterZijtekst">
		<h1>Welkom op de C.S.R.-OWee-site!</h1>
		C.S.R. is de grootste Christelijke studentenvereniging van Delft. Met een rijke historie, een splinternieuwe
		soci&euml;teit in de oude binnenstad, en zo’n 25 verenigingshuizen heeft C.S.R. veel te bieden. 
		Van 20 tot en met 24 augustus is voor jou de OWee (<strong>O</strong>ntvangst<strong>Wee</strong>k) georganiseerd! 
		Natuurlijk ben je ook buiten de OWee van harte welkom op een C.S.R.-maaltijd of op een borrel. Bel gerust met het 
		bestuur om een afspraak te maken (telefoon 015-2135681).
		<br />  
		<br /><br />
		<?php oweeLinks(); ?>
		<br /><br /><br /><br />
		
		<form action="lidworden.php" method="post">
			<table id="formTable"> 
				<tr>
					<td colspan="2">
						<h2>Meer informatie</h2>
						Heb je nog vragen, of wil je nóg meer informatie? Vul dan het onderstaande formulier in:
					</td>
				</tr>
				<tr><td>Naam</td><td><input id="name" name="naam" class="inputLicht" /></td></tr>
				<tr><td>E-mail</td><td><input id="address" name="email" class="inputLicht" /></td></tr>
				<tr><td>Telefoonnummer</td><td><input id="city" name="telefoon" class="inputLicht" /></td></tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" id="submit" name="verzenden" value="verzenden" class="inputLicht" /></td>
				</tr>
			</table>
		</form>
	</div>
</div>

<?php oweeCredits(); ?>

</body>
</html>
