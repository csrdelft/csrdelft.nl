<div class="kopje2"><center>Lid worden</center></div>

Je kunt lid worden van C.S.R. als je een universitaire of HBO-opleiding volgt.
Naast deze voorwaarde geldt dat je de grondslag van de vereniging
onderschrijft. Dit doe je door de apostolische Geloofsbelijdenis te
ondertekenen. Tijdens het eerste halfjaar wordt deze geloofsbelijdenis
uitvoerig besproken op kring, zodat je weet waar je voor kiest.

<p>

<table border=0 cellspacing=5 cellpadding=0 marginheight=0 marginwidth=0>
<tr>

<td align="center">
<img src="/informatie/images/owee.jpg" width="133" height="100"><br clear="all">
OWee op C.S.R.
</td>

<td>
Heb je belangstelling, kom dan langs in de OWee, één van de laatste weken van
de zomervakantie om je in te schrijven. Als je je inschrijft, ga je mee op
startkamp. Daar kun je kennis maken met je jaargenoten en met ouderejaars
C.S.R.-leden. Je blijft een half jaar adspirant-lid. In die tijd maak je kennis
met alle facetten van de vereniging voor je besluit lid te worden. In deze tijd
wordt je begeleid en ingeleid in het verenigingsleven door de
NoviciaatsCommissie. Na dit halfjaar wordt je geïnstalleerd als lid van de
vereniging.
</td>

</tr>
</table>

<p>

Buiten de OWee om kun je terecht bij het bestuur voor informatie, ook over
gastlidmaatschap en het volgen van kringen zonder lid te worden. Wil je meer
informatie, of weet je nu al zeker dat je lid wilt worden? Vul dan onderstaand
formulier in!

<?php

$action = getVar('a', 'none');
if (getVar('a', 'none') != 'ok') {

?>

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

<center>
<form method="post" name="frm_lidworden" onsubmit="return requiredFields();" action="/tools/sendmail/sendmail.php">
<input type="hidden" name="form_id" VALUE="lidworden">

<table border=0 cellspacing=2 cellpadding=0 marginheight=0 marginwidth=0>

<tr><td align="right">Je naam:</td><td><input type="text" name="naam"></td></tr>
<tr><td align="right">Straat + huisnummer:</td><td><input type="text" name="straat"></td></tr>
<tr><td align="right">Postcode:</td><td><input type="text" name="postcode"></td></tr>
<tr><td align="right">Woonplaats:</td><td><input type="text" name="plaats"></td></tr>
<tr><td align="right">Email:</td><td><input type="text" name="submit_by"></td></tr>
<tr><td align="right">Ik wil alleen meer informatie:</td><td><input type="radio" name="ik_wil_graag" value="alleen_informatie" checked></td></tr>
<tr><td align="right">Ik wil graag lid worden:</td><td><input type="radio" name="ik_wil_graag" value="lid_worden"></td></tr>
<tr><td align="center" colspan=2><input type="submit" value="Verzenden"></td></tr>

</table>

</form>
</center>

<?php } else { ?>

<center><div class="h3rood">Bedankt voor je interesse!</div></center>
<p>
Je berichtje is doorgegeven aan de Vice-Praeses van het bestuur. Bij deze
persoon kun je altijd terecht met vragen over kennismaken met C.S.R.
Is het inmiddels zomer of begin augustus, zorg dan dat je zeker in de OWee,
de Delftse OntvangstWeek langskomt op C.S.R. Meer informatie hierover is
tegen die tijd altijd ergens te vinden op deze website, of vraag de ViP
(vice-praeses<img src="/pics/at.gif" width=8 height=9>csrdelft.nl) of de
OWeeCommissie (oweecie<img src="/pics/at.gif" width=8 height=9>csrdelft.nl).

<?php } ?>
