<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.woonoordcontent.php
# -------------------------------------------------------------------
#
# Beeldt informatie af over Woonoorden
#
# -------------------------------------------------------------------
# Historie:
# 28-08-2005 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('bbcode/include.bbcode.php');
require_once ('class.woonoord.php');

class WoonoordContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_woonoord;
	var $_lid;

	### public ###

	function WoonoordContent (&$woonoord, &$lid) {
		$this->_woonoord =& $woonoord;
		$this->_lid=& $lid;
	}

	function view() {

    	# namen-sorteerfunctie
		function cmp($a, $b) { return strcmp($a['achternaam'], $b['achternaam']); }

?>
<center><span class="kopje2">Woonoorden</span></center><p>

Veel leden van C.S.R. wonen in verenigings-woonoorden. Als een woonoord aan
bepaalde eisen voldoet, kan het een offici&euml;le status als C.S.R. huis krijgen.
Daarnaast zijn er kotten en overige woonoorden.<br />
<br />
Am. Talstra over C.S.R.-huizen in 'Veertig Roem, lustrumalmanak 2001':<br />
<em>"In 1990 werd de titel "C.S.R.-huis" officieel ingevoerd, hoewel er natuurlijk al veel langer verdiepingen 
of huizen bestonden die geheel of gedeeltelijk door C.S.R.-leden werden bewoond. 
<?php
if ($this->_lid->hasPermission('P_LEDEN_READ')){
echo "Het net geopende en meest gewaardeerde huis Studenten Sanatorium Sonnenvanck had op zijn openingsfeest in 
	1989 een certificaat ontvangen dat de benoeming tot C.S.R.-huis vermeldde. De bewoners probeerden vervolgens 
	door middel van een motie op de H.V. van 5 februari 1990 bescherming voor de titel C.S.R.-huis te regelen, en niet zonder succes.";
}
?>
 Tijdens een extra H.V. enkele dagen later werden de voorwaarden vastgesteld: in een C.S.R.-huis diende tenminste 75% van de 
minimaal drie bewoners lid van C.S.R. te zijn, en een C.S.R.-kot bestond uit minimaal twee bewoners waarvan 
tenminste 50% C.S.R.-lid was.
<?php
if ($this->_lid->hasPermission('P_LEDEN_READ')){
	echo 'Bovendien werd als specifieke eis gesteld dat de bewoners van kotten niet geabonneerd mochten zijn op de Penthouse. ';
}
?>
Erkende huizen nodigden het bestuur uit voor een maaltijd en ontvingen hierbij een 
certificaat en een Kaapsviooltje. Bovendien waren zij verplicht een open activiteit voor de hele vereniging te organiseren.
</em>
<br />
<br />

<table width="100%" border="0" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">

<?php
		$soorten = array('huis' => 'C.S.R. Huizen', 'kot' => 'C.S.R. Kotten', 'overig' => 'Overige Woonoorden');
		foreach ($soorten as $soort => $titel) {
?>
<tr>
<td width="50%"><hr><span class="kopje2"><?=$titel?></span><hr></td>
<td width="2%">&nbsp;</td>
<td width="47%"><hr><span class="kopje2">Bewoners</span><hr></td>
</tr>
<?php
			$woonoorden = $this->_woonoord->getAll($soort);
			foreach($woonoorden as $woonoord) {
?>
<tr height="30">
<td colspan="3" valign="middle"><? if ($woonoord['link'] == '') { ?><span class="kopje3"><?=mb_htmlentities($woonoord['naam'])?></span><? }
else { ?><a href="<?=htmlspecialchars($woonoord['link'])?>" class="a3"><?=mb_htmlentities($woonoord['naam'])?></a><? } ?> (<?=htmlspecialchars($woonoord['adres'])?>)</td>
</tr>
<tr>
<td valign="top"><? if ($woonoord['plaatje'] != '') { ?><img src="<?php echo htmlspecialchars($woonoord['plaatje']) ?>" align="right"><?php } ?><?=mb_htmlentities($woonoord['tekst'])?></td>
<td>&nbsp;</td>
<td valign="top">
<?php
				$bewoners = $this->_woonoord->getBewoners($woonoord['id']);
				usort($bewoners, 'cmp');
				foreach ($bewoners as $bewoner) {
					echo mb_htmlentities($bewoner['voornaam']);
					if ($bewoner['tussenvoegsel'] != "") echo " ".mb_htmlentities($bewoner['tussenvoegsel']);
					echo " ".mb_htmlentities($bewoner['achternaam']) . "<br />\n";
				}
?>
</td>
</tr>
<?php
			}
		}
?>
<tr>
<td><hr></td>
<td>&nbsp;</td>
<td><hr></td>
</tr>
</table>

<br clear="all">

<?php
	}
}

?>
