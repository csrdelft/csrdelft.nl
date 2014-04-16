<?php

/**
 * Database.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Database extends PDO {

	/**
	 * Singleton instance
	 * @var Database
	 */
	private static $_instance;

	/**
	 * Get singleton Database instance.
	 * 
	 * @return Database
	 */
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

	/**
	 * Array of prepared SQL statements for debug
	 * @var array
	 */
	private $queries = array();

	/**
	 * Get array of prepared SQL statements for debug
	 * @return array
	 */
	public function getQueries() {
		return $this->queries;
	}

	/**
	 * Build a safe query.
	 * 
	 * @param string $statement SQL
	 * @param array $values
	 * @param array $driver_options
	 * @return PDOStatement
	 */
	public function prepare($statement, $values = array(), array $driver_options = array()) {
		if (defined('DEBUG')) {
			$query = $statement;
			foreach ($values as $value) {
				if (is_bool($value)) {
					$query = preg_replace('/\?/', ($value ? 'true' : 'false'), $query, 1); //TODO: named parameters
				} else {
					$query = preg_replace('/\?/', "'$value'", $query, 1); //TODO: named parameters
				}
			}
			$this->queries[] = $query;
		}
		return parent::prepare($statement, $driver_options);
	}

	/**
	 * Optional named parameters.
	 * 
	 * @param array $columns
	 * @param string $from
	 * @param string $where
	 * @param array $params
	 * @param string $orderby
	 * @param int $limit
	 * @param int $start
	 * @return PDOStatement
	 */
	public static function sqlSelect(array $columns, $from, $where = null, array $params = array(), $orderby = null, $limit = null, $start = 0) {
		$sql = 'SELECT ' . implode(', ', $columns) . ' FROM ' . $from;
		if ($where !== null) {
			$sql .= ' WHERE ' . $where;
		}
		if ($orderby !== null) {
			$sql .= ' ORDER BY ' . $orderby;
		}
		if (is_int($limit)) {
			$sql .= ' LIMIT ' . (int) $start . ', ' . $limit;
		}
		$query = self::instance()->prepare($sql, $params);
		$query->execute($params);
		return $query;
	}

	/**
	 * Optional named parameters.
	 * 
	 * @param string $from
	 * @param string $where
	 * @param array $params
	 * @return boolean
	 */
	public static function sqlExists($from, $where = null, array $params = array()) {
		$sql = 'SELECT EXISTS (SELECT 1 FROM ' . $from;
		if ($where !== null) {
			$sql .= ' WHERE ' . $where;
		}
		$sql .= ')';
		$query = self::instance()->prepare($sql, $params);
		$query->execute($params);
		$result = $query->fetchColumn();
		return (boolean) $result;
	}

	/**
	 * Requires named parameters.
	 * Optional ON DUPLICATE KEY UPDATE
	 * 
	 * @param string $into
	 * @param array $properties
	 * @param boolean $update_duplicate
	 * @return string last inserted row id or sequence value
	 * @throws Exception if number of rows affected !== 1
	 */
	public static function sqlInsert($into, array $properties, $update_duplicate = false) {
		$insert_params = array();
		foreach ($properties as $key => $value) {
			$insert_params[':I' . $key] = $value; // name parameters after column
		}
		$sql = 'INSERT INTO ' . $into;
		$sql .= ' (' . implode(', ', array_keys($properties)) . ')';
		$sql .= ' VALUES (' . implode(', ', array_keys($insert_params)) . ')'; // named params
		if ($update_duplicate) {
			$sql .= ' ON DUPLICATE KEY UPDATE '; // code below duplicate of sqlUpdate
			$fields = array();
			foreach ($properties as $key => $value) {
				$fields[] = $key . ' = :U' . $key; // name parameters after column
				if (array_key_exists(':U' . $key, $insert_params)) {
					throw new Exception('Named parameter already defined: ' . $key);
				}
				$insert_params[':U' . $key] = $value;
			}
			$sql .= implode(', ', $fields);
		}
		$query = self::instance()->prepare($sql, $insert_params);
		$query->execute($insert_params);
		if (!$update_duplicate AND $query->rowCount() !== 1) {
			throw new Exception('sqlInsert rowCount=' . $query->rowCount());
		}
		return self::instance()->lastInsertId();
	}

	/**
	 * Requires named parameters.
	 * 
	 * @param string $table
	 * @param array $properties
	 * @param string $where
	 * @param array $where_params
	 * @param int $limit
	 * @return int number of rows affected
	 * @throws Exception if duplicate named parameter
	 */
	public static function sqlUpdate($table, array $properties, $where, array $where_params = array(), $limit = null) {
		$sql = 'UPDATE ' . $table . ' SET ';
		$fields = array();
		foreach ($properties as $key => $value) {
			$fields[] = $key . ' = :U' . $key; // name parameters after column
			if (array_key_exists(':U' . $key, $where_params)) {
				throw new Exception('Named parameter already defined: ' . $key);
			}
			$where_params[':U' . $key] = $value;
		}
		$sql .= implode(', ', $fields);
		$sql .= ' WHERE ' . $where;
		if (is_int($limit)) {
			$sql .= ' LIMIT ' . $limit;
		}
		$query = self::instance()->prepare($sql, $where_params);
		$query->execute($where_params);
		return $query->rowCount();
	}

	/**
	 * Optional named parameters.
	 * 
	 * @param string $from
	 * @param string $where
	 * @param array $where_params
	 * @param int $limit
	 * @return int number of rows affected
	 */
	public static function sqlDelete($from, $where, array $where_params, $limit = null) {
		$sql = 'DELETE FROM ' . $from;
		$sql .= ' WHERE ' . $where;
		if (is_int($limit)) {
			$sql .= ' LIMIT ' . $limit;
		}
		$query = self::instance()->prepare($sql, $where_params);
		$query->execute($where_params);
		return $query->rowCount();
	}

	/**
	 * Create table and return SQL.
	 * 
	 * @param string $name
	 * @param array $columns
	 * @param array $primary_key
	 * @return string SQL query
	 */
	public static function sqlCreateTable($name, array $columns, array $primary_key) {
		$sql = 'CREATE TABLE ' . $name . ' (';
		foreach ($columns as $key => $value) {
			$sql .= $key . ' ' . $value . ', ';
		}
		$sql .= 'PRIMARY KEY (' . implode(', ', $primary_key) . ')) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
		$query = self::instance()->prepare($sql);
		$query->execute();
		return $sql;
	}

}
