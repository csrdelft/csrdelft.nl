<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.hok.php
# -------------------------------------------------------------------
# Maakt zo'n blauw hok met een titel en inhoud
# Als er meerdere titel/objectparen in zitten, dan worden de blauwe
# blokken aan elkaar geschakeld.
# -------------------------------------------------------------------


require_once('class.simplehtml.php');

class Hok extends SimpleHTML {

	### private ###

	# Een object is een van SimpleHTML afgeleid object waarin een
	# stuk pagina zit.
	var $_titels = array();
	var $_objects = array();

	### public ###

	function Hok($titel, &$object) {
		$this->addObject($titel, $object);
	}

	function addObject($titel, &$object) {
		$this->_titels[] = $titel;
		$this->_objects[] =& $object;
	}

	# Heel simpel... De tabelopmaak zit nu in CSS
	function view() {

?>
<table style="width: 100%;" class="hoktable">
<?
		for ($i=0; $i < count($this->_titels); $i++) {
?>
<tr><td style="width: 100%;" class="hoktitel"><?=htmlspecialchars($this->_titels[$i])?></td></tr>
<tr><td style="width: 100%;" class="hoktekst"><?=$this->_objects[$i]->view()?></td></tr>
<?
		}
?>
</table>
<?

	}
}

?>
