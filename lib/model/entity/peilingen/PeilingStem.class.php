<?php
/**
 * Class PeilingStem
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * 
 */
class PeilingStem extends PersistentEntity {

	/**
	 * Foreign key
	 * @var int
	 */
	public $peiling_id;
	/**
	 * Foreign key
	 * @var string
	 */
	public $uid;

	protected static $persistent_attributes = array(
		'peiling_id' => array(T::Integer),
		'uid'       => array(T::UID)
	);

	protected static $primary_key = array('peiling_id', 'uid');

	protected static $table_name = 'peiling_stemmen';

}
