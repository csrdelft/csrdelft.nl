<?php

/**
 * LogModel.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class LogModel extends PersistenceModel {

    const ORM = 'LogEntry';

    protected static $instance;

    public function opschonen() {
        // Gebruik directe delete, dit is veel sneller
        Database::sqlDelete($this->getTableName(), 'moment < ?', array(date('Y-m-d H:i:s', strtotime('-2 months'))));
    }

    public function log() {
        $entry = new LogEntry();
        if (isset($_SESSION['_suedFrom'])) {
            $entry->uid = $_SESSION['_suedFrom'];
        } else {
            $entry->uid = $_SESSION['_uid'];
        }
        $entry->moment = getDateTime();
        $entry->locatie = '';
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $entry->ip = $_SERVER['REMOTE_ADDR'];
        } else {
            // Niet een extern request
            return;
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            $entry->url = $_SERVER['REQUEST_URI'];
        } else {
            $entry->url = '';
        }
        if (isset($_SERVER['HTTP_REFERER'])) {
            $entry->referer = $_SERVER['HTTP_REFERER'];
        } else {
            $entry->referer = '';
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $entry->useragent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $entry->useragent = '';
        }

        $this->create($entry);
    }

}
