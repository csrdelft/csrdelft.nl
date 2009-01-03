<?php
/*
 * segfault.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
header('Content-Type: text/html;');

class foo{

	private $bar;

	public function __construct(){
		echo 'about to segfault';
		$this->segFault();
		echo 'this will not be printed';
	}
	public function segFault(){
		return $this->segFault();
	}

}
$foo=new foo();


?>
