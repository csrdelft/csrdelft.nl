<?php

require_once 'model/framework/Database.singleton.php';
require_once 'model/framework/TransactionWrapper.class.php';
require_once 'model/framework/Persistence.interface.php';
require_once 'model/entity/framework/TreeNode.interface.php';
require_once 'model/entity/framework/PersistentEntity.abstract.php';

/**
 * PersistenceModel.abstract.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Uses the database to provide persistence.
 * Requires an ORM class constant to be defined in superclass. 
 * Requires a static property $instance in superclass.
 * Optional DIR class constant for location of ORM class.
 *
 */
abstract class PersistenceModel implements Persistence {

	public static function __static() {
		$orm = static::ORM;
		if (defined('static::DIR')) {
			$dir = static::DIR;
		} else {
			$dir = '';
		}
		require_once 'model/entity/' . $dir . $orm . '.class.php';
		$orm::__static(); // Extend the persistent attributes
		if (DB_CHECK) {
			require_once 'model/framework/DatabaseAdmin.singleton.php';
			$orm::checkTable();
		}
	}

	/**
	 * This has to be called once before using static methods due to
	 * static constructor emulation.
	 *
	 * @return $this
	 */
	public static function instance() {
		if (!isset(static::$instance)) {
			// Unfortunately PHP does not support static constructors
			static::__static(); // This is the next best thing for now
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Wrap all method calls to this model inside a database transaction.
	 *
	 * @return TransactionWrapper
	 */
	public static function transaction() {
		return new TransactionWrapper(static::instance());
	}

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = null;
	/**
	 * Object relational mapping
	 * @var PersistentEntity
	 */
	private $orm;

	protected function __construct() {
		$orm = static::ORM;
		$this->orm = new $orm();
	}

	public function getTableName() {
		return $this->orm->getTableName();
	}

	/**
	 * Get all attribute names.
	 *
	 * @return array
	 */
	public function getAttributes() {
		return $this->orm->getAttributes();
	}

	public function getAttributeDefinition($attribute_name) {
		return $this->orm->getAttributeDefinition($attribute_name);
	}

	public function getPrimaryKey() {
		return $this->orm->getPrimaryKey();
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
	 * @return PDOStatement implements Traversable using foreach does NOT require ->fetchAll()
	 */
	public function find($criteria = null, array $criteria_params = array(), $groupby = null, $orderby = null, $limit = null, $start = 0) {
		if ($orderby == null) {
			$orderby = $this->default_order;
		}
		try {
			$result = Database::sqlSelect(array('*'), $this->getTableName(), $criteria, $criteria_params, $groupby, $orderby, $limit, $start);
			$result->setFetchMode(PDO::FETCH_CLASS, static::ORM, array($cast = true));
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
	 * @return PDOStatement implements Traversable using foreach does NOT require ->fetchAll()
	 */
	public function findSparse(array $attributes, $criteria = null, array $criteria_params = array(), $groupby = null, $orderby = null, $limit = null, $start = 0) {
		if ($orderby == null) {
			$orderby = $this->default_order;
		}
		$attributes = array_merge($this->getPrimaryKey(), $attributes);
		$result = Database::sqlSelect($attributes, $this->getTableName(), $criteria, $criteria_params, $groupby, $orderby, $limit, $start);
		$result->setFetchMode(PDO::FETCH_CLASS, static::ORM, array($cast = true, $attributes));
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
		$result = Database::sqlSelect(array('COUNT(*)'), $this->getTableName(), $criteria, $criteria_params);
		return (int) $result->fetchColumn();
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
		foreach ($this->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		return Database::sqlExists($this->getTableName(), implode(' AND ', $where), $primary_key_values);
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
	 * Load saved enitity data and replace current entity object values.
	 *
	 * @see retrieveAttributes
	 *
	 * @param PersistentEntity $entity
	 * @return PersistentEntity|false
	 */
	public function retrieve(PersistentEntity $entity) {
		return $this->retrieveAttributes($entity, $entity->getAttributes());
	}

	/**
	 * Load saved entity data and create new object.
	 *
	 * @param array $primary_key_values
	 * @return PersistentEntity|false
	 */
	protected function retrieveByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		$result = Database::sqlSelect(array('*'), $this->getTableName(), implode(' AND ', $where), $primary_key_values, null, null, 1);
		return $result->fetchObject(static::ORM, array($cast = true));
	}

	/**
	 * Do NOT use @ and . in your primary keys or you WILL run into trouble here!
	 *
	 * @param string $UUID
	 * @return PersistentEntity|false
	 */
	public function retrieveByUUID($UUID) {
		$parts = explode('@', $UUID, 2);
		$primary_key_values = explode('.', $parts[0]);
		return $this->retrieveByPrimaryKey($primary_key_values);
	}

	/**
	 * Retrieve the value of sparse attributes.
	 *
	 * Usage example:
	 *
	 * $model = UserModel::instance();
	 * $users = $model->findSparse(array('naam'), ...); // retrieves only naam attribute
	 * foreach ($users as $user) {
	 *   echo $user->getAddress(); // address is sparse: retrieve address
	 * }
	 *
	 * class User extends PersitentEntity {
	 *   public function getAddress() {
	 *     $attributes = array('city' 'street', 'number', 'postalcode');
	 *     UserModel::instance()->retrieveAttributes($this, $attributes);
	 *   }
	 * }
	 *
	 * Foreign key example:
	 *
	 * $user->getAddress();
	 *
	 * class User extends PersitentEntity {
	 *   public $address_uuid; // foreign key
	 *   public $address;
	 *   public function getAddress() {
	 *     if (!isset($this->address)) {
	 *       $fk = array('address_uuid')
	 *       if ($this->isSparse($fk) {
	 *         UserModel::instance()->retrieveAttributes($this, $fk);
	 *       }
	 *       $this->address = AddressesModel::instance()->retrieveByUUID($this->address_uuid);
	 *     }
	 *     return $this->address;
	 *   }
	 * }
	 *
	 * @param PersistentEntity $entity
	 * @param array $attributes
	 * @return PersistentEntity|false
	 */
	public function retrieveAttributes(PersistentEntity $entity, array $attributes) {
		$where = array();
		foreach ($entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		$result = Database::sqlSelect($attributes, $entity->getTableName(), implode(' AND ', $where), $entity->getValues(true), null, null, 1);
		$result->setFetchMode(PDO::FETCH_INTO, $entity);
		$success = $result->fetch();
		if ($success) {
			$entity->onAttributesRetrieved($attributes);
		}
		return $success;
	}

	/**
	 * Save existing entity.
	 * Sparse attributes that have not been retrieved are excluded by PersistentEntity->getValues().
	 *
	 * @param PersistentEntity $entity
	 * @return int number of rows affected
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
	 * Delete existing entity.
	 *
	 * @param PersistentEntity $entity
	 * @return int number of rows affected
	 */
	public function delete(PersistentEntity $entity) {
		return $this->deleteByPrimaryKey($entity->getValues(true));
	}

	/**
	 * Delete existing entity and all of its children, if any.
	 *
	 * @param PersistentEntity $entity
	 * @return int number of rows affected
	 */
	public function deleteRecursive(PersistentEntity $entity) {
		$rowCount = 0;
		// Delete children, if any
		if ($entity instanceof TreeNode AND $entity->hasChildren()) {
			foreach ($entity->getChildren() as $child) {
				$rowCount += $this->deleteRecursive($child);
			}
		}
		return $rowCount + $this->delete($entity);
	}

	/**
	 * Requires positional values.
	 *
	 * @param array $primary_key_values
	 * @return int number of rows affected
	 */
	protected function deleteByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		return Database::sqlDelete($this->getTableName(), implode(' AND ', $where), $primary_key_values, 1);
	}

}
