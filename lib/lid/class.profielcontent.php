<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.profielcontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van een ledenprofiel
# -------------------------------------------------------------------



class ProfielContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_state;

	//array met profiel.
	private $lid;
	private $_profiel;
	### public ###

	//LET OP: hier wordt lid wél meegegeven, want het gaat hier om een profiel-object.
	function ProfielContent ($lid) {
		$this->lid=$lid;
		$this->_profiel = $lid->getProfiel();
	}
	function getTitel(){
		$this->lid->tsMode='plain';
		return 'Het profiel van '.(string)$this->lid;
	}
	function viewWaarbenik(){
		echo '<a href="/intern/">Intern</a> &raquo; <a href="/leden/lijst.php">Ledenlijst</a> &raquo; ';
		echo 'profiel van '.$this->_lid->getFullname($this->_profiel['uid']);

	}
	function viewStateNone(){
		$profhtml = array();
		foreach($this->_profiel as $key => $value){
			$profhtml[$key] = mb_htmlentities($value);
		}

		$profhtml['fullname']=$this->lid->getNaam();


		$woonoord=$this->lid->getWoonoord();
		if($woonoord instanceof Groep){
			$profhtml['woonoord']=$groep->getLink();
		}else{
			$profhtml['woonoord']='<br />';
		}

		require_once('groepen/class.groepcontent.php');
		$profhtml['groepen']=new GroepenProfielContent($this->_profiel['uid']);

		//soccie saldo
		$profhtml['saldi']='';
		//alleen als men het eigen profiel bekijkt.
		if(LoginLid::instance()->isSelf($this->_profiel['uid'])){
			$profhtml['saldi']=$this->lid->getSaldi();
		}

		/*
		 * Saldografiek gaan we
		 * - gewoon en meteen weergeven bij het lid (niet oudlid) zelf.
		 * - niet meteen weergeven voor SocCie en pubcie, alleen op verzoek.
		 */
		if($this->_profiel['uid']=='9808' OR $this->_profiel['status']!='S_OUDLID'){
			if(LoginLid::instance()->isSelf($this->_profiel['uid'])){
				$profhtml['saldografiek']='<br /><img src="/tools/saldografiek.php?uid='.$this->_profiel['uid'].'" /><img src="/tools/saldografiek.php?maalcie&timespan=60&uid='.$this->_profiel['uid'].'" />';
			}else{
				if(LoginLid::instance()->hasPermission('P_ADMIN,groep:soccie')){
					$profhtml['saldografiek']='<br /><a  onclick="document.getElementById(\'saldoGrafiek\').innerHTML=\''.htmlspecialchars('<img src="/tools/saldografiek.php?uid='.$this->_profiel['uid'].'" />').'\'" class="knop">Saldografiek weergeven</a><br />';
					$profhtml['saldografiek'].='<br /><div id="saldoGrafiek"></div>';
				}
			}
		}

		$profhtml['abos']=array();
		require_once 'maaltijden/class.maaltrack.php';
		require_once 'maaltijden/class.maaltijd.php';
		$maaltrack=new Maaltrack();
		$profhtml['abos']=$maaltrack->getAbo($this->_profiel['uid']);
		$profhtml['recenteMaaltijden']=Maaltijd::getRecenteMaaltijden($this->_profiel['uid']);

		require_once 'forum/class.forum.php';
		$profhtml['recenteForumberichten']=Forum::getPostsVoorUid($this->_profiel['uid']);


		//de html template in elkaar draaien en weergeven
		$profiel=new Smarty_csr();

		$profiel->assign('profhtml', $profhtml);
		$profiel->assign('isOudlid', $this->_profiel['status'] == 'S_OUDLID');

		$profiel->assign('magBewerken', (LoginLid::instance()->hasPermission('P_PROFIEL_EDIT') AND LoginLid::instance()->isSelf($this->_profiel['uid'])) OR LoginLid::instance()->hasPermission('P_LEDEN_EDIT'));
		$profiel->assign('isAdmin', LoginLid::instance()->hasPermission('P_ADMIN'));
		$profiel->assign('melding', $this->getMelding());

		//eigen profiel niet cachen, dan krijgen we namelijk rare dingen
		//dat we andermans saldo's zien enzo
		if(LoginLid::instance()->isSelf($this->_profiel['uid'])){
			$profiel->caching=false;
		}
		$template='profiel.tpl';
		$profiel->display($template, $this->_profiel['uid']);
	}
	function viewStateEdit(){
		echo '<h2>Profiel wijzigen</h2>
			Hieronder kunt u uw eigen gegevens wijzigen. Voor enkele velden is het niet mogelijk zelf
			wijzigingen door te voeren. Voor de meeste velden geldt daarnaast dat de ingevulde gegevens
			een geldig formaat moeten hebben. Mochten er fouten in het gedeelte van uw profiel staan,
			dat u niet zelf kunt wijzigen, meld het dan bij de Vice-Abactis. <br /> <br />Als er
			<span class="waarschuwing">tekst in rode letters</span> wordt afgebeeld bij een veld, dan
			betekent dat dat de invoer niet geaccepteerd is, en dat u die zult moeten moeten aanpassen aan het
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
			if($this->_lid->hasPermission('P_LEDEN_MOD')){
				//Dieten passen normale leden maar aan op de maaltijdpagina.
				$form[1][] = array('ztekst',"&nbsp;","Di&euml;ten (Vego, notenallergie etc.):");
				$form[1]['eetwens'] = array('input',"Di&euml;ten: (max 20 tekens)");
			}
			$form[1]['studienr'] = array('input',"Studienummer:");
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
		switch('none'){ //$this->_state->getMyState()) {
			case 'none': $this->viewStateNone(); break;
			case 'edit': $this->viewStateEdit(); break;
		}
	}
}

?>
