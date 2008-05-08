<h1>Lid worden?</h1>

C.S.R. is in Delft d&eacute; vereniging waar zowel van gezelligheid als van vorming het beste te vinden is en daarmee is het een zeer belangrijk deel van het leven van de leden. Als jij een universitaire of HBO-opleiding volgt kan ook jij lid worden. Daarnaast moet je wel de grondslag van de vereniging onderschrijven. Dit doe je door de <a href="http://nl.wikipedia.org/wiki/Apostolische_geloofsbelijdenis">apostolische geloofsbelijdenis</a> te ondertekenen. Deze geloofsbelijdenis is één van de onderwerpen die tijdens het eerste halfjaar Bijbelkring aan de orde komen, zodat je weet waar je voor kiest. Tijdens het eerste jaar kring is er sowieso gelegenheid tot open en toch diepe gesprekken over allerlei onderwerpen omdat je met mensen van je eigen jaar toch een speciale band hebt.<br />
<br />
Ben je geïnteresseerd? Kom dan langs in de <a href="http://www.owee.nl">OWee</a>, de op één na laatste week van augustus, om de sfeer te proeven of je in te schrijven. Dit laatste kan tot en met de laatste dag van de Owee. Wanneer je je inschrijft maak je in de week na de Owee verder kennis met C.S.R., je jaargenoten en de rest van de leden. Je gaat onder andere een weekend weg met de vereniging. Voor je daadwerkelijk besluit lid te worden, ben je eerst een half jaar aspirant-lid. In die tijd maak je verder kennis met de vereniging.<br />
<br />
<a href="http://csrdelft.nl/vereniging/">Hier</a> kan je meer lezen over wat C.S.R. is. Wil je meer informatie dan kan je het formulier onderaan deze pagina gebruiken. We nodigen je van harte uit om eens langs te komen op een maaltijd of een borrel, als je even wat van je laat horen spreken we wat af, maar je kan natuurlijk ook lukraak op een donderdagavond langskomen. We zouden je graag ontmoeten: stuur je mail naar <a href="mailto:owee@csrdelft.nl">owee@csrdelft.nl</a> of bel even met het bestuur (015-2135681). Nóg meer contactgegevens vind je <a href="http://csrdelft.nl/contact.php">hier</a>.<br />
<br />

<?php

$action = isset($_GET['a']) ? $_GET['a'] : 'none';
if ($action != 'ok') {
	print(<<<EOT

<script type="text/javascript" language="JavaScript">
function requiredFields() {
	if (document.frm_lidworden.naam.value == "") {
		alert("Vul je naam in!");
		document.frm_lidworden.naam.focus();
		return false;
	}
	if (document.frm_lidworden.submit_by.value == "") {
		alert("Vul je email-adres in!");
		document.frm_lidworden.submit_by.focus();
		return false;
	}
	return true;
}
</script>


<form method="post" name="frm_lidworden" onsubmit="return requiredFields();" action="/tools/sendmail/sendmail.php">
<input type="hidden" name="form_id" VALUE="lidworden">

<table>

<tr><td align="right">Je naam:</td><td><input type="text" class="tekst" name="naam"></td></tr>
<tr><td align="right">Straat + huisnummer:</td><td><input type="text" class="tekst" name="straat"></td></tr>
<tr><td align="right">Postcode:</td><td><input type="text" class="tekst" name="postcode"></td></tr>
<tr><td align="right">Woonplaats:</td><td><input type="text" class="tekst" name="plaats"></td></tr>
<tr><td align="right">Email:</td><td><input type="text" class="tekst" name="submit_by"></td></tr>
<tr><td align="right">opmerking:</td><td><textarea name="opmerking" rows="4" cols="20" class="tekst"></textarea></td></tr>
<tr><td align="center" colspan=2><input type="submit" class="tekst" value=" Verzenden "></td></tr>

</table>

</form>

EOT
	);
} else {
	print(<<<EOT

<center><div class="waarschuwing">Bedankt voor je interesse!</div></center>
<p>
Je berichtje is doorgegeven aan de Vice-Praeses van het bestuur. Bij deze
persoon kun je altijd terecht met vragen over kennismaken met C.S.R.
Is het inmiddels zomer of begin augustus, zorg dan dat je zeker in de OWee,
de Delftse OntvangstWeek langskomt op C.S.R. Meer informatie hierover is
tegen die tijd altijd ergens te vinden op deze website, of vraag de ViP
(vice-praeses@csrdelft.nl) of de
OWeeCommissie (oweecie@csrdelft.nl).

EOT
	);
}

?>
