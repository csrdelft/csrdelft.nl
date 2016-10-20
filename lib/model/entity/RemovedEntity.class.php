<?php

/**
 * RemovedEntity.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class RemovedEntity extends PersistentEntity implements TreeNode {

	/**
	 * Primary key
	 * @var int
	 */
	public $object_id;
	/**
	 * Parent removed entity
	 * Foreign key
	 * @var int
	 */
	public $parent_id;
	/**
	 * Class name of model
	 * @var string
	 */
	public $model_class;
	/**
	 * DateTime
	 * @var string
	 */
	public $removed_moment;
	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 */
	public $removed_by_uid;
	/**
	 * Permission required to restore removed entity
	 * @var string
	 */
	public $permission_restore;
	/**
	 * Permission required to permanently delete entity
	 * @var string
	 */
	public $permission_delete;
	/**
	 * Lazy loading by foreign key
	 * @var RemovedEntity[]
	 */
	private $children;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'object_id'			 => array(T::Integer, false, 'auto_increment'),
		'parent_id'			 => array(T::Integer, true),
		'model_class'		 => array(T::String),
		'removed_moment'	 => array(T::String),
		'removed_by_uid'	 => array(T::UID),
		'permission_restore' => array(T::String),
		'permission_delete'	 => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('object_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'removed_objects';

	public function hasParent() {
		return $this->parent_id != null;
	}

	public function getParent() {
		return PrullenbakModel::instance()->find('object_id = ?', array($this->parent_id), null, null, 1)->fetch();
	}

	public function hasChildren() {
		$this->getChildren();
		return !empty($this->children);
	}

	public function getChildren() {
		if (!isset($this->children)) {
			$this->children = PrullenbakModel::instance()->find('parent_id = ?', array($this->object_id))->fetchAll();
		}
		return $this->children;
	}

}
