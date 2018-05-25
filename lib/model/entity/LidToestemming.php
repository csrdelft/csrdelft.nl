<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\T;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 *
 * Een LidToestemming beschrijft een Instelling per Lid.
 */
class LidToestemming extends Instelling {
    /**
     * Lidnummer1
     * Foreign key
     * @var string
     */
    public $uid;

    /**
     * Database table columns
     * @var array
     */
    protected static $persistent_attributes = array(
        'uid' => array(T::UID),
    );
    /**
     * Database primary key
     * @var array
     */
    protected static $primary_key = array('module', 'instelling_id', 'uid');
    /**
     * Database table name
     * @var string
     */
    protected static $table_name = 'lidtoestemmingen';
}
