<?php

include_once('lib/include.common.php');
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
  <title>C.S.R.-Delft OWee | Logeren in een C.S.R.-huis!</title>
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
<div id="posterPagina">
	<a href="index.php"><img class="poster" src="plaatjes/poster.jpg" alt="OWee Poster" /></a>
	<div id="posterZijtekst">
		<h1>Logeren in een C.S.R.-huis</h1>
<?php
if(!isset($_POST['naam']) AND !isset($_POST['email']) AND !isset($_POST['telefoon'])){
		echo 
			'Het is tijdens de OWee wel handig als je in Delft kunt slapen. 
			Daarom biedt C.S.R. jou een slaapplek aan tijdens de OWee. 
			Zo leer je direct wat mensen kennen, en hoef je niet op en neer te reizen.<br />
			Als je interesse hebt in een slaapplek, vul dat dan hieronder in. 
			Uiteraard zijn er aan deze slaapplek geen kosten verbonden:<br /><br />';
		oweeForm('logeren.php');
}else{
	if(trim($_POST['naam'])!='' AND trim($_POST['email'])!='' AND oweeFormMail('informatie')){
		echo 'Bedankt voor het aanvragen van een logeerplek, er wordt zo spoedig mogelijk contact met je opgenomen.';
		echo '<br /><br /><br /><br />';
	}else{	
		echo 'Naam en Email zijn wel beide nodig!';
		oweeForm('logeren.php');
	}
}
?>	<br /><br /><br /><br /><br />
		<br /><br /><br /><br /><br />
		<?php oweeLinks(); ?>
	</div>
</div>
<?php oweeCredits(); ?>

</body>
</html>
