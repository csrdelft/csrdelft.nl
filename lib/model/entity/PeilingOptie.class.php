<?php
/**
 * Class PeilingOptie
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class PeilingOptie extends PersistentEntity {
    public $id;
    public $peilingid;
    public $optie;
    public $stemmen;
    public $percentage = 0;

    protected static $table_name = 'peilingoptie';
    protected static $primary_key = array('id');
    protected static $persistent_attributes = array(
        'id'        => array(T::Integer, false, 'auto_increment'),
        'peilingid' => array(T::Integer),
        'optie'     => array(T::String),
        'stemmen'   => array(T::Integer)
    );
}
