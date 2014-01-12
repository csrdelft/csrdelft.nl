<?php

require_once 'MVC/model/Database.class.php';
require_once 'MVC/model/Persistence.interface.php';

/**
 * PersistenceModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Uses database to provide persistence.
 * 
 */
abstract class PersistenceModel extends Database implements Persistence {

	abstract function create(PersistentEntity &$entity);

	function retrieve(PersistentEntity &$entity) {
		$select = PersistentEntity::getFields();
		$where = '';
		$params = array();
		foreach (PersistentEntity::getPrimaryKey() as $key) {
			$where .= $key . ' = ?';
			$params[] = $entity->$key;
		}
		$result = parent::select($select, PersistentEntity::getTableName(), $where, $params, null, 1);
		$result->fetch(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, get_class($entity));
	}

	abstract function update(PersistentEntity &$entity);

	abstract function delete(PersistentEntity &$entity);

	/**
	 * Optional named parameters.
	 * 
	 * @param string $where
	 * @param array $params
	 * @return PersistentEntity
	 * @throws Exception row count !== 1
	 */
	protected function get($where, array $params) {
		$one = $this->load($where, $params, null, 1);
		if (sizeof($one) !== 1) {
			throw new Exception('getEntity row count: ' . sizeof($one));
		}
		return reset($one);
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

		return $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->class_name);
	}

	/**
	 * Requires named parameters.
	 * 
	 * @param array $properties
	 * @return int last insert id
	 * @throws Exception row count !== 1
	 */
	protected function nieuw(array $properties) {

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
	 * @param array $properties
	 * @param string $where
	 * @param array $params
	 * @return int affected rows
	 */
	protected function wijzig(array $properties, $where, array $params) {
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
	protected function verwijder($where, array $params) {
		$sql = 'DELETE FROM ' . $this->table_name;
		$sql .= ' WHERE ' . $where;
		$db = Database::instance();
		$query = $db->prepare($sql, $params);
		$query->execute($params);
		return $query->rowCount();
	}

}

?>