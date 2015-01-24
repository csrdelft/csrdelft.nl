<?php

/**
 * MededelingenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MededelingenModel extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $mededeling_id;
	/**
	 * Uid van auteur
	 * @var string
	 */
	public $uid;
	/**
	 * Bestuur, commissie, lid
	 * @var string
	 */
	public $type;
	/**
	 * Textuele inhoud met eventueel bbcode
	 * @var string 
	 */
	public $tekst;
	/**
	 * Iedereen, leden, oud-leden, niemand, prullenbak
	 * @var string
	 */
	public $zichtbaar_voor;
	/**
	 * Vanaf dit moment zichtbaar
	 * @var string
	 */
	public $zichtbaar_vanaf;
	/**
	 * Tot dit moment zichtbaar
	 * @var string
	 */
	public $zichtbaar_tot;
	/**
	 * Volgorde van weergave
	 * @var int
	 */
	public $prioriteit;
	/**
	 * Url naar afbeelding van 200x200
	 * @var string
	 */
	public $afbeelding_url;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'mededeling_id'		 => array(T::Integer, false, 'auto_increment'),
		'uid'				 => array(T::UID),
		'type'				 => array(T::String),
		'tekst'				 => array(T::Text),
		'zichtbaar_voor'	 => array(T::String),
		'zichtbaar_vanaf'	 => array(T::DateTime),
		'zichtbaar_tot'		 => array(T::DateTime),
		'prioriteit'		 => array(T::Integer),
		'afbeelding_url'	 => array(T::Text)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('$mededeling_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'mededelingen';

}
