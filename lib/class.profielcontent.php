<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.profielcontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van een ledenprofiel
# -------------------------------------------------------------------


require_once ('class.commissie.php');

class ProfielContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_state;
	
	//array met profiel.
	var $_profiel;
	### public ###
	
	//LET OP: hier wordt lid wél meegegeven, want het gaat hier om een profiel-object.
	function ProfielContent ($profiel, &$state) {
		$this->_state =& $state;
		$this->_lid=$profiel;
		
		$this->_profiel = $this->_lid->getTmpProfile();
	}
	function getTitel(){
		return 'Het profiel van '.naam($this->_profiel['voornaam'], $this->_profiel['achternaam'], $this->_profiel['tussenvoegsel']);
	}
	function viewWaarbenik(){
		echo '<a href="/intern/">Intern</a> &raquo; <a href="/leden/lijst.php">Ledenlijst</a> &raquo; ';
		echo 'profiel van '.naam($this->_profiel['voornaam'], $this->_profiel['achternaam'], $this->_profiel['tussenvoegsel']);

	}
	function viewStateNone(){
		$profhtml = array();
		foreach($this->_profiel as $key => $value){
			$profhtml[$key] = mb_htmlentities($value);
		}
		$profhtml['fullname']=$this->_lid->getFullName($this->_profiel['uid']);
		$profhtml['civitasnaam']=$this->_lid->getNaamLink($this->_profiel['uid'], 'civitas', false);
				
		# email-adres
		if($profhtml['email'] != ''){ 
			$profhtml['email'] = sprintf('<a href="mailto:%s">%s</a>', $profhtml['email'], $profhtml['email']);
		}
		
		$profhtml['foto']=$this->_lid->getPasfoto($this->_profiel['uid']);
			
		//soccie saldo
		$profhtml['saldi']='';
		//alleen als men het eigen profiel bekijkt.
		if($this->_profiel['uid']==$this->_lid->getUid()){
			$aSaldi=$this->_lid->getSaldi();
			//zijn er uberhaupt wel saldi...
			if($aSaldi!==false){
				if($aSaldi['soccieSaldo']<0){
					$profhtml['saldi'].='SocCie-saldo: &euro; <span class="waarschuwing">'.sprintf ("%01.2f",$aSaldi['soccieSaldo']).'</span><br />';
				}else{
					$profhtml['saldi'].='SocCie-saldo: &euro; '.sprintf ("%01.2f",$aSaldi['soccieSaldo']).'<br />';
				}
				if($aSaldi['maalcieSaldo']<0){
					$profhtml['saldi'].='MaalCie-saldo: &euro; <span class="waarschuwing">'.sprintf ("%01.2f",$aSaldi['maalcieSaldo']).'</span>';
				}else{
					$profhtml['saldi'].='MaalCie-saldo: &euro; '.sprintf ("%01.2f",$aSaldi['maalcieSaldo']);
				}
			}
		}
		# kijken of deze persoon nog in een geregistreerd woonoord woont...
		require_once('class.woonoord.php');
		$woonoord=new Woonoord();
		$woonoord = $woonoord->getWoonoordByUid($this->_profiel['uid']);
		$profhtml['woonoord']=($woonoord !== false) ? '<em>'.$woonoord['naam'].'</i><br />' : '';
		
		# kijken of deze persoon commissielid is
		require_once('class.commissie.php');
		$commissie=new Commissie();
		$profhtml['commissies']="";				
		$aCommissies = $commissie->getCieByUid($this->_profiel['uid']);
		if (count($aCommissies) != 0) {
			foreach ($aCommissies as $cie) {
				$aCieNaam=mb_htmlentities($cie['naam']);
				$profhtml['commissies'].= 'Commissie: <a href="/groepen/commissie/'.$aCieNaam.'.html">'.$aCieNaam."</a><br />\n";
			}				
		}
		//de html template in elkaar draaien en weergeven
		$profiel=new Smarty_csr();

		
		$profiel->assign('profhtml', $profhtml);
		$profiel->assign('isOudlid', $this->_profiel['status'] == 'S_OUDLID');
		//eigen profiel niet cachen, dan krijgen we namelijk rare dingen 
		//dat we andermans saldo's zien enzo
		if($this->_profiel['uid']==$this->_lid->getUid()){
			$profiel->caching=false;
		}
		$profiel->display('profiel.tpl', $this->_profiel['uid']);
		
		# gaan we een linkje afbeelden naar de edit-functie, of de editvakken?
		if ( ($this->_lid->hasPermission('P_PROFIEL_EDIT') and $this->_profiel['uid'] == $this->_lid->getUid()) or 
			$this->_lid->hasPermission('P_LEDEN_EDIT') ){
			echo '<a href="'.$this->_state->getMyUrl(true).'/edit" class="knop">Bewerken</a> ';
		}
		if($this->_lid->hasPermission('P_ADMIN')){
			echo '<a href="/tools/stats.php?uid='.$this->_profiel['uid'].'" class="knop">overzicht van bezoeken</a> ';
			echo '<a href="/intern/profiel/'.$this->_profiel['uid'].'/wachtwoord" class="knop"
				onclick="return confirm(\'Weet u zeker dat u het wachtwoord van deze gebruiker wilt resetten?\')">reset wachtwoord</a>';
			echo '<br />'.$this->getMelding();
		}
	}
	function viewStateEdit(){
		echo '<h2>Profiel wijzigen</h2>
			Hieronder kunt u uw eigen gegevens wijzigen. Voor enkele velden is het niet mogelijk zelf 
			wijzigingen door te voeren. Voor de meeste velden geldt daarnaast dat de ingevulde gegevens 
			een geldig formaat moeten hebben. Mochten er fouten in het gedeelte van uw profiel staan, 
			dat u niet zelf kunt wijzigen, meld het dan bij de Vice-Abactis. <br /> <br />Als er 
			<span class="waarschuwing">tekst in rode letters</span> wordt afgebeeld bij een veld, dan 
			betekent dat dat de invoer niet geaccepteerd is, en dat u die zal moeten aanpassen aan het
			gevraagde formaat. Een aantal velden kan leeg gelaten worden als er geen zinvolle informatie voor is.';
				
		#
		# NB!! Op de tekst die hieronder vast wordt ingesteld wordt geen htmlentities ofzo gedaan
		#

		$form[0][] = array('ztekst',"&nbsp;","<strong>Identiteit</strong>");

		if ($this->_profiel['status'] == 'S_OUDLID' or $this->_lid->hasPermission('P_LEDEN_MOD')) {
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

		if ($this->_profiel['status'] == 'S_OUDLID' or $this->_lid->hasPermission('P_LEDEN_MOD')) {
			$gebdatum = $this->_profiel['gebdatum'];
			//echo $gebdatum; exit;
			$form[0][] = array('ztekst',"&nbsp;","Gebruik het formaat YYYY-mm-dd");
			$form[0]['gebdatum'] = array('input',"Geb.datum:");
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
		$form[0]['bankrekening'] = array('input', "Bankrekening:");
		
		$form[0][] = array('ztekst',"&nbsp;","Weergave van namen op het Forum<br />(dit is wat je zelf ziet, niet wat anderen zien!):");
		$form[0]['forum_name'] = array('select', "Forum:", array('civitas' => 'Toon Am. / Ama.','nick' => 'Toon bijnamen'));

		if ($this->_lid->hasPermission('P_LEDEN_MOD')) {
			$form[0]['kerk'] = array('input',"Kerk:");
			$form[0]['muziek'] = array('input',"Muziek:");
		}

		if ($this->_profiel['status'] != 'S_OUDLID' or $this->_lid->hasPermission('P_LEDEN_MOD')) {
			$form[1][] = array('ztekst',"&nbsp;","<b>Ouders</b>");
			$form[1]['o_adres'] = array('input',"Adres Ouders:");
			$form[1]['o_postcode'] = array('input',"Postcode Ouders:");
			$form[1]['o_woonplaats'] = array('input',"Woonplaats Ouders:");
			$form[1]['o_land'] = array('input',"Land Ouders:");
			$form[1]['o_telefoon'] = array('input',"Telefoon Ouders:");
			$form[1][] = array('ztekst',"&nbsp;","<b>Diversen:</b>");
			$form[1][] = array('ztekst',"&nbsp;","Di&euml;ten (Vego, notenallergie etc.):");
			$form[1]['eetwens'] = array('input',"Di&euml;ten: (max 20 tekens)");
		}

		if ($this->_profiel['status'] == 'S_OUDLID' or $this->_lid->hasPermission('P_LEDEN_MOD')) {
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
		$form[1][] = array('ztekst',"&nbsp;","Deze bijnaam kunt u ook gebruiken voor het inloggen:");
		$form[1]['nickname'] = array('input',"Bijnaam:");
		$form[1][] = array('ztekst',"&nbsp;","Wachtwoord wijzigen (optioneel):");
		$form[1]['oldpass'] = array('password',"Oude wachtwoord:");
		$form[1]['nwpass'] = array('password',"Nieuw wachtwoord:");
		$form[1]['nwpass2'] = array('password',"Nieuw wachtwoord:");
		
		//status veranderen...
		if ($this->_lid->hasPermission('P_LEDEN_MOD')) {
			$form[1]['status'] = array('select', 'Status:', 
				array('S_LID' => 'Lid', 'S_GASTLID' => 'Gastlid', 
					'S_KRINGEL'=>'Kringel', 'S_NOVIET'=>'Noviet', 'S_OUDLID'=>'Oudlid', 'S_NOBODY' => 'Geen lid'));
		}
		# evt. foutmeldingen ophalen
		$formerror = $this->_lid->getFormErrors();
		$myurl = $this->_state->getMyUrl();
		echo '
			<form name="frmcontent" action="'.$myurl.'" method="post">
				<input type="hidden" name="a" value="save" />
				<table>
					<tr>';
				foreach ($form as $formkolom) {
					echo '<td><table class="profiel_edit">';
					foreach ($formkolom as $field => $fieldinfo) {
						if (isset($formerror[$field])) {
							echo '<tr><td>&nbsp;</td><td class="waarschuwing">'.$formerror[$field].'</td></tr>';
						}
						//roept een methode aan die verschillende formulier-elementen kan maken.
						$this->viewFormField($field, $fieldinfo);
					}
					echo '</table></td>';
				}
				echo '</tr></table><br />
					<input type="submit" name="submit" value="Wijzigingen opslaan" />
					<a href="'.$myurl.'" class="knop">Annuleren</a>
					</form>';
	}
	function viewFormField($field, $fieldinfo){
		echo '<tr><td>'.$fieldinfo[1].'</td><td>';
		switch ($fieldinfo[0]) {
			case 'input':
				# is de inhoud van het vak al meegegeven?
				if(isset($fieldinfo[2])){ 
					$field_usr = mb_htmlentities($fieldinfo[2]);
				}else{ 
					$field_usr = mb_htmlentities($this->_profiel[$field]);
				}
				echo '<input type="text" name="frmdata['.$field.']" value="'.$field_usr.'" />';
			break;
			case 'textarea':
				$field_usr = mb_htmlentities($this->_profiel[$field]);
				echo '<textarea name="frmdata['.$field.']" rows="'.$fieldinfo[2].'">'.$field_usr.'</textarea>';
			break;
			case 'ztekst':
				echo $fieldinfo[2];
			break;
			case 'password':
				echo '<input type="password" name="frmdata['.$field.']" value="" />';
			break;
			case 'select':
				echo '<select name="frmdata['.$field.']">';
				foreach ($fieldinfo[2] as $key => $value) {
					$selected = ($this->_profiel[$field] == $key) ? ' selected="selected"' : '';
					echo '<option value="'.$key. '" '.$selected.'>'.$value.'</option>';
				}
				echo '</select>';
			break;
		}
		echo '</td></tr>'."\n";
	}
	function view() {
		switch($this->_state->getMyState()) {
			case 'none': $this->viewStateNone(); break;
			case 'edit': $this->viewStateEdit(); break;
		}
	}
}

?>
