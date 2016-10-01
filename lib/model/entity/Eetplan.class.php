<?php
class Eetplan extends PersistentEntity {
    public $uid;
    public $woonoord_id;
    public $avond;

    public function getWoonoord() {
        return WoonoordenModel::get($this->woonoord_id);
    }

    public function getNoviet() {
        return ProfielModel::get($this->uid);
    }

    protected static $table_name = 'eetplan';
    protected static $persistent_attributes = array(
        'uid' => array(T::UID, false),
        'woonoord_id' => array(T::Integer, false),
        'avond' => array(T::Date, false)
    );

    protected static $primary_key = array('uid', 'woonoord_id');
}