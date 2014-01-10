<?php

/**
 * ReflectionModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * CRUD by means of PHP Reflection.
 * 
 */
abstract class ReflectionModel {

	private $class;
	private $table;
	private $columns;

	public function __construct($class, $table = null) {
		$this->class = $class;
		if ($table === null) {
			$this->table = $this->class;
		}
		$this->columns = array_keys(get_class_vars($class));
	}

	abstract public function getOne($primary_key);

	abstract public function getAll($where = null, array $values = array(), $assoc = false);

	protected function load($where = null, array $values = array(), $orderby = null, $start = 0, $limit = 100) {
		$sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table;
		if ($where !== null) {
			$sql .= ' WHERE ' . $where;
		}
		if ($orderby !== null) {
			$sql .= ' ORDER BY ' . $orderby;
		}
		$sql .= ' LIMIT ' . $start . ', ' . $limit;
		$db = & CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		return $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->class);
	}

	abstract public function save($entity);

	protected function insert(array $properties) {
		$params = array();
		foreach ($properties as $key => $value) {
			$params[':' . $key] = $value; // named params
		}
		$sql = 'INSERT INTO ' . $this->table;
		$sql .= ' (' . implode(', ', array_keys($properties)) . ')';
		$sql .= ' VALUES (' . implode(', ', array_keys($params)) . ')';
		$db = & CsrPdo::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		if ($query->rowCount() !== 1) {
			throw new Exception('Insert row count: ' . $query->rowCount());
		}
		return intval($db->lastInsertId());
	}

	protected function update(array $where, array $properties) {
		$sql = 'UPDATE ' . $this->table . ' SET ';
		$params = array();
		$fields = array();
		foreach ($properties as $key => $value) {
			$fields[] = $key . ' = :' . $key;
			$params[':' . $key] = $value; // named params
		}
		$sql .= implode(', ', $fields);
		$sql .= ' WHERE ';
		$fields = array();
		foreach ($where as $key => $value) {
			$fields[] = $key . ' = :where' . $key;
			$params[':where' . $key] = $value; // named params
		}
		$sql .= implode(', ', $fields);
		$db = & CsrPdo::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		return $query->rowCount();
	}

	public function delete($where, array $params) {
		$sql = 'DELETE FROM ' . $this->table;
		$sql .= ' WHERE ' . $where;
		$db = & CsrPdo::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		return $query->rowCount();
	}

	private function create_table() {
		
	}

	private function drop_table() {
		
	}

}

?>