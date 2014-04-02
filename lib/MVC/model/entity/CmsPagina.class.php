<?php

/**
 * CmsPagina.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Content Management System Paginas zijn statische pagina's die via de front-end kunnen worden gewijzigd.
 */
class CmsPagina extends PersistentEntity {

	/**
	 * Primary key
	 * @var string
	 */
	public $naam;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Inhoud
	 * @var string
	 */
	public $inhoud;
	/**
	 * DateTime
	 * @var string
	 */
	public $laatst_gewijzigd;
	/**
	 * Permissie voor tonen
	 * @var string
	 */
	public $rechten_bekijken;
	/**
	 * Link
	 * @var string
	 */
	public $rechten_bewerken;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'naam' => 'varchar(255) NOT NULL',
		'titel' => 'varchar(255) NOT NULL',
		'inhoud' => 'longtext NOT NULL',
		'laatst_gewijzigd' => 'datetime NOT NULL',
		'rechten_bekijken' => 'varchar(255) NOT NULL',
		'rechten_bewerken' => 'varchar(255) NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('naam');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'pagina';

	public function magBekijken() {
		return LoginLid::mag($this->rechten_bekijken);
	}

	public function magBewerken() {
		return LoginLid::mag($this->rechten_bewerken);
	}

}
