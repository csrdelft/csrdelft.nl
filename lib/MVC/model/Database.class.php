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
	 * Get singleton Database instance
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
					$query = preg_replace('/\?/', ($value ? 'true' : 'false'), $query, 1);
				} else {
					$query = preg_replace('/\?/', "'$value'", $query, 1);
				}
			}
			$this->queries[] = $query;
		}
		return parent::prepare($statement, $driver_options);
	}

	/**
	 * Optional named parameters.
	 * 
	 * @param array $select
	 * @param string $from
	 * @param string $where
	 * @param array $params
	 * @param string $orderby
	 * @param int $limit
	 * @param int $start
	 * @return PDOStatement
	 */
	public static function sqlSelect(array $select, $from, $where = null, array $params = array(), $orderby = null, $limit = null, $start = 0) {
		$sql = 'SELECT ' . implode(', ', $select) . ' FROM ' . $from;
		if ($where !== null) {
			$sql .= ' WHERE ' . $where;
		}
		if ($orderby !== null) {
			$sql .= ' ORDER BY ' . $orderby;
		}
		if ($limit !== null) {
			$sql .= ' LIMIT ' . $start . ', ' . $limit;
		}
		$query = self::instance()->prepare($sql, $params);
		$query->execute($params);
		return $query;
	}

	/**
	 * Optional named parameters.
	 * 
	 * @param array $properties
	 * @return int last insert id
	 * @throws Exception row count !== 1
	 */
	public static function sqlInsert($into, array $properties) {
		$params = array();
		foreach ($properties as $key => $value) {
			$params[':' . $key] = $value; // named params
		}
		$sql = 'INSERT INTO ' . $into;
		$sql .= ' (' . implode(', ', array_keys($properties)) . ')';
		$sql .= ' VALUES (' . implode(', ', array_keys($params)) . ')';
		$query = self::instance()->prepare($sql, $params);
		$query->execute($params);
		if ($query->rowCount() !== 1) {
			throw new Exception('sqlInsert rowCount=' . $query->rowCount());
		}
		return self::instance()->lastInsertId();
	}

	/**
	 * Requires named parameters.
	 * 
	 * @param string $table
	 * @param array $set_properties
	 * @param string $where
	 * @param array $where_params
	 * @return int number of rows affected
	 */
	public static function sqlUpdate($table, array $set_properties, $where, array $where_params = array()) {
		$sql = 'UPDATE ' . $table . ' SET ';
		$fields = array();
		foreach ($set_properties as $key => $value) {
			$fields[] = $key . ' = :sql' . $key; // sql prefix
			$where_params[':sql' . $key] = $value; // named parameters
		}
		$sql .= implode(', ', $fields);
		$sql .= ' WHERE ' . $where;
		$query = self::instance()->prepare($sql, $where_params);
		$query->execute($where_params);
		return $query->rowCount();
	}

	/**
	 * Optional named parameters.
	 * 
	 * @param string $where
	 * @param array $where_params
	 * @return int number of rows affected
	 */
	public static function sqlDelete($from, $where, array $where_params) {
		$sql = 'DELETE FROM ' . $from;
		$sql .= ' WHERE ' . $where;
		$query = self::instance()->prepare($sql, $where_params);
		$query->execute($where_params);
		return $query->rowCount();
	}

	/**
	 * Create table and return SQL.
	 * 
	 * @param string $table
	 * @param array $columns
	 * @param array $primary_key
	 * @return string
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
