<?php
/**
 * Class PeilingStem
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class PeilingStem extends PersistentEntity {

    public $peilingid;
    public $uid;

    protected static $persistent_attributes = array(
        'peilingid' => array(T::UnsignedInteger),
        'uid'       => array(T::UID)
    );

    protected static $primary_key = array('peilingid', 'uid');

    protected static $table_name = 'peiling_stemmen';

}
