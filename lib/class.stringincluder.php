<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.stringincluder.php
# -------------------------------------------------------------------


class stringincluder{
	public $string='lege pagina';
	public $title;
	function stringincluder($string, $title=''){
		$this->string=$string;
		$this->title=$title;
	}
	function getTitel(){ return $this->title; }
	function view(){
		echo $this->string;
	}
}
?>
