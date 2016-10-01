<?php
class EetplanBekenden extends PersistentEntity {
    public $uid1;
    public $uid2;

    public function getNoviet1() {
        return ProfielModel::instance()->find('uid = ?', array($this->uid1))->fetch();
    }

    public function getNoviet2() {
        return ProfielModel::instance()->find('uid = ?', array($this->uid2))->fetch();
    }

    protected static $table_name = 'eetplan_bekenden';
    protected static $persistent_attributes = array(
        'uid1' => array(T::UID, false),
        'uid2' => array(T::UID, false),
    );
    protected static $primary_key = array('uid1', 'uid2');
}
