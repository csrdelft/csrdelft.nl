<?php
/**
 * Created by IntelliJ IDEA.
 * User: René
 * Date: 2-10-2014
 * Time: 21:02
 */

class SocCie extends PersistentEntity {

    /**
     * Saldo
     * @var string
     */
    public $saldo;

    /**
     * Database table fields
     * @var array
     */
    protected static $persistent_fields = array(
        'socCieId'	 => array(T::Integer),
        'stekUID'	 => array(T::UID),
        'saldo'	 => array(T::Integer, false),
        'naam'	 => array(T::String),
        'deleted'	 => array(T::Integer, false)
    );

    /**
     * Database table name
     * @var string
     */
    protected static $table_name = 'socCieKlanten';

    /**
     * Database primary key
     * @var array
     */
    protected static $primary_keys = array('socCieId');

    public function getSaldo() {
        return $this->saldo / 100;
    }

}