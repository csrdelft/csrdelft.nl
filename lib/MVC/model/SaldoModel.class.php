<?php
/**
 * Created by IntelliJ IDEA.
 * User: René
 * Date: 2-10-2014
 * Time: 20:48
 */

class SaldoModel extends PersistenceModel {

	const orm = 'Saldo';

    protected static $instance;

    public function getSaldo($uid) {

        $sql = 'SELECT saldo / 100 AS saldo FROM socCieKlanten WHERE stekUID = :uid';
        $query = Database::instance()->prepare($sql);
        $query->bindValue(':uid', $uid);

        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result['saldo'];

    }

}