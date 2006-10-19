<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.csrdelft.php
# -------------------------------------------------------------------
# csrdelft is de klasse waarbinnen een pagina in elkaar wordt gezooid
#
# -------------------------------------------------------------------
# Historie:
# 18-12-2004 Hans van Kranenburg
# . aangemaakt
#

require_once('class.simplehtml.php');

class csrdelft extends SimpleHTML {

	### private ###
	var $_lid;
	
	//body is een object met een view-methode welke de content van de pagina maakt.
	//Als body een methode zijKolom() heeft die gebruiken om de zij-kolom te vullen
	var $_body;
	//menu bevat een menu-object.
	var $_menu;
	//standaard geen zijkolom...
	var $_zijkolom=false;
	
	var $_titel='Geen titel gezet.';
	var $_waarbenik=false;
	
	function csrdelft($body, &$lid, &$db){
		if(is_object($body)){
			$this->_body=$body;
			//als de body een methode heeft om een titel mee te geven die gebruiken, anders de standaard.
			if(method_exists($this->_body, 'getTitel')){
				$this->_titel=$this->_body->getTitel();
			}
		}
		$this->_lid=&$lid;
		//nieuw menu-object aanmaken...
		require_once('class.menu.php');
		$this->_menu=new menu(&$lid, &$db);
		
	}
	
	function getTitel(){ return mb_htmlentities($this->_titel); }
	function setZijkolom($zijkolom){
		if(is_object($zijkolom)){
			$this->_zijkolom=$zijkolom;
		}
	}
	function getBreed(){
		if($this->_zijkolom===false){ echo 'Breed'; }else{ echo ''; }
	}
	
	function viewWaarbenik(){
		if(is_object($this->_waarbenik)){
			echo 'bla';
		}elseif(method_exists($this->_body, 'viewWaarbenik')){
			echo '&raquo; ';
			$this->_body->viewWaarbenik();
		}else{
			//uit de menu-array halen
			$this->_menu->viewWaarbenik();
		}
	}
	function view() {
		header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>
<head>
  <title>C.S.R.-Delft | <?php echo $this->getTitel() ?></title>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content='PubCie C.S.R.-Delft, Jan Pieter Waagmeester' />
	<meta name="robots" content="index, follow" />
	<link rel="stylesheet" href="/layout/undohtml.css" type="text/css" />
	<link rel="stylesheet" href="/layout/default.css" type="text/css" />
	<link rel="stylesheet" href="/layout/forum.css" type="text/css" />
	<script type="text/javascript" src="/layout/csrdelft.js" ></script>
	<link rel="alternate" title="C.S.R.-Delft RSS" type="application/rss+xml" href="http://csrdelft.nl/forum/rss.php" />
</head>
<body>
<div id="layoutContainer">
<?php $this->_menu->view(); ?>
<div id="bodyContainer<?php echo $this->getBreed(); ?>">
	<div id="lichaam<?php echo $this->getBreed(); ?>">
		<div id="bodyContent">
			<?php $this->_body->view(); ?>
		</div>
	</div>
	<div id="copyright">
		<div id="copyrightContent">
			&copy; 2006 PubCie Waagmeester der C.S.R.-Delft. | <a href="http://validator.w3.org/check?uri=referer">XHTML 1.1</a>
		</div>
	</div>
</div>
<?php
if($this->_zijkolom!==false){
	echo '<div id="zijkolom"><div id="zijkolomContent">';
	if(is_object($this->_zijkolom)){
		$this->_zijkolom->view();
	}else{
		foreach($this->_zijkolom as $object){
			$object->view();
		}
	}
	echo '</div></div>';
}
?>
<div id="navigatie">
	<div id="navigatieContent"><?php $this->viewWaarbenik(); ?></div>
</div>
<div id="hoofder<?php echo $this->getBreed(); ?>">
	<div id="beeldmerk"><a href="/"><img src="/layout/images/csr.jpg" alt="Beeldmerk van de Vereniging" /></a></div>
</div>
<?php
if($this->_lid->isLoggedIn()){
	//zoekformuliertje voor de ledenlijst...
	echo '<div id="searchbox">
		<form method="post" action="/leden/lijst.php"><p>
			<input type="hidden" name="a" value="zoek" /><input type="hidden" name="waar" value="naam" /><input type="hidden" name="moot" value="alle" /><input type="hidden" name="status" value="leden" />
			<input type="hidden" name="sort" value="achternaam" /><input type="hidden" name="kolom[]" value="adres" /><input type="hidden" name="kolom[]" value="email" /><input type="hidden" name="kolom[]" value="telefoon" />
			<input type="hidden" name="kolom[]" value="mobiel" />';
	if(isset($_POST['wat'])){
		echo '<input type="text" value="'.mb_htmlentities($_POST['wat']).'" name="wat" />';
	}else{
		echo '<input type="text" value="Zoeken in ledenlijst..." onfocus="this.value=\'\'" name="wat" />';
	}
	echo '</p></form></div>';
} 
//einde isLoggedIn();
?>
<div id="personalBox">
	<?php
		//inloggen bla bla bla
		if($this->_lid->isLoggedIn()){
			echo 'U bent '.str_replace(' ', '&nbsp;', $this->_lid->getCivitasName()).'<br />';;
			echo ' <a href="/logout.php">log&nbsp;uit</a> | <a href="/leden/profiel/'.$this->_lid->getUid().'">profiel</a>';
		}else{
			//linkje om het inlogformulier weer te geven.
			echo '<a href="#" onclick="document.getElementById(\'inloggen\').style.display = \'block\'">
				Inloggen...</a><div id="inloggen">';
			echo '<form id="frm_login" action="/login.php" method="post">';
			//eventueel een foutmelding over het inloggen weergeven...
			if (isset($_SESSION['auth_error'])) {
				print('<span style="color: red;">'.htmlspecialchars($_SESSION['auth_error']) . '</span><br />' . "\n");
				unset($_SESSION['auth_error']);
			}
			echo '
				<table>
				<tr><td><strong>Inloggen:</strong></td><td>
					<input type="hidden" name="url" value="'.$_SERVER["REQUEST_URI"].'" />
					<input type="submit" class="login" value="inloggen" /></td></tr>
				<tr><td>Naam:</td><td><input type="text" name="user" class="login" /></td></tr>
				<tr><td>Wachtwoord:</td><td><input type="password" name="pass" class="login" /></td></tr>
				</table>
			</form>';
			echo '</div>';
		}
	?>

</div>
<div id="lijntje"><img src="/layout/images/pixel.gif" height="3px" width="20px" alt="lijntje..." /></div>
<div id="hoofderFoto"><img src="/layout/images/hoofder5.jpg" height="130px" alt="een impressie van de Civitas" /></div>
</div>
</body>
</html>
<?php		
	}

}

?>
