<?php
include_once('lib/include.common.php');
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
  <title>C.S.R.-Delft OWee | Meer Informatie</title>
  <meta http-equiv='content-type' content='text/html; charset=UTF-8' />
	<meta name='author' content='PubCie C.S.R.-Delft, Jan Pieter Waagmeester' />
	<meta name='robots' content='index, follow' />
	<link rel='stylesheet' href='plaatjes/default.css' type='text/css' />

	<style type="text/css">
	</style>
	<script type="text/javascript">
	</script>
</head>
<body>
<div id="omringendeContainer">
	<a href="index.php"><img class="linkerkantPagina" id="logoGroot" src="plaatjes/logo_groot.jpg" alt="OWee logo" /></a>
	<div class="rechterkantPagina">
		<?php
		if(isset($_POST['naam']) AND isset($_POST['email']) AND isset($_POST['telefoon'])){
			if(trim($_POST['naam'])!='' AND trim($_POST['email'])!='' AND oweeFormMail('lidworden')){
				echo '<h1 class="titel">Aanvraag gelukt!</h1>De OWeeCommmissie zal zo spoedig mogelijk contact met je opnemen.';
			}else{	
				echo '<h1 class="titel">Naam en e-mail invullen!</h1>Naam en e-mail zijn wel beide nodig!<br /><br />';
				oweeForm('lidworden.php');
			}	
		}else{
			echo 'Oeps, er ging iets mis...';
		} ?>
		<br />
		<br />
		<br />
		<br />
		<?php oweeLinks(); ?>
	</div>
	<div id="onderkantPagina">
		<?php oweeThema(); ?>
	</div>
</div>
<?php oweeCredits(); ?>
</body>
</html>
