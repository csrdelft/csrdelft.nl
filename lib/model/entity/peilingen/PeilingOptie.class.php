<?php
/**
 * Class PeilingOptie
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class PeilingOptie extends PersistentEntity {

	/**
	 * Foreign key
	 * @var int
	 */
    public $peiling_id;
	/**
	 * Titel
	 * @var string
	 */
    public $optie;
	/**
	 * Aantal stemmen
	 * @var int
	 */
    public $stemmen;

    public static function init($optie) {
        $peilingoptie = new PeilingOptie();
        $peilingoptie->optie = $optie;
        return $peilingoptie;
    }

    protected static $persistent_attributes = array(
        'peiling_id' => array(T::Integer),
        'optie'     => array(T::String),
        'stemmen'   => array(T::Integer)
    );

	protected static $table_name = 'peiling_optie';

}
