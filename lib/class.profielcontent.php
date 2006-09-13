<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.profielcontent.php
# -------------------------------------------------------------------
#
# Bekijken en wijzigen van een ledenprofiel
#
# -------------------------------------------------------------------
# Historie:
# 09-09-2005 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.commissie.php');

class ProfielContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_state;
	var $_woonoord;
	var $_commissie;

	### public ###

	function ProfielContent (&$lid, &$state, &$woonoord, &$commissie) {
		$this->_lid =& $lid;
		$this->_state =& $state;
		$this->_woonoord =& $woonoord;
		$this->_commissie =& $commissie;
	}

	function view() {
		# 
		$profiel = $this->_lid->getTmpProfile();
		#print_r($profiel);

		switch($this->_state->getMyState()) {
			case 'none':

				$profhtml = array();
				foreach($profiel as $key => $value) $profhtml[$key] = mb_htmlentities($value);
				$profhtml['fullname'] = naam($profiel['voornaam'], $profiel['achternaam'], $profiel['tussenvoegsel']);
				
				$profhtml['website_kort'] = $profhtml['website'];
				if (mb_strlen($profhtml['website_kort']) > 25) {
					$profhtml['website_kort'] = substr($profhtml['website_kort'], 0, 25) . '...';
				}
				
				# email-adres
				if ($profhtml['email'] != '') $profhtml['email'] = sprintf('<a href="mailto:%s">%s</a>', $profhtml['email'], $profhtml['email']);
				
				# leden-foto, mag gif of jpg zijn.
				if (file_exists( HTDOCS_PATH.'/leden/pasfotos/'.$profiel['uid'].'.gif')){
					$foto = '<img src="/leden/pasfotos/'.$profiel['uid'].'.gif" />';
				}elseif(file_exists( HTDOCS_PATH.'/leden/pasfotos/'.$profiel['uid'].'.jpg')){
					$foto = '<img src="/leden/pasfotos/'.$profiel['uid'].'.jpg" />';
				}elseif($profhtml['status']=='S_NOVIET'){
					$aSjaars=array('pino.png', 'oscar.png', 'elmo.png');
					$foto = '<img src="/leden/pasfotos/'.$aSjaars[rand(0, count($aSjaars)-1)].'" 
						alt="Eerstejaars moet gaan slapen, eerstejaars moet naar bed" />';
				}else{ $foto = 'Geen foto aanwezig. <br />Mail de pubcie om <br />er een toe te voegen.'; }
				
				//soccie saldo
				$sSaldo='';
				//alleen als men het eigen profiel bekijkt.
				if($profiel['uid']==$this->_lid->getUid()){
					$sSaldo=$this->_lid->getSaldo();
					if($sSaldo!==false){
						if($sSaldo<0){
							$sSaldo='SocCie-saldo: &euro; <span class="bodyrood">'.sprintf ("%01.2f",$sSaldo).'</span>';
						}else{
							$sSaldo='SocCie-saldo: &euro; '.sprintf ("%01.2f",$sSaldo);
						}
					}
				}
				
				# kijken of deze persoon nog in een geregistreerd woonoord woont...
				$woonoord = $this->_woonoord->getWoonoordByUid($profiel['uid']);
				$woonoordhtml = ($woonoord !== false) ? "<i>" . $woonoord['naam'] . "</i><br />\n" : "";
				
				# kijken of deze persoon commissielid is
				$ciehtml = "";				
				$cies = $this->_commissie->getCieByUid($profiel['uid']);
				if (count($cies) != 0) {
					foreach ($cies as $cie) {
						$ciehtml .= 'Commissie: <a href="/informatie/commissie/'.
							mb_htmlentities($cie['naam']) . '.html">' .
							mb_htmlentities($cie['naam']) . "</a><br />\n";
					}				
				}
				
				print(<<<EOT
<center>
<span class="kopje2">Profiel van {$profhtml['fullname']}</span>
<p>
<table align="center" class="lijnhoktable" border="1" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">
<tr>
<td class="lijnhoktekst" rowspan="4">
{$foto}
</td>
<td class="lijnhoktitel" width="33%">Identiteit</td>
<td class="lijnhoktitel" width="33%">Adres</td>
<td class="lijnhoktitel" width="33%"	>Email/Telefoon</td>
</tr>
<tr>
<td class="lijnhoktekst" valign="top">
Naam: {$profhtml['fullname']}<br />
Lid-nummer: {$profhtml['uid']}<br />
Bijnaam: {$profhtml['nickname']}
</td>
<td class="lijnhoktekst" valign="top">
{$woonoordhtml}
{$profhtml['adres']}<br />
{$profhtml['postcode']} {$profhtml['woonplaats']}<br />
{$profhtml['land']}
</td>
<td class="lijnhoktekst" valign="top">
E-mail: {$profhtml['email']}<br />
Telefoon: {$profhtml['telefoon']}<br />
Pauper: {$profhtml['mobiel']}
</td>
</tr>
<tr>
<td class="lijnhoktitel">Studie/Lidmaatschap</td>
<td class="lijnhoktitel">
EOT
				);

				if ($profiel['status'] == 'S_OUDLID') print 'Functie/Beroep';
				else print 'Ouders';
				
				print(<<<EOT
</td>
<td class="lijnhoktitel">Overig</td>
</tr>
<tr>
<td class="lijnhoktekst" valign="top">
Studie: {$profhtml['studie']}<br />
Studie sinds: {$profhtml['studiejaar']}<br />
Lid sinds: {$profhtml['lidjaar']}<br />
Geboortedatum: {$profhtml['gebdag']}-{$profhtml['gebmnd']}-{$profhtml['gebjaar']}<br />
EOT
				);

				if ($profiel['status'] != 'S_OUDLID') print (<<<EOT
Kring: {$profhtml['moot']}.{$profhtml['kring']}<br />
{$ciehtml}
EOT
				);
				
				print(<<<EOT
</td>
<td class="lijnhoktekst" valign="top">
EOT
				);

				if ($profiel['status'] != 'S_OUDLID') print (<<<EOT
{$profhtml['o_adres']}<br />
{$profhtml['o_postcode']} {$profhtml['o_woonplaats']}<br />
{$profhtml['o_land']}<br />
{$profhtml['o_telefoon']}
EOT
					);
				else print ($profhtml['beroep']);
				
				print(<<<EOT
</td>
<td class="lijnhoktekst" valign="top">
ICQ: {$profhtml['icq']}<br />
MSN: {$profhtml['msn']}<br />
Jabber: {$profhtml['jid']}<br />
Skype: {$profhtml['skype']}<br />
Website: <a href="{$profhtml['website']}" target="_blank">{$profhtml['website_kort']}</a><br />
Eetwens: {$profhtml['eetwens']}<br />
{$sSaldo}
</td>
</tr>
</table>


<br />
EOT

				);
	
				# gaan we een linkje afbeelden naar de edit-functie, of de editvakken?
				if ( ($this->_lid->hasPermission('P_PROFIEL_EDIT') and $profiel['uid'] == $this->_lid->getUid()) or ($this->_lid->hasPermission('P_LEDEN_EDIT')) ) {
?>
<a href="<?=$this->_state->getMyUrl(true) . 'a=edit'?>">[ Bewerken ]</a>
<?php
				}
				#if (isset($_SERVER["HTTP_REFERER"]) and strpos($_SERVER["HTTP_REFERER"],"leden/lijst.php") !== false) {
?>
<a href="javascript: history.go(-1)">[ Terug ]</a>
<?php
				#}
if($this->_lid->hasPermission('P_ADMIN')){
	echo '<a href="/tools/stats.php?uid='.$profiel['uid'].'">[ overzicht van bezoeken ]</a>';
}

?>

</center>
<?php
	
				break;
			case 'edit':
				print(<<<EOT
<center><span class="kopje2">Profiel wijzigen</span></center>
<br />

Hieronder kunt u uw eigen gegevens wijzigen. Voor enkele velden is het niet mogelijk zelf wijzigingen door te voeren.
Voor de meeste velden geldt daarnaast dat de ingevulde gegevens een geldig formaat moeten hebben.
Mochten er fouten in het gedeelte van uw profiel staan, dat u niet zelf kunt wijzigen, meld het dan bij de Vice-Abactis.
<br />
<br />
Als er <span class="tekstrood">tekst in rode letters</span> wordt afgebeeld bij een veld, dan betekent dat dat de invoer
niet geaccepteerd is, en dat u die zal moeten aanpassen aan het gevraagde formaat. Een aantal velden kan leeg gelaten
worden als er geen zinvolle informatie voor is.
<p>
EOT
				);
				
				#
				# NB!! Op de tekst die hieronder vast wordt ingesteld wordt geen htmlentities ofzo gedaan
				#

				$form[0][] = array('ztekst',"&nbsp;","<b>Identiteit</b>");

				if ($profiel['status'] == 'S_OUDLID' or $this->_lid->hasPermission('P_LEDEN_MOD')) {
					$form[0]['voornaam'] = array('input',"Voornaam:");
					$form[0]['tussenvoegsel'] = array('input',"Tussenv.:");
					$form[0]['achternaam'] = array('input',"Achternaam:");
				}
				if ($this->_lid->hasPermission('P_LEDEN_MOD')) {
					$form[0]['voornamen'] = array('input',"Voornamen:");
					$form[0]['postfix'] = array('input',"Postfix:");
					$form[0]['geslacht'] = array('select', "Geslacht:", array('m' => 'Man','v' => 'Vrouw'));
				}

				$form[0]['adres'] = array('input',"Adres:");
				$form[0]['postcode'] = array('input',"Postcode:");
				$form[0]['woonplaats'] = array('input',"Woonplaats:");
				$form[0]['land'] = array('input',"Land:");

				if ($profiel['status'] == 'S_OUDLID' or $this->_lid->hasPermission('P_LEDEN_MOD')) {
					$gebdatum = implode('-',array($profiel['gebdag'],$profiel['gebmnd'],$profiel['gebjaar']));
					$form[0][] = array('ztekst',"&nbsp;","Gebruik het formaat dd-mm-YYYY");
					$form[0]['gebdatum'] = array('input',"Geb.datum:",$gebdatum);
				}				

				$form[0][] = array('ztekst',"&nbsp;","<b>Email/Telefoon</b>");
				$form[0]['telefoon'] = array('input',"Telefoon:");
				$form[0]['mobiel'] = array('input',"Pauper:");
				$form[0]['email'] = array('input',"Email:");

				$form[0][] = array('ztekst',"&nbsp;","<b>Diversen</b>");
				$form[0]['icq'] = array('input',"ICQ:");
				$form[0]['msn'] = array('input',"MSN:");
				$form[0]['jid'] = array('input',"Jabber:");
				$form[0]['skype'] = array('input',"Skype:");
				$form[0]['website'] = array('input',"Website:");

				$form[0][] = array('ztekst',"&nbsp;","Weergave van namen op het Forum<br />(dit is wat je zelf ziet, niet wat anderen zien!):");
				$form[0]['forum_name'] = array('select', "Forum:", array('civitas' => 'Toon Am. / Ama.','nick' => 'Toon bijnamen'));

				if ($this->_lid->hasPermission('P_LEDEN_MOD')) {
					$form[0]['kerk'] = array('input',"Kerk:");
					$form[0]['muziek'] = array('input',"Muziek:");
				}

				if ($profiel['status'] != 'S_OUDLID' or $this->_lid->hasPermission('P_LEDEN_MOD')) {
					$form[1][] = array('ztekst',"&nbsp;","<b>Ouders</b>");
					$form[1]['o_adres'] = array('input',"Adres Ouders:");
					$form[1]['o_postcode'] = array('input',"Postcode Ouders:");
					$form[1]['o_woonplaats'] = array('input',"Woonplaats Ouders:");
					$form[1]['o_land'] = array('input',"Land Ouders:");
					$form[1]['o_telefoon'] = array('input',"Telefoon Ouders:");
					$form[1][] = array('ztekst',"&nbsp;","<b>Diversen:</b>");
					$form[1][] = array('ztekst',"&nbsp;","Vaste eetgewoontes (vego etc):");
					$form[1]['eetwens'] = array('input',"Eetwens: (max 20 tekens)");
				}

				if ($profiel['status'] == 'S_OUDLID' or $this->_lid->hasPermission('P_LEDEN_MOD')) {
					$form[1][] = array('ztekst',"&nbsp;","<b>Studie/Lidm./Werk</b>");
					$form[1]['studie'] = array('input',"Studie:");
					$form[1]['studiejaar'] = array('input',"Beginjaar studie:");
					$form[1]['lidjaar'] = array('input',"Lid sinds:");
					$form[1]['beroep'] = array('textarea',"Functie/Beroep:",10);
				}
				if ($this->_lid->hasPermission('P_LEDEN_MOD')) {
					$form[1]['moot'] = array('select', "Moot:", range(0,4));
					$form[1]['kring'] = array('select', "Kring:", range(0,10));
					$form[1]['kringleider'] = array('select', "Kringleider:", array('n' => 'Nee','o' => 'Ouderejaarskring','e' => 'Eerstejaarskring'));
					$form[1]['motebal'] = array('select', "Motebal:", array('0' => 'Nee','1' => 'Ja'));
				}

				$form[1][] = array('ztekst',"&nbsp;","<b>Inloggegevens</b>");
				$form[1][] = array('ztekst',"&nbsp;","Deze bijnaam kunt ook gebruiken voor het inloggen:");
				$form[1]['nickname'] = array('input',"Bijnaam:");
				$form[1][] = array('ztekst',"&nbsp;","Wachtwoord wijzigen (optioneel):");
				$form[1]['oldpass'] = array('password',"Oude wachtwoord:");
				$form[1]['nwpass'] = array('password',"Nieuwe wachtwoord:");
				$form[1]['nwpass2'] = array('password',"Nieuwe wachtwoord:");
				
				# evt. foutmeldingen ophalen
				$formerror = $this->_lid->getFormErrors();
				$myurl = $this->_state->getMyUrl();
				
				print(<<<EOT
<form name="frmcontent" action="{$myurl}" method="POST">
<input type="hidden" name="a" value="save">

<table align="center" class="tekst" border="0" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">
<tr>

EOT
				);
				foreach ($form as $formkolom) {
					print(<<<EOT
<td valign="top">
<table align="center" class="tekst" border="0" cellspacing="2" cellpadding="0" marginheight="0" marginwidth="0">

EOT
					);

					foreach ($formkolom as $field => $fieldinfo) {
						if (isset($formerror[$field])) {
							print(<<<EOT
<tr>
<td>&nbsp;</td>
<td class="tekstrood">{$formerror[$field]}</td>
</tr>

EOT
							);
						}
						
						switch ($fieldinfo[0]) {
							case 'input':
								# is de inhoud van het vak al meegegeven?
								if (isset($fieldinfo[2])) $field_usr = mb_htmlentities($fieldinfo[2]);
								else $field_usr = mb_htmlentities($profiel[$field]);
								print(<<<EOT
<tr>
<td>{$fieldinfo[1]}</td>
<td><input type="text" name="frmdata[{$field}]" class="tekst" style="width:260px;" value="{$field_usr}"></td>
</tr>

EOT
								);
								break;
							case 'textarea':
								$field_usr = mb_htmlentities($profiel[$field]);
								print(<<<EOT
<tr>
<td valign="top">{$fieldinfo[1]}</td>
<td><textarea name="frmdata[{$field}]" rows="{$fieldinfo[2]}" style="width:260px" class="tekst">{$field_usr}</textarea>
</td>
</tr>

EOT
								);
								break;
							case 'ztekst':
								print("<tr><td>{$fieldinfo[1]}</td><td>{$fieldinfo[2]}</td></tr>\n");
								break;
							case 'password':
								print(<<<EOT
<tr>
<td>{$fieldinfo[1]}</td>
<td><input type="password" name="frmdata[{$field}]" class="tekst" style="width:260px;" value=""></td>
</tr>

EOT
								);
								break;
							case 'select':
								print("<tr>\n<td>\n{$fieldinfo[1]}\n</td>\n");
								print("<td>\n<select name=\"frmdata[{$field}]\" class=\"tekst\">\n");
								foreach ($fieldinfo[2] as $key => $value) {
									$selected = ($profiel[$field] == $key) ? ' selected' : '';
									printf("<option value=\"%s\" %s>%s</option>\n", $key, $selected, $value);
								}
								print("</select>\n</td>\n</tr>\n");
								break;
						}
					}
					print("</table>\n</td>");
				}
				print(<<<EOT
</tr>
</table>
<br clear="all">
<center>
<input type="image" src="/images/wijzigingen_opslaan.gif" width=106 height=12 alt="Wijzigingen opslaan" name="foo" value="bar">
<a href="{$myurl}"><img src="/images/annuleren.gif" width=69 height=12 alt="Annuleren" border="0"></a>
</center>
</form>

EOT
				);
				break;
		}
	}
}

?>
