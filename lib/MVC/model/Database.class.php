<?php

require_once 'MVC/model/Database.class.php';

/**
 * Database.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Database extends PDO {

	static private $_instance;

	public static function instance() {
		if (!isset(self::$_instance)) {

			if (defined('ETC_PATH')) {
				$cred = parse_ini_file(ETC_PATH . '/mysql.ini');
			} else {
				$cred = array(
					'host' => 'localhost',
					'user' => 'foo',
					'pass' => 'bar',
					'db' => 'csrdelft'
				);
			}
			$dsn = 'mysql:host=' . $cred['host'] . ';dbname=' . $cred['db'];
			$options = array(
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			);

			self::$_instance = new Database($dsn, $cred['user'], $cred['pass'], $options);
		}
		return self::$_instance;
	}

	private $_queries = array();

	public function getQueries() {
		return $this->_queries;
	}

	public function prepare($statement, $values = array(), array $options = array()) {
		if (defined('DEBUG')) {
			$query = $statement;
			foreach ($values as $value) {
				if (is_bool($value)) {
					$query = preg_replace('/\?/', ($value ? 'true' : 'false'), $query, 1);
				} else {
					$query = preg_replace('/\?/', "'$value'", $query, 1);
				}
			}
			array_push($this->_queries, $query);
		}
		return parent::prepare($statement, $options);
	}

}

?>