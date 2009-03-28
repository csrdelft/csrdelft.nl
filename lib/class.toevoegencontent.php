<?php

class ToevoegenContent extends SimpleHTML {

	var $_toevoegen;
	var $numberoffiles;
	var $errorcodes; // TODO: hier weghalen en lokaal maken
	
	function ToevoegenContent(&$toevoegen) {
		$this->_toevoegen = &$toevoegen;
		$this->numberoffiles = 3;
		// we initialiseren $errorcodes niet, zodat de isset-methode false terug geeft
	}
	
	function getTitel() {
		return 'Documenten toevoegen';
	}
	
	function viewForm($number) {
		$maxfilesizeform = 10*1024*1024;
		$catsOptions=$this->_toevoegen->getCatsOptions();
		$showErrors=false;
		
		if(isset($this->errorcodes) && !empty($this->errorcodes)){
			$showErrors=true;
		}
	
		echo '<h1>Documenten toevoegen</h1><p>'."\n";

		// begin form
		echo '<form enctype="multipart/form-data" action="." method="POST">'."\n";
		echo '<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxfilesizeform.'">'."\n";
		
		// table
		echo '<table border="0" class="forumtabel">'."\n";
		for( $i = 1; $i < $number+1; $i++) {
			$title='';
			// geen errors geven bij lege delen van het formulier
			if($showErrors && $this->errorcodes[$i] == UPLOAD_ERR_NO_FILE){ // als het veld leeg was...
				// TODO: ook $_POST checken!!! (of die ook leeg was)
				continue;
			}
			if(!($showErrors && $this->errorcodes[$i] == UPLOAD_CUSTOM_ERR_SUCCEEDED)){ // als dit bestand niet gelukt is (of er een leeg formulier moet komen)
//				echo 'files:'."\n";
//				print_r($_FILES);
//				echo 'post:'."\n";
//				print_r($_POST);
				echo '<tr>';
				echo '<td>Bestand:</td>';
				echo '<td><input name="file'.$i.'" type="file" size="40"></td>';
				echo '</tr>'."\n";

				if(isset($_POST['title'.$i])){
					$title=mb_htmlentities($_POST['title'.$i]);
				}
				if(isset($_POST['cat'.$i])){
					$cat=(int)($_POST['cat'.$i]);
					// FIXME is het nodig datie weer de DB in moet?!
					$catsOptions=$this->_toevoegen->getCatsOptions($cat);
				}
				// TODO: vullen!
				echo '<tr>';
				echo '<td>Titel:</td>';
				echo '<td><input name="title'.$i.'" type="text" value="'.$title.'"></td>';
				echo '</tr>';
				
				echo '<tr><td>Categorie:</td>';
				echo '<td><select name="cat'.$i.'">';
				echo $catsOptions;
				echo '</select></td></tr>'."\n";
			}
			if($showErrors){
				if(empty($title)){ // als het bestand gelukt is en er dus nog geen titel uit de POST is gehaald
					$title=mb_htmlentities($_POST['title'.$i]);
				}
				echo '<tr><td colspan="2">'.$this->getErrorLine($i, $title).'</td></tr>'."\n";
			}
			echo '<tr><td colspan="2" valign="middle"><hr></td></tr>'."\n";
		}
		echo '</table>'."\n";
		echo '<span><input type="submit" value="Toevoegen" /></span>'."\n";
		echo '<span style="margin:3px;"><input type="reset" value="Leeg maken" /></span>'."\n"; // TODO: style weghalen!
		echo '<span><input type="button" value="Annuleren" onClick="window.location=\'../\'" /></span>'."\n";
		echo '</form>'."\n";
	}
		
