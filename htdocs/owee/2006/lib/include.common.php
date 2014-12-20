<?php

# instellingen & rommeltjes
if(file_exists('/srv/www/www.csrdelft.nl/lib/include.config.php')){
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

}

function oweeLinks(){
	echo '
<!--<a class="meerLink" href="http://csrdelft.nl/informatie/">&raquo; Meer over C.S.R.</a><br />-->
<a class="meerLink" href="studentenleven.php">&raquo; C.S.R. en mijn studentenleven</a><br />
<a class="meerLink" href="programma.php">&raquo; Programma bij C.S.R. in de OWee</a><br />
<a class="meerLink" href="logeren.php">&raquo; Logeren bij C.S.R. in de OWee.</a><br /><br />
<a class="meerLink" href="http://csrdelft.nl">&times; Website van C.S.R.</a><br />
<a class="meerLink" href="http://owee.nl">&times; Delft OWee website.</a>';
}


function oweeCredits(){
	echo '
<div class="credits">
	&copy; 2006 <a href="http://csrdelft.nl">C.S.R.-Delft</a> | 
	Techniek: <a href="http://csrdelft.nl/informatie/commissie.php?cie=PubCie">PubCie</a>, Jieter | 
	Grafisch: <a href="http://csrdelft.nl/informatie/commissie.php?cie=OWeeCie">OWeeCie</a>, Willem Jan
</div>';
}

function FormValue($sName){
	if(isset($_POST[$sName])){
		return htmlentities($_POST[$sName]);
	}else{
		return '';
	}
}

function oweeForm($sAction){
echo '
<form action="'.$sAction.'" method="post">
	<table id="formTable"> 
		<tr>
			<td>Naam</td>
			<td><input id="name" name="naam" class="inputLicht" value="'.FormValue('naam').'" /></td>
		</tr>
		<tr>
			<td>E-mail</td>
			<td><input id="address" name="email" class="inputLicht" value="'.FormValue('email').'" /></td>
		</tr>
		<tr>
			<td>Telefoonnummer</td>
			<td><input id="address" name="telefoon" class="inputLicht" value="'.FormValue('telefoon').'" /></td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" id="submit" name="verzenden" value="verzenden" class="inputLicht" /></td>
		</tr>
	</table>
</form>';
}
function oweeFormMail($sFormdoel){
	$sNaam=trim(htmlentities($_POST['naam'], ENT_QUOTES, 'UTF-8'));
	$sEmail=trim(htmlentities($_POST['email'], ENT_QUOTES, 'UTF-8'));
	$sTelefoon=trim(htmlentities($_POST['telefoon'], ENT_QUOTES, 'UTF-8'));
	
	$sEmailBody=
"Er is een nieuwe aanvraag voor ".$sFormdoel." van de webstek. De gegevens zijn:
Naam: ".$sNaam."
Email: ".$sEmail."
Telefoonnummer: ".$sTelefoon."

ingevuld op: ".date('Y-m-d H:i:s');
	return mail('pubcie@csrdelft.nl, owee@csrdelft.nl', 'Nieuwe aanvraag voor '.$sFormdoel.' owee webstek', $sEmailBody);
}


?>
