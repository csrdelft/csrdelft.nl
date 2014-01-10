<?php

/**
 * Mededeling.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Mededeling {

	public $id = 'int(11) NOT NULL AUTO_INCREMENT';
	public $type = 'varchar(255) NOT NULL';
	public $tekst = 'text NOT NULL';
	public $zichtbaar_voor = 'varchar(255) NOT NULL';
	public $zichtbaar_vanaf = 'datetime NOT NULL';
	public $zichtbaar_tot = 'datetime NOT NULL';
	public $prioriteit = 'int(11) NOT NULL';

}

?>