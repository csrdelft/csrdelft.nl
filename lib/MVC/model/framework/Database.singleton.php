<?php

/**
 * Database.singleton.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Database extends PDO {

	/**
	 * Singleton instance
	 * @var Database
	 */
	private static $instance;

	/**
	 * Get singleton Database instance.
	 * 
	 * @return Database
	 */
	public static function instance() {
		if (!isset(self::$instance)) {
			$cred = parse_ini_file(ETC_PATH . 'mysql.ini');
			if ($cred === false) {
				$cred = array(
					'host'	 => 'localhost',
					'user'	 => 'username',
					'pass'	 => 'password',
					'db'	 => 'csrdelft'
				);
			}
			$dsn = 'mysql:host=' . $cred['host'] . ';dbname=' . $cred['db'];
			$options = array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
				PDO::ATTR_ERRMODE			 => PDO::ERRMODE_EXCEPTION
			);
			self::$instance = new Database($dsn, $cred['user'], $cred['pass'], $options);
		}
		return self::$instance;
	}

	/**
	 * Array of SQL statements for debug
	 * @var array
	 */
	private static $queries = array();

	/**
	 * Get array of SQL statements for debug
	 * @return array
	 */
	public static function getQueries() {
		return self::$queries;
	}

	/**
	 * @source http://stackoverflow.com/a/1376838
	 * 
	 * Replaces any parameter placeholders in a query with the value of that
	 * parameter. Useful for debugging. Assumes anonymous parameters from 
	 * $params are are in the same order as specified in $query
	 *
	 * @param string $query The sql query with parameter placeholders
	 * @param array $params The array of substitution parameters
	 * @return string The interpolated query
	 */
	public static function interpolateQuery($query, $params) {
		$attributes = array();
		// build a regular expression for each parameter
		foreach ($params as $attribute => $value) {
			if (is_string($attribute)) {
				$attributes[] = '/:' . $attribute . '/';
			} else {
				$attributes[] = '/[?]/';
			}
			if (is_string($value)) {
				$params[$attribute] = '"' . $value . '"'; // quotes
			} else {
				$params[$attribute] = $value;
			}
		}
		return preg_replace($attributes, $params, $query, 1);
	}

	/**
	 * Optional named parameters.
	 * 
	 * @param array $attributes
	 * @param string $from
	 * @param string $where
	 * @param array $params
	 * @param string $orderby
	 * @param string $groupby
	 * @param int $limit
	 * @param int $start
	 * @return PDOStatement
	 */
	public static function sqlSelect(array $attributes, $from, $where = null, array $params = array(), $orderby = null, $groupby = null, $limit = null, $start = 0) {
		$sql = 'SELECT ' . implode(', ', $attributes) . ' FROM ' . $from;
		if ($where !== null) {
			$sql .= ' WHERE ' . $where;
		}
		if ($orderby !== null) {
			$sql .= ' ORDER BY ' . $orderby;
		}
		if ($groupby !== null) {
			$sql .= ' GROUP BY ' . $groupby;
		}
		if ($limit !== null) {
			$sql .= ' LIMIT ' . (int) $start . ', ' . (int) $limit;
		}
		$query = self::instance()->prepare($sql);
		$query->execute($params);
		self::$queries[] = self::interpolateQuery($query->queryString, $params);
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
		$query = self::instance()->prepare($sql);
		$query->execute($params);
		self::$queries[] = self::interpolateQuery($query->queryString, $params);
		return (boolean) $query->fetchColumn();
	}

	/**
	 * Requires named parameters.
	 * Optional REPLACE (DELETE & INSERT) if primary key already exists.
	 * 
	 * @param string $into
	 * @param array $properties
	 * @param boolean $replace
	 * @return string last inserted row id or sequence value
	 * @throws Exception if number of rows affected !== 1
	 */
	public static function sqlInsert($into, array $properties, $replace = false) {
		$insert_params = array();
		foreach ($properties as $attribute => $value) {
			$insert_params[':I' . $attribute] = $value; // name parameters after attribute
		}
		if ($replace) {
			$sql = 'REPLACE';
		} else {
			$sql = 'INSERT';
		}
		$sql .= ' INTO ' . $into;
		$sql .= ' (' . implode(', ', array_keys($properties)) . ')';
		$sql .= ' VALUES (' . implode(', ', array_keys($insert_params)) . ')'; // named params
		$query = self::instance()->prepare($sql);
		$query->execute($insert_params);
		self::$queries[] = self::interpolateQuery($query->queryString, $insert_params);
		if ($query->rowCount() !== 1) {
			throw new Exception('sqlInsert rowCount=' . $query->rowCount());
		}
		return self::instance()->lastInsertId();
	}

	/**
	 * Requires positional parameters.
	 * 
	 * @param string $into
	 * @param array $properties = array( array("attr_name1", "attr_name2", ...), array("entry1value1", "entry1value2", ...), array("entry2value1", "entry2value2", ...), ...)
	 * @param boolean $replace DELETE & INSERT if primary key already exists
	 * @return int number of rows affected
	 * @throws Exception if number of values !== number of properties
	 */
	public static function sqlInsertMultiple($into, array $properties, $replace = false) {
		if ($replace) {
			$sql = 'REPLACE';
		} else {
			$sql = 'INSERT';
		}
		$insert_values = array();
		$attributes = array_shift($properties);
		$count = count($attributes);
		$sql .= ' INTO ' . $into . ' (' . implode(', ', $attributes) . ') VALUES ';
		foreach ($properties as $i => $props) { // for all entries
			if (count($props) !== $count) {
				throw new Exception('Missing property value(s) for entry: ' . $i);
			}
			if ($i > 0) {
				$sql .= ', ';
			}
			$sql .= '(';
			foreach ($props as $j => $value) {
				$param = ':I' . $i . $attributes[$j]; // name parameters after attribute with index
				$insert_values[$param] = $value;
				if ($j > 0) {
					$sql .= ', ';
				}
				$sql .= $param;  // named params
			}
			$sql .= ')';
		}
		$query = self::instance()->prepare($sql);
		$query->execute($insert_values);
		self::$queries[] = self::interpolateQuery($query->queryString, $insert_values);
		return $query->rowCount();
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
		$attributes = array();
		foreach ($properties as $attribute => $value) {
			$attributes[] = $attribute . ' = :U' . $attribute; // name parameters after attribute
			if (array_key_exists(':U' . $attribute, $where_params)) {
				throw new Exception('Named parameter already defined: ' . $attribute);
			}
			$where_params[':U' . $attribute] = $value;
		}
		$sql .= implode(', ', $attributes);
		$sql .= ' WHERE ' . $where;
		if ($limit !== null) {
			$sql .= ' LIMIT ' . (int) $limit;
		}
		$query = self::instance()->prepare($sql);
		$query->execute($where_params);
		self::$queries[] = self::interpolateQuery($query->queryString, $where_params);
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
		if ($limit !== null) {
			$sql .= ' LIMIT ' . (int) $limit;
		}
		$query = self::instance()->prepare($sql);
		$query->execute($where_params);
		self::$queries[] = self::interpolateQuery($query->queryString, $where_params);
		return $query->rowCount();
	}

}
