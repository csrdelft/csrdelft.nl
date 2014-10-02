<?php
/**
 * Created by IntelliJ IDEA.
 * User: René
 * Date: 2-10-2014
 * Time: 20:48
 */

class SocCieModel extends PersistenceModel {

	const orm = 'SocCie';

    protected static $instance;

    public function getSaldo($uid) {

        $result = $this->find('stekUID = ?', array($uid))->fetch();
        if(!$result) {
            return 0;
        }
        return $result->getSaldo();

    }

}