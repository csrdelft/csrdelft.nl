<?php

/**
 * Class Peiling
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class Peiling extends PersistentEntity {
    public $id;
    public $titel;
    public $tekst;

    protected static $table_name = 'peiling';
    protected static $primary_key = array('id');
    protected static $persitent_attributes = array(
        'id'    => array(T::Integer, false, 'auto_increment'),
        'titel' => array(T::String),
        'tekst' => array(T::Text)
    );
}


