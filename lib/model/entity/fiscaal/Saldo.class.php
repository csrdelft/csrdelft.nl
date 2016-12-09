<?php

require_once 'model/entity/fiscaal/SaldoCommissie.enum.php';

/**
 * Saldo.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class Saldo extends PersistentEntity implements JsonSerializable {

    public $cie;
    public $uid;
    public $moment;
    public $saldo;
    /**
     * Database table columns
     * @var array
     */
    protected static $persistent_attributes = array(
        'cie'        => array(T::Enumeration, false, 'SaldoCommissie'),
        'uid'        => array(T::UID),
        'moment'     => array(T::DateTime),
        'saldo'      => array(T::Float)
    );
    /**
     * Database primary key
     * @var array
     */
    protected static $primary_key = array();
    /**
     * Database table name
     * @var string
     */
    protected static $table_name = 'saldolog';

    public function jsonSerialize() {
        // Time * 1000 voor flot
        return sprintf('[%d, %.2F]', strtotime($this->moment) * 1000, $this->saldo);
    }

}
