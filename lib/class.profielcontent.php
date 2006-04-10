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

	### public ###

	function ProfielContent (&$lid, &$state) {
		$this->_lid =& $lid;
		$this->_state =& $state;
	}

	function view() {
		# 
		$profiel = $this->_lid->getTmpProfile();

		switch($this->_state->getMyState()) {
			case 'none':

				$profhtml = array();
				foreach($profiel as $key => $value) $profhtml[$key] = mb_htmlentities($value);
				$profhtml['fullname'] = mb_htmlentities(str_replace('  ', ' ',implode(' ',array($profiel['voornaam'],$profiel['tussenvoegsel'],$profiel['achternaam']))));
				
				$profhtml['website_kort'] = $profhtml['website'];
				if (mb_strlen($profhtml['website_kort']) > 25) {
					$profhtml['website_kort'] = substr($profhtml['website_kort'], 0, 25) . '...';
				}
				
				
				# leden-foto
				if (file_exists( HTDOCS_PATH.'leden/pasfotos/'.$profiel['uid'].'.gif'))
					$foto = '<img src="/leden/pasfotos/'.$profiel['uid'].'.gif" />';
				else $foto = 'Geen foto aanwezig. <br />Mail de pubcie om <br />er een toe te voegen.';
				
				//soccie saldo
				//koppeltabel klopt nog niet, dus ff uitgezet.
				if($profiel['uid']==$this->_lid->getUid()){
					$sSaldo=$this->_lid->getSaldo();
					if($sSaldo!==false){
						if($sSaldo<0){
							$sSaldo='SocCie-saldo: &euro; <span class="bodyrood">'.sprintf ("%01.2f",$sSaldo).'</span>
								<script language="Javascript">
									document.bgColor=\'red\'
								</script>';
						}else{
							$sSaldo='SocCie-saldo: &euro; '.sprintf ("%01.2f",$sSaldo);
						}
					}else{
						$sSaldo='';
					}
				}else{
					$sSaldo='';
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
<td class="lijnhoktitel">Identiteit</td>
<td class="lijnhoktitel">Adres</td>
<td class="lijnhoktitel">Email/Telefoon</td>
</tr>
<tr>
<td class="lijnhoktekst" valign="top">
Naam: {$profhtml['fullname']}<br />
Lid-nummer: {$profhtml['uid']}<br />
Bijnaam: {$profhtml['nickname']}
</td>
<td class="lijnhoktekst" valign="top">
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
<td class="lijnhoktitel">Ouders</td>
<td class="lijnhoktitel">Overig</td>
</tr>
<tr>
<td class="lijnhoktekst" valign="top">
Studie: {$profhtml['studie']}<br />
Studie sinds: {$profhtml['studiejaar']}<br />
Lid sinds: {$profhtml['lidjaar']}<br />
Geboortedatum: {$profhtml['gebdag']}-{$profhtml['gebmnd']}-{$profhtml['gebjaar']}<br />
Kring: {$profhtml['moot']}.{$profhtml['kring']}
</td>
<td class="lijnhoktekst" valign="top">
{$profhtml['o_adres']}<br />
{$profhtml['o_postcode']} {$profhtml['o_woonplaats']}<br />
{$profhtml['o_land']}<br />
{$profhtml['o_telefoon']}
</td>
<td class="lijnhoktekst" valign="top">
ICQ: {$profhtml['icq']}<br />
MSN: {$profhtml['msn']}<br />
Skype: {$profhtml['skype']}<br />
Website: <a href="{$profhtml['website']}" target="_blank">{$profhtml['website_kort']}</a><br />
Eetwens: {$profhtml['eetwens']}<br />
{$sSaldo}
</td>
</tr>
</table>


<br clear="all">
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
Automatiche syncrhonisatie met de LDAP ledenlijst is nog niet geimplementeerd.<br />
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

				$form[0]['adres'] = array('input',"Adres:");
				$form[0]['postcode'] = array('input',"Postcode:");
				$form[0]['woonplaats'] = array('input',"Woonplaats:");
				$form[0]['land'] = array('input',"Land:");
				$form[0]['telefoon'] = array('input',"Telefoon:");
				$form[0]['mobiel'] = array('input',"Pauper:");
				$form[0]['email'] = array('input',"Email:");
				$form[0]['icq'] = array('input',"ICQ:");
				$form[0]['msn'] = array('input',"MSN:");
				$form[0]['skype'] = array('input',"Skype:");
				$form[0]['website'] = array('input',"Website:");

				$form[0][] = array('ztekst',"&nbsp;","Weergave van namen op het Forum<br />(dit is wat je zelf ziet, niet wat anderen zien!):");
				$form[0]['forum_name'] = array('select', "Forum:", array('civitas' => 'Toon Am. / Ama.','nick' => 'Toon bijnamen'));

				$form[1]['o_adres'] = array('input',"Adres Ouders:");
				$form[1]['o_postcode'] = array('input',"Postcode Ouders:");
				$form[1]['o_woonplaats'] = array('input',"Woonplaats Ouders:");
				$form[1]['o_land'] = array('input',"Land Ouders:");
				$form[1]['o_telefoon'] = array('input',"Telefoon Ouders:");

				$form[1][] = array('ztekst',"&nbsp;","Vaste eetgewoontes (vego etc):");
				$form[1]['eetwens'] = array('input',"Eetwens: (max 20 tekens)");
				$form[1][] = array('ztekst',"&nbsp;","Deze bijnaam kunt ook ook gebruiken voor het inloggen:");
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
								$field_usr = mb_htmlentities($profiel[$field]);
								print(<<<EOT
<tr>
<td>{$fieldinfo[1]}</td>
<td><input type="text" name="frmdata[{$field}]" class="tekst" style="width:260px;" value="{$field_usr}"></td>
</tr>
EOT
								);
								break;
							case 'ztekst':
								print("<tr>\n<td>{$fieldinfo[1]}</td>\n<td>{$fieldinfo[2]}</td>\n</tr>\n");
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
<span class="tekstrood">N.B. Het opslaan van wijzigigen is op het moment kapot.</span><br />
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
