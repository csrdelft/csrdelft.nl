<?php
# instellingen & rommeltjes
#require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

# login-systeem
/* Wordt (nog) nergens gebruikt.. mag dus wel uit.
require_once('class.lid.php');
require_once('class.mysql.php');
#$db = MySQL::get_MySql();
#$lid = Lid::get_Lid();
*/

function oweeThema(){
	echo '
	<div class="linkerkantPagina" id="themaVerhaal">
		<h2 class="titel">Hou je vast!!</h2>
		Dit jaar is ons OWee-thema "Hou je vast!". Als je in Delft gaat
		studeren zul je nieuwe, mooie dingen mee gaan maken. Wij geloven dat je
		een houvast, God, nodig hebt.
	</div>';
}

function oweeLinks(){
	echo '
<a class="meerLink" href="studentenleven.php">&raquo; C.S.R. en mijn studentenleven</a><br />
<a class="meerLink" href="programma.php">&raquo; Programma bij C.S.R. in de OWee</a><br />
<a class="meerLink" href="logeren.php">&raquo; Logeren bij C.S.R. in de OWee</a><br /><br />
<a class="meerLink" href="http://csrdelft.nl">&times; Website van C.S.R.</a><br />
<a class="meerLink" href="http://owee.nl">&times; Delft OWee website</a>';
}

function oweeCredits(){
	echo '
<div class="credits">
	&copy; 2006, 2007 <a href="http://csrdelft.nl">C.S.R.-Delft</a> |
	Oude Delft 9 |
	Delft |
	015-2135681<br>
	Techniek: <a href="http://csrdelft.nl/groepen/commissie/PubCie.html">PubCie</a>, Jieter, Maarten | 
	Grafisch: <a href="http://csrdelft.nl/groepen/commissie/OWeeCie.html">OWeeCie</a>, Willem Jan, Karin
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
	<table> 
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
