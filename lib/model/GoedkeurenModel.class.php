<?php

require_once 'model/PrullenbakModel.class.php';

/**
 * GoedkeurenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class GoedkeurenModel extends PrullenbakModel {

	const ORM = 'ApproveEntity';

	protected static $instance;

	public static function get($object_id) {
		return static::instance()->retrieveByPrimaryKey(array($object_id));
	}

	protected static function getAttributesModel() {
		return ApproveAttributesModel::instance();
	}

	protected function makeApproveEntity($model_class, array $attribute_values, $permission_approve = null, $permission_delete = null, $queued_by_uid = null, $queued_moment = null, $parent_id = null) {
		$entity = new ApproveEntity();
		$entity->parent_id = $parent_id;
		$entity->model_class = $model_class;
		$entity->queued_moment = isGeldigeDatum($queued_moment) ? $queued_moment : getDateTime();
		$entity->queued_by_uid = AccountModel::isValidUid($queued_by_uid) ? $queued_by_uid : LoginModel::getUid();
		$entity->permission_approve = $permission_approve ? $permission_approve : LoginModel::getUid();
		$entity->permission_delete = $permission_delete ? $permission_delete : LoginModel::getUid();
		$entity->object_id = $this->create($entity);
		// Create queued attributes
		$attributes_model = static::getAttributesModel();
		foreach ($attribute_values as $attribute_name => $value) {
			$attributes_model->makeApproveAttribute($entity->object_id, $attribute_name, $value);
		}
		return $entity;
	}

	/**
	 * Add an entity to the approve queue.
	 *
	 * @param PersistenceModel $model
	 * @param PersistentEntity $entity
	 * @param string $permission_approve
	 * @param string $permission_delete
	 * @param string $queued_by_uid
	 * @param string $queued_moment
	 */
	public function queue(PersistenceModel $model, PersistentEntity $entity, $permission_approve = null, $permission_delete = null, $queued_by_uid = null, $queued_moment = null) {
		// Children first, if any
		if ($entity instanceof TreeNode AND $entity->hasChildren()) {
			throw new Exception('TreeNode has children: use removeRecursive');
		}
		// Create approve entity
		$this->makeApproveEntity(get_class($model), $entity->getValues(), $permission_approve, $permission_delete, $queued_by_uid, $queued_moment);
		// Delete from original model
		$model->delete($entity);
	}

	/**
	 * Add an entity to the approve queue and all of its children, if any.
	 *
	 * @param PersistenceModel $model
	 * @param PersistentEntity $entity
	 * @param string $permission_approve
	 * @param string $permission_delete
	 * @param string $queued_by_uid
	 * @param string $queued_moment
	 * @parem int $parent_id
	 */
	public function queueRecursive(PersistenceModel $model, PersistentEntity $entity, $permission_approve = null, $permission_delete = null, $queued_by_uid = null, $queued_moment = null, $parent_id = null) {
		// Create queued entity
		$parent = $this->makeApproveEntity(get_class($model), $entity->getValues(), $permission_approve, $permission_delete, $queued_by_uid, $queued_moment, $parent_id);
		// Remove children, if any
		if ($entity instanceof TreeNode AND $entity->hasChildren()) {
			foreach ($entity->getChildren() as $child) {
				$this->queueRecursive($model, $child, $permission_approve, $permission_delete, $queued_by_uid, $queued_moment, $parent->object_id);
			}
		}
		// Delete from original model
		$model->deleteRecursive($entity);
	}

	/**
	 * Approve entity.
	 *
	 * @param PersistentEntity $approve_entity
	 * @throws Exception if approve fails
	 */
	public function approve(PersistentEntity $approve_entity, $callback = 'onApproved') {
		$this->restore($approve_entity, $callback);
	}

	/**
	 * Retore queued entity and all of its children, if any.
	 *
	 * @param PersistentEntity $approve_entity
	 * @throws Exception if approve fails
	 */
	public function approveRecursive(PersistentEntity $approve_entity, $callback = 'onApproved') {
		$this->approve($approve_entity, $callback);
		// Approve children, if any
		if ($approve_entity instanceof TreeNode AND $approve_entity->hasChildren()) {
			foreach ($approve_entity->getChildren() as $child) {
				$this->approveRecursive($child, $callback);
			}
		}
	}

}

class ApproveAttributesModel extends RemovedAttributesModel {

	const ORM = 'ApproveAttribute';

	protected static $instance;

	public function makeApproveAttribute($approve_object_id, $attribute_name, $value) {
		$attribute = new ApproveAttribute();
		$attribute->object_id = $approve_object_id;
		$attribute->name = $attribute_name;
		$attribute->value = $value;
		$this->create($attribute);
		return $attribute;
	}

}
