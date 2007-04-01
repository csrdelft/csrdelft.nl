<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.stringincluder.php
# -------------------------------------------------------------------


class stringincluder{
	var $string='lege pagina';
	var $title;
	function stringincluder($string, $title=''){
		$this->string=$string;
		$this->title=$title;
	}
	function getTitle(){ return $this->title; }
	function view(){
		echo $this->string;
	}
}
?>
