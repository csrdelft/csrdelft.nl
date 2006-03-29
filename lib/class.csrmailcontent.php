<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.csrmailcontent.php
# -------------------------------------------------------------------
# Verzorgt het opvragen van eetplangegevens
# -------------------------------------------------------------------
# Historie:
# 01-10-2005 Jieter
# . gemaakt
#

require_once ('class.mysql.php');

class Csrmailcontent {
	
	var $_csrmail;
	var $_clearForm=false;
	
	function Csrmailcontent(&$csrmail){
		$this->_csrmail=$csrmail;
	}
	function geefBerichtInvoerVeld($titel, $categorie, $bericht, $sError=false, $ID=0){
		echo '<form method="post" action="csrmail.php?ID='.$ID.'" ><div class="pubciemail-form">';
		if($sError!==false){
			echo '<div class="foutmelding">'.$sError.'</div>';
		} ?>
		<strong>Titel:</strong><br />
		<input type="text" name="titel" value="<?php echo htmlspecialchars($titel); ?>" style="width: 100%;" class="tekst" /><br /><br />
		<strong>Categorie:</strong><br />
		Selecteer hier bestuur, C.S.R. of overig. Uiteraard wordt de categorie bestuur enkel voor bestuur gebruikt. Uw invoer is enkel een voorstel.
		<em>Aankondigingen over kamers te huur komen in <strong>overig</strong> terecht! C.S.R. is bedoeld voor activiteiten van C.S.R.-commissies 
		en andere verenigingsactiviteiten.</em><br />
		<select name="categorie" class="tekst" >
		<?php
			$aOpties=array(array('voorwoord', 'Voorwoord'), array('bestuur', 'Bestuur'), array('csr', 'C.S.R.'), array('overig', 'Overig'));
			if(trim($categorie)=='')
				$categorie='overig';
			foreach($aOpties as $aOptie){
				if( !($aOptie[0]=='voorwoord' AND !$this->_csrmail->_lid->hasPermission('P_MAIL_COMPOSE'))){
					if($aOptie[0]==$categorie){
						echo '<option value="'.$aOptie[0].'" selected="selected">'.$aOptie[1].'</option>';
					}else{
						echo '<option value="'.$aOptie[0].'">'.$aOptie[1].'</option>';
					}
				}
			}	?>
		</select><br /><br />
		<strong>Bericht:</strong><br />
		<textarea name="bericht" cols="80" style="width: 100%;" rows="15" class="tekst"><?php echo htmlspecialchars($bericht); ?></textarea>
		<!--<input type="submit" name="verzenden" value="opslaan" class="tekst" />--> <input type="submit" name="verzendenMeer" value="opslaan" class="tekst" />
		</div>
	</form>
	<?php
	}
	