	function getErrorLine($fileNumber, $title){
		$error = $this->errorcodes[$fileNumber];
//		$message='';
		
		/* Voor in de viewForm-functie:
		 * if( isset($this->errorcodes[0]) ) {
			$message='Error! ';
			if($this->errorcodes[0] == UPLOAD_CUSTOM_ERR_NO_FILES) {
				'Geen bestanden opgegeven.';
			} else {
				$message.='(Onbekend)';
			}
		}*/
		
		$bestandresult = 'Het document getiteld \''.$title.'\' ';
		// TODO: engelse termen weghalen
		if($error == UPLOAD_ERR_INI_SIZE ) { // 1
			$bestandresult .= 'kan niet worden ge-upload omdat de server niet zulke grote bestanden accepteert.';
		} else if ($error == UPLOAD_ERR_FORM_SIZE) { // 2 (b'vo)
			$bestandresult .= 'kan niet worden ge-upload omdat het bestand te groot is (volgens het formulier).';
		} else if ($error ==  UPLOAD_ERR_PARTIAL) { // 3
			$bestandresult .= 'is slechts gedeeltelijk ge-upload.';
		} else if($error == UPLOAD_ERR_NO_FILE) { // 4
			$bestandresult .= 'niet opgegeven.';
		} else if($error == UPLOAD_CUSTOM_ERR_SUCCEEDED ) { //20
			$bestandresult .= 'is succesvol geupload!';
		} else if($error == UPLOAD_CUSTOM_ERR_TOO_BIG) {
			$bestandresult .= 'kan niet worden ge-upload omdat het bestand te groot is (volgens het script).';
		} else if($error == UPLOAD_CUSTOM_ERR_INVALID_CHARS) {
			$bestandresult .= 'kan niet worden ge-upload omdat de bestandsnaam ongeldige tekens bevat.';
		} else if($error == UPLOAD_CUSTOM_ERR_MOVE_FAILED) {
			$bestandresult .= 'kan niet worden ge-upload omdat het verplaatsen is mislukt.';
		} else if($error == UPLOAD_CUSTOM_ERR_IS_NOT_UPLOADED_FILE) {
			$bestandresult .= 'kan niet worden ge-upload vanwege een fout in het script.';
		} else if($error == UPLOAD_CUSTOM_ERR_NO_FILES) { // 25
			$bestandresult .= 'kan niet worden ge-upload omdat er geen bestanden doorgegeven zijn.';
		} else if($error == UPLOAD_CUSTOM_ERR_ALREADY_EXIST) { // 26
			$bestandresult .= 'kan niet worden ge-upload worden omdat hij al bestaat.';
		} else if($error == UPLOAD_CUSTOM_ERR_NO_TITLE) { // 30
			$bestandresult .= 'kan niet worden ge-upload worden omdat er geen titel opgegeven is.';
		} else {
			$bestandresult .= 'geeft errorcode '.$error.'.';
		}
		/*  (Nog) niet opgevangen errors:
		 *		UPLOAD_CUSTOM_ERR_NO_CATEGORY (31)
		 *		UPLOAD_CUSTOM_ERR_TITLE_EXISTS (32)
		 * 		UPLOAD_CUSTOM_ERR_INSERT_FAILED (33)
		 */

/*		if($errorcounter == $this->numberoffiles) {
			echo '<tr><td>Geen bestanden opgegeven...</td></tr>';
		}
		echo '<tr><td><a href=".">Opnieuw</a></td><td><a href="../">Naar documenten</a></td></tr>';
		echo '</table>';*/

		return $bestandresult;
	}
	
	// the view-function
	function view() {
		$postIsArray = isset($_POST) && is_array($_POST);
		if( !( (		$postIsArray				&& empty($_POST))
		&& (isset($_FILES) && is_array($_FILES) && empty($_FILES)) ) ) // TODO: overbodige checks weglaten
		// als de arrays $_POST en $_FILES *niet* leeg zijn
		{
			// er is iets ge-upload

//			if($postIsArray && isset($_POST['docId']) && !empty($_POST['docId'])){
//				// er is 1 bestand ge-upload vanuit documenten.php
//				$id = (int)$_POST['docId'];
//				$this->_toevoegen->uploadFiles();
//				if(isset($this->_toevoegen->errorcodes[1]) && $this->_toevoegen->errorcodes[1] == UPLOAD_CUSTOM_ERR_SUCCEEDED){
//					// als het ene bestand succesvol is ge-upload
//					$this->toevoegen->addFileExtension($id,$_FILES['file']['name']);
//					
//					// als dit lukt, naar documenten.php met een gelukt-alert?!
//					echo '<script language="JavaScript">'."\n"; // function naHetLaden(){ alert("'.$message.'") }
//					echo 'window.location="./gelukt";</script>'."\n";
//				} else {
//										
//					// naar documenten/toevoegen/mislukt linken
//					echo '<script language="JavaScript">'."\n"; // function naHetLaden(){ alert("'.$message.'") }
//					echo 'window.location="./mislukt";</script>'."\n";
//				}
//			} else {
				// er is een 'normale' upload gedaan (bijvoorbeeld 1 tot 3 bestanden tegelijk)
			$this->_toevoegen->uploadFiles();		// uploads the files in $_FILES
			$this->errorcodes=$this->_toevoegen->getErrorcodes();
//			}
		} else { // $_POST of $_FILES wel leeg
			$this->viewForm($this->numberoffiles);
			return;
		}
		
		if(isset($this->errorcodes) && is_array($this->errorcodes) && !empty($this->errorcodes)) {
			// er zijn bestanden geupload (want er zijn errorcodes)
			$numberOfErrors=$this->_toevoegen->getNumberOfErrors($this->errorcodes);
//			echo "\n".'<script language="javascript">alert(\'number of errors:'.$numberOfErrors.'\')</script>';
			if($numberOfErrors>0){
				$this->viewForm(count($_FILES));
			} else if($numberOfErrors==-1){
				$message='Er zijn geen bestanden opgegeven. Probeer het opnieuw.';
				echo '<script language="JavaScript">function naHetLaden(){ alert("'.$message.'") } '."\n";
				echo 'window.onload=naHetLaden</script>'."\n";
				$this->errorcodes='';
				$this->viewForm(count($_FILES));
			} else { // == 0
				echo '<script language="JavaScript">'."\n"; // function naHetLaden(){ alert("'.$message.'") }
				echo 'window.location="./gelukt";</script>'."\n";
			}
		} else {
			echo 'Er is iets fout gegaan met de errorcodes!';
		}
	}
	
/*	
	// Set-functions
	function setErrors($errors) {
		$this->errorcodes = $errors;
	}
*/	
}
?>
