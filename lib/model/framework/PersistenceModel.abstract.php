<?php

require_once 'model/framework/Database.singleton.php';
require_once 'model/framework/Persistence.interface.php';
require_once 'model/entity/framework/PersistentEntity.abstract.php';

/**
 * PersistenceModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Uses the database to provide persistence.
 * Requires an ORM class constant to be defined in superclass. 
 * Requires a static property $instance in superclass.
 * 
 */
abstract class PersistenceModel implements Persistence {

	/**
	 * @return $this
	 */
	public static function instance() {
		if (!isset(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	public static function getTableName() {
		return static::instance()->orm->getTableName();
	}

	/**
	 * Do NOT use @ and . in your primary keys or you WILL run into trouble here!
	 * 
	 * @param string $UUID
	 * @return PersistentEntity
	 */
	public static function getUUID($UUID) {
		$parts = explode('@', $UUID, 2);
		$primary_key_values = explode('.', $parts[0]);
		return static::instance()->retrieveByPrimaryKey($primary_key_values);
	}

	/**
	 * Object relational mapping
	 * @var PersistentEntity
	 */
	protected $orm;
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = null;

	protected function __construct($subdir = '') {
		$orm = static::orm;
		require_once 'model/entity/' . $subdir . $orm . '.class.php';
		$orm::__constructStatic(); // extend persistent attributes
		if (DB_CHECK) {
			require_once 'model/framework/DatabaseAdmin.singleton.php';
			$orm::checkTable();
		}
		$this->orm = new $orm();
	}

	/**
	 * Find existing entities with optional search criteria.
	 * Retrieves all attributes.
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $groupby GROUP BY
	 * @param string $orderby ORDER BY
	 * @param int $limit max amount of results
	 * @param int $start results from index
	 * @return PDOStatement
	 */
	public function find($criteria = null, array $criteria_params = array(), $groupby = null, $orderby = null, $limit = null, $start = 0) {
		if ($orderby == null) {
			$orderby = $this->default_order;
		}
		try {
			$result = Database::sqlSelect(array('*'), $this->orm->getTableName(), $criteria, $criteria_params, $groupby, $orderby, $limit, $start);
			$result->setFetchMode(PDO::FETCH_CLASS, static::orm, array($cast = true));
			return $result;
		} catch (PDOException $ex) {
			fatal_handler($ex);
		}
	}

	/**
	 * Find existing entities with optional search criteria.
	 * Retrieves only requested attributes and the primary key values.
	 * 
	 * @param array $attributes to retrieve
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $groupby GROUP BY
	 * @param string $orderby ORDER BY
	 * @param int $limit max amount of results
	 * @param int $start results from index
	 * @return PDOStatement
	 */
	public function findSparse(array $attributes, $criteria = null, array $criteria_params = array(), $groupby = null, $orderby = null, $limit = null, $start = 0) {
		if ($orderby == null) {
			$orderby = $this->default_order;
		}
		$attributes = array_merge($this->orm->getPrimaryKey(), $attributes);
		$result = Database::sqlSelect($attributes, $this->orm->getTableName(), $criteria, $criteria_params, $groupby, $orderby, $limit, $start);
		$result->setFetchMode(PDO::FETCH_CLASS, static::orm, array($cast = true, $attributes));
		return $result;
	}

	/**
	 * Count existing entities with optional criteria.
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @return int count
	 */
	public function count($criteria = null, array $criteria_params = array()) {
		$result = Database::sqlSelect(array('COUNT(*)'), $this->orm->getTableName(), $criteria, $criteria_params);
		return (int) $result->fetchColumn();
	}

	/**
	 * Check if entities with optional search criteria exist.
	 * 
	 * @param string $criteria
	 * @param array $criteria_params
	 * @return boolean entities with search criteria exist
	 */
	public function exist($criteria = null, array $criteria_params = array()) {
		return Database::sqlExists($this->orm->getTableName(), $criteria, $criteria_params);
	}

	/**
	 * Check if enitity exists.
	 * 
	 * @param PersistentEntity $entity
	 * @return boolean entity exists
	 */
	public function exists(PersistentEntity $entity) {
		return $this->existsByPrimaryKey($entity->getValues(true));
	}

	/**
	 * Check if enitity with primary key exists.
	 * 
	 * @param array $primary_key_values
	 * @return boolean primary key exists
	 */
	protected function existsByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->orm->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		return $this->exist(implode(' AND ', $where), $primary_key_values);
	}

	/**
	 * Save new entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return string last insert id
	 */
	public function create(PersistentEntity $entity) {
		return Database::sqlInsert($entity->getTableName(), $entity->getValues());
	}

	/**
	 * Load saved enitity data and replace entity object.
	 * 
	 * @WARNING: returns new object!
	 * @see retrieveAttributes
	 * 
	 * Todo: something clever with references
	 * 
	 * @param PersistentEntity $entity
	 * @return PersistentEntity|false
	 */
	public function retrieve(PersistentEntity $entity) {
		return $this->retrieveByPrimaryKey($entity->getValues(true));
	}

	/**
	 * Load saved entity data and create new object.
	 * 
	 * @param array $primary_key_values
	 * @return PersistentEntity|false
	 */
	protected function retrieveByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->orm->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		$result = Database::sqlSelect(array('*'), $this->orm->getTableName(), implode(' AND ', $where), $primary_key_values, null, null, 1);
		return $result->fetchObject(static::orm, array($cast = true));
	}

	/**
	 * Retrieve the value of sparse attributes.
	 * 
	 * Usage example:
	 * 
	 * $users = UserModel::instance()->findSparse(array('naam'), ...); // retrieves only naam attribute
	 * foreach ($users as $user) {
	 *   echo $user->getAddress(); // address is sparse: retrieve address
	 * }
	 * class User {
	 * public function getAddress() {
	 *   $attr = array('city' 'street', 'number', 'postalcode');
	 *   UserModel::instance()->retrieveAttributes($this, $attr);
	 *   $this->castValues($attr); // because PDO does not do this automatically (yet)
	 *   $this->attr_retrieved = array_merge($this->attr_retrieved, $attr); // bookkeeping
	 * }
	 * 
	 * Wrong usage: forget to register retrieved atributes and to cast the values, problematic for $entity->getValues() and $entity->isSparse()
	 * 
	 * $model = UserModel::instance();
	 * $user = $model->getUser($uid); // retrieves non-sparse attributes
	 * $model->retrieveAttributes($user, array('city' 'street', 'number', 'postalcode')); // suppose address is sparse: retrieve address
	 * echo ...
	 * 
	 * @param PersistentEntity $entity
	 * @param array $attributes
	 * @return mixed false on failure
	 */
	public function retrieveAttributes(PersistentEntity $entity, array $attributes) {
		$where = array();
		foreach ($entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		$result = Database::sqlSelect($attributes, $entity->getTableName(), implode(' AND ', $where), $entity->getValues(true), null, null, 1);
		$result->setFetchMode(PDO::FETCH_INTO, $entity);
		return $result->fetch();
	}

	/**
	 * Save existing entity.
	 * Sparse attributes that have not been retrieved are excluded by PersistentEntity->getValues().
	 *
	 * @param PersistentEntity $entity
	 * @return int rows affected
	 */
	public function update(PersistentEntity $entity) {
		$properties = $entity->getValues();
		$where = array();
		$params = array();
		foreach ($entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = :W' . $key; // name parameters after column
			$params[':W' . $key] = $properties[$key];
			unset($properties[$key]); // do not update primary key
		}
		return Database::sqlUpdate($entity->getTableName(), $properties, implode(' AND ', $where), $params, 1);
	}

	/**
	 * Remove existing entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return boolean rows affected
	 */
	public function delete(PersistentEntity $entity) {
		return $this->deleteByPrimaryKey($entity->getValues(true));
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_key_values
	 * @return boolean rows affected
	 */
	protected function deleteByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->orm->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		return Database::sqlDelete($this->orm->getTableName(), implode(' AND ', $where), $primary_key_values, 1);
	}

}
