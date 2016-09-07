<?php

/**
 * Bestuur.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Bestuur extends AbstractGroep {

	const leden = 'BestuursLedenModel';

	/**
	 * Bestuurstekst
	 * @var string
	 */
	public $bijbeltekst;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'bijbeltekst' => array(T::Text)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'besturen';

	public function getUrl() {
		return '/groepen/besturen/' . $this->id . '/';
	}

}