	function geefBerichtNieuw($sError=false){
		$titel=$categorie=$bericht='';
		if(!$this->_clearForm){
			if(isset($_POST['titel']))
				$titel=trim($_POST['titel']);
			if(isset($_POST['categorie']))
				$categorie=trim($_POST['categorie']);
			if(isset($_POST['bericht']))
				$bericht=trim($_POST['bericht']);
		}
		echo '<h3>PubCie-post invoer</h3>
			Hier kunt u uw bericht(en) voor de pubCie-post achterlaten. Dit is makkelijker voor u en voor de pubCie 
			omdat u verplicht een titel moet verzinnen. Bovendien wordt u afgerekend als u C.S.R. zonder puntjes schrijft.<br />';
		if($this->_csrmail->_lid->hasPermission('P_MAIL_COMPOSE')){
			echo '<br /><a href="/leden/csrmailpreview.php">PubCie-post voorbeeld bekijken</a><br />';
		}
		$this->geefBerichtInvoerVeld($titel, $categorie, $bericht, $sError);
	}	
	function geefBerichtBewerken($sError, $iBerichtID){
		//bericht ophalen
		if($_SERVER['REQUEST_METHOD']=='POST'){
			//bewerken, maar wel de ingevoerde waarden gebruiken.
			$aBericht['titel']=$aBericht['bericht']='';
			$aBericht['cat']='csr';
			if(isset($_POST['titel']))
				$aBericht['titel']=trim($_POST['titel']);
			if(isset($_POST['categorie']))
				$aBericht['cat']=trim($_POST['categorie']);
			if(isset($_POST['bericht']))
				$aBericht['bericht']=trim($_POST['bericht']);
		}else{
			//bewerken, waarden uit de database.
			$aBericht=$this->_csrmail->getBerichtVoorGebruiker($iBerichtID);
		}
		if(is_array($aBericht)){
			//bericht daadwerkelijk in het formulier rossen
			$this->geefBerichtInvoerVeld(stripslashes($aBericht['titel']), $aBericht['cat'], stripslashes($aBericht['bericht']), $sError, $iBerichtID);
		}else{
			//bericht bestaat niet of is niet van gebruiker
			echo '<h3>Helaas</h3>U mag dit bericht niet bewerken, omdat het niet bestaat of niet van u is.';
		}
	}
	function toonBerichten(){
		$aBerichten=$this->_csrmail->getBerichtenVoorGebruiker();
		echo '<h3>Overzicht van door u geplaatste berichten:</h3>';
		if(is_array($aBerichten)){
			echo '<dl>';
			foreach($aBerichten as $aBericht){
				kapStringNetjesAf($aBericht['bericht'], 3000);
				echo '<dt><em>'.$aBericht['datumTijd'].':</em> <u>'.str_replace('csr', 'C.S.R.', $aBericht['cat']).'</u> <strong>'.$aBericht['titel'].'</strong> 
					[ <a href="csrmail.php?ID='.$aBericht['ID'].'&amp;bewerken">bewerken</a> | <a href="csrmail.php?ID='.$aBericht['ID'].'&amp;verwijder">verwijderen</a> ]</dt>';
				echo '<dd>'.$this->process($aBericht['bericht']).'</dd>';
			}
		}else{
			echo 'U heeft nog geen berichten geplaatst in deze pubcie-mail;';
		}
	}
	function process($sString){
		$sString=stripslashes($sString);
		$sString=htmlentities($sString, ENT_COMPAT, 'UTF-8');
		$sString=trim($sString);
		 $aUbbCodes=array(
      array("[b]", "<strong>"),
      array("[/b]", "</strong>"),
      array("[i]", "<em>"),
      array("[/i]", "</em>"),
      array("[u]", "<span class=\"onderlijn\">"),
      array("[/u]", "</span>"));
    foreach($aUbbCodes as $ubbCode){
    	$sString=str_replace($ubbCode[0], $ubbCode[1], $sString);
 		}
		//linkjes
		$sString=eregi_replace("\\[url=([^\\[]*)\]([^\\[]*)\\[/url\\]","<a href=\"\\1\" >\\2</a>", $sString);
		$sString=nl2br($sString);
		return $sString;
	}
	function view(){
		if($_SERVER['REQUEST_METHOD']=='POST'){
			if($this->_csrmail->valideerBerichtInvoer($sError)===true){
				if((isset($_GET['ID']) AND $_GET['ID']==0)){
					//nieuw bericht invoeren
					if($this->_csrmail->addBericht($_POST['titel'], $_POST['categorie'], $_POST['bericht'] )){
						if(isset($_POST['verzendenMeer'])){
							$this->_clearForm=true;
							echo '<h3>Dank u</h3>Uw bericht is opgenomen in ons databeest, en het zal in de komende pubcie-post verschijnen.';
							$this->toonBerichten();
							$this->geefBerichtNieuw();
						}else{
							echo '<h3>Dank u</h3>Uw bericht is opgenomen in ons databeest, en het zal in de komende pubcie-post verschijnen.';
							$this->toonBerichten();
						}
					}else{
						echo '<h1>Fout</h1>Er ging iets mis met het invoeren van uw bericht. 
							Probeer opnieuw, of stuur uw bericht in een mail naar pubcie@csrdelft.nl';
					}
				}else{
					//bericht bewerken.
					if($this->_csrmail->bewerkBericht((int)$_GET['ID'], $_POST['titel'], $_POST['categorie'], $_POST['bericht'])){
						if(isset($_POST['verzendenMeer'])){
							$this->_clearForm=true;
							echo '<h3>Dank u</h3>Uw bericht is opgenomen in ons databeest, en het zal in de komende pubcie-post verschijnen.';
							$this->toonBerichten();
							$this->geefBerichtNieuw();
						}else{
							echo '<h3>Dank u</h3>Uw bericht is opgenomen in ons databeest, en het zal in de komende pubcie-post verschijnen.';
							$this->toonBerichten();
						}
					}else{
						echo '<h1>Fout</h1>Er ging iets mis met het invoeren van uw bericht. 
							Probeer opnieuw, of stuur uw bericht in een mail naar pubcie@csrdelft.nl';
					}
				}
			}else{
				if(isset($_GET['ID']) AND $_GET['ID']==0){
					$this->geefBerichtNieuw($sError);
				}else{
					$this->geefBerichtBewerken($sError, (int)$_GET['ID']);
				}
			}
		}else{
			if(isset($_GET['ID'])){
				$iBerichtID=(int)$_GET['ID'];
				if(isset($_GET['verwijder'])){
					//verwijderen
					if($this->_csrmail->verwijderBerichtVoorGebruiker($iBerichtID)){
						echo '<h3>Uw bericht is verwijderd.</h3>U kunt hieronder nog een nieuw bericht invoeren.';
					}else{
						echo '<h3>Er ging iets mis!</h3>Uw bericht is niet verwijderd. Probeer het a.u.b. nog eens.';
					}
				}
				if(isset($_GET['bewerken'])){
					//bericht bewerken.
					$this->geefBerichtBewerken(false, $iBerichtID);
				}else{
					$this->toonBerichten();
					$this->geefBerichtNieuw();
				}
			}else{
				//standaard actie
				$this->geefBerichtNieuw();
				$this->toonBerichten();
			}
		}
	}
}//einde classe
?>
