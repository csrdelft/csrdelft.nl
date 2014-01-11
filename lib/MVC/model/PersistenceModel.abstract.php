<?php

require_once 'MVC/model/Database.class.php';

/**
 * PersistenceModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Generic CRUD.
 * 
 */
abstract class PersistenceModel {

	private $class_name;
	protected $table_name;
	protected $columns;
	protected $primary_key;

	public function __construct($class_name) {
		$this->class_name = $class_name;
		$this->table_name = $class_name::$table_name;
		$this->columns = array_keys($class_name::$persistent_fields);
		$this->primary_key = $class_name::$primary_key;
	}

	/**
	 * Optional named parameters.
	 * 
	 * @param string $where
	 * @param array $params
	 * @return PersistentEntity
	 * @throws Exception row count !== 1
	 */
	protected function fetchOne($where, array $params) {
		$one = $this->load($where, $params, null, 1);
		if (sizeof($one) !== 1) {
			throw new Exception('fetchOne row count: ' . sizeof($one));
		}
		return $one[0];
	}

	/**
	 * Optional named parameters.
	 * 
	 * @param string $where
	 * @param array $params
	 * @param string $orderby
	 * @param int $limit
	 * @param int $start
	 * @return PersistentEntity[]
	 */
	protected function load($where = null, array $params = array(), $orderby = null, $limit = null, $start = 0) {
		$sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table_name;
		if ($where !== null) {
			$sql .= ' WHERE ' . $where;
		}
		if ($orderby !== null) {
			$sql .= ' ORDER BY ' . $orderby;
		}
		if ($limit !== null) {
			$sql .= ' LIMIT ' . $start . ', ' . $limit;
		}
		$db = Database::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		return $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->class_name);
	}

	/**
	 * Requires named parameters.
	 * 
	 * @param array $properties
	 * @return int last insert id
	 * @throws Exception row count !== 1
	 */
	protected function insert(array $properties) {
		$params = array();
		foreach ($properties as $key => $value) {
			$params[':' . $key] = $value; // named params
		}
		$sql = 'INSERT INTO ' . $this->table_name;
		$sql .= ' (' . implode(', ', $this->columns) . ')';
		$sql .= ' VALUES (' . implode(', ', array_keys($params)) . ')';
		$db = Database::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		if ($query->rowCount() !== 1) {
			throw new Exception('insert row count: ' . $query->rowCount());
		}
		return intval($db->lastInsertId());
	}

	/**
	 * Requires named parameters.
	 * 
	 * @param string $where
	 * @param array $params by value
	 * @param array $properties
	 * @return int affected rows
	 */
	protected function update($where, array $params, array $properties) {
		$sql = 'UPDATE ' . $this->table_name . ' SET ';
		$fields = array();
		foreach ($properties as $key => $value) {
			if (!array_key_exists($key, $this->primary_key)) { // never change primary key
				$fields[] = $key . ' = :' . $this->class_name . $key;
			}
			$params[':' . $this->class_name . $key] = $value; // named params
		}
		$sql .= implode(', ', $fields);
		$sql .= ' WHERE ' . $where;
		$db = Database::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		return $query->rowCount();
	}

	/**
	 * Optional named parameters.
	 * 
	 * @param string $where
	 * @param array $params
	 * @return int row count
	 */
	protected function delete($where, array $params) {
		$sql = 'DELETE FROM ' . $this->table_name;
		$sql .= ' WHERE ' . $where;
		$db = Database::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		return $query->rowCount();
	}

	/**
	 * Convenience method.
	 * 
	 */
	protected function create_table() {
		$sql = 'CREATE TABLE ' . $this->table_name . ' (';
		$class_name = $this->class_name;
		foreach ($class_name::$persistent_fields as $key => $value) {
			$sql .= $key . ' ' . $value . ', ';
		}
		$sql .= 'PRIMARY KEY (' . implode(', ', $this->primary_key) . ')) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
		$db = Database::instance();
		$query = $db->prepare($sql, array());
		$query->execute(array());
		echo $sql;
		exit;
	}

}

?>