<?php

/**
 * ApproveEntity.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ApproveEntity extends PersistentEntity implements TreeNode {

	/**
	 * Primary key
	 * @var int
	 */
	public $object_id;
	/**
	 * Parent approve entity
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
	public $queued_moment;
	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 */
	public $queued_by_uid;
	/**
	 * Permission required to approve entity
	 * @var string
	 */
	public $permission_approve;
	/**
	 * Permission required to deny entity
	 * @var string
	 */
	public $permission_deny;
	/**
	 * Lazy loading by foreign key
	 * @var ApproveEntity[]
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
		'queued_moment'		 => array(T::String),
		'queued_by_uid'		 => array(T::UID),
		'permission_approve' => array(T::String),
		'permission_deny'	 => array(T::String)
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
	protected static $table_name = 'approve_objects';

	public function hasParent() {
		return $this->parent_id != null;
	}

	public function getParent() {
		return ApproveModel::instance()->find('object_id = ?', array($this->parent_id), null, null, 1)->fetch();
	}

	public function hasChildren() {
		$this->getChildren();
		return !empty($this->children);
	}

	public function getChildren() {
		if (!isset($this->children)) {
			$this->children = ApproveModel::instance()->find('parent_id = ?', array($this->object_id))->fetchAll();
		}
		return $this->children;
	}

}
