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
	protected $table;
	protected $columns;

	public function __construct($class, $table = null) {
		$this->class = $class;
		if ($table === null) {
			$this->table = $this->class;
		}
		$this->columns = array_keys(get_class_vars($class));
	}

	abstract public function getOne($primary_key);

	abstract public function getAll($where = null, array $values = array(), $assoc = false);

	abstract public function save($entity);

	/**
	 * Optional named parameters.
	 * 
	 * @param string $where
	 * @param array $values
	 * @param type $orderby
	 * @param type $limit
	 * @param type $start
	 * @return type
	 */
	protected function load($where = null, array $values = array(), $orderby = null, $limit = null, $start = 0) {
		$sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table;
		if ($where !== null) {
			$sql .= ' WHERE ' . $where;
		}
		if ($orderby !== null) {
			$sql .= ' ORDER BY ' . $orderby;
		}
		if ($limit !== null) {
			$sql .= ' LIMIT ' . $start . ', ' . $limit;
		}
		$db = CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		return $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->class);
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
		$sql = 'INSERT INTO ' . $this->table;
		$sql .= ' (' . implode(', ', array_keys($properties)) . ')';
		$sql .= ' VALUES (' . implode(', ', array_keys($params)) . ')';
		$db = CsrPdo::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		if ($query->rowCount() !== 1) {
			throw new Exception('Insert row count: ' . $query->rowCount());
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
		$sql = 'UPDATE ' . $this->table . ' SET ';
		$fields = array();
		foreach ($properties as $key => $value) {
			$fields[] = $key . ' = :' . $this->class . $key;
			$params[':' . $this->class . $key] = $value; // named params
		}
		$sql .= implode(', ', $fields);
		$sql .= ' WHERE ' . $where;
		$db = CsrPdo::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		return $query->rowCount();
	}

	/**
	 * Optional named parameters.
	 * 
	 * @param string $where
	 * @param array $params
	 * @return type
	 */
	protected function delete($where, array $params) {
		$sql = 'DELETE FROM ' . $this->table;
		$sql .= ' WHERE ' . $where;
		$db = CsrPdo::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		return $query->rowCount();
	}

	/**
	 * Single or multiple columns as primary key.
	 * 
	 * @param array $primary_key
	 */
	protected function create_table(array $primary_key) {
		$sql = 'CREATE TABLE ' . $this->table . ' (';
		$fields = get_class_vars($this->class);
		foreach ($fields as $key => $value) {
			$sql .= $key . ' ' . $value . ', ';
		}
		$sql = ') PRIMARY KEY (' . implode(', ', $primary_key) . ') ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
		$db = CsrPdo::instance();
		$query = $db->prepare($sql, array());
		$query->execute(array());
	}

}

?>