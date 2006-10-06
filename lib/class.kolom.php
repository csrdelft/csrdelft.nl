<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.column.php
# -------------------------------------------------------------------
#
# -------------------------------------------------------------------
# Historie:
# 18-12-2004 Hans van Kranenburg
# . aangemaakt
#

require_once('class.simplehtml.php');

class string2object{
	var $_string;
	function string2object($string){ $this->_string=$string; }
	function view(){ echo $this->_string; }
}
class kolom extends SimpleHTML {

	### private ###

	# Een object is een van SimpleHTML afgeleid object waarin een
	# stuk pagina zit, wat we er met view() uit kunnen krijgen.
	var $_objects = array();

	### public ###

	function kolom(){
	}

	
	function addObject(&$object) { $this->_objects[] =& $object; }
	function addTekst($string){ $this->addObject(new string2object($string)); }
	//aliasje
	function add(&$object){ $this->addObject(&$object); }
	
	function getTitel(){
		if(isset($this->_objects[0])){
			return $this->_objects[0]->getTitel();
		}
	}
	function view() {
		foreach ($this->_objects as $object) {
			$object->view();
			echo '<br />';
		}
	}
}

?>
