<?php

/**
 * PrullenbakModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class PrullenbakModel extends PersistenceModel {

	const ORM = 'RemovedEntity';

	protected static $instance;

	public static function get($object_id) {
		return static::instance()->retrieveByPrimaryKey(array($object_id));
	}

	protected static function getAttributesModel() {
		return RemovedAttributesModel::instance();
	}

	protected function makeRemovedEntity($model_class, array $attribute_values, $permission_restore = null, $permission_delete = null, $removed_by_uid = null, $removed_moment = null, $parent_id = null) {
		$entity = new RemovedEntity();
		$entity->parent_id = $parent_id;
		$entity->model_class = $model_class;
		$entity->removed_moment = isGeldigeDatum($removed_moment) ? $removed_moment : getDateTime();
		$entity->removed_by_uid = AccountModel::isValidUid($removed_by_uid) ? $removed_by_uid : LoginModel::getUid();
		$entity->permission_restore = $permission_restore ? $permission_restore : LoginModel::getUid();
		$entity->permission_delete = $permission_delete ? $permission_delete : LoginModel::getUid();
		$entity->object_id = $this->create($entity);
		// Create removed attributes
		$attributes_model = static::getAttributesModel();
		foreach ($attribute_values as $attribute_name => $value) {
			$attributes_model->makeRemovedAttribute($entity->object_id, $attribute_name, $value);
		}
		return $entity;
	}

	/**
	 * Move an entity to the recycle bin.
	 *
	 * @param PersistenceModel $model
	 * @param PersistentEntity $entity
	 * @param string $permission_restore
	 * @param string $permission_delete
	 * @param string $removed_by_uid
	 * @param string $removed_moment
	 */
	public function remove(PersistenceModel $model, PersistentEntity $entity, $permission_restore = null, $permission_delete = null, $removed_by_uid = null, $removed_moment = null) {
		// Children first, if any
		if ($entity instanceof TreeNode AND $entity->hasChildren()) {
			throw new Exception('TreeNode has children: use recursive ' . __FUNCTION__);
		}
		// Create removed entity
		$this->makeRemovedEntity(get_class($model), $entity->getValues(), $permission_restore, $permission_delete, $removed_by_uid, $removed_moment);
		// Delete from original model
		$model->delete($entity);
	}

	/**
	 * Move an entity to the recycle bin and all of its children, if any.
	 *
	 * @param PersistenceModel $model
	 * @param PersistentEntity $entity
	 * @param string $permission_restore
	 * @param string $permission_delete
	 * @param string $removed_by_uid
	 * @param string $removed_moment
	 * @parem int $parent_id
	 */
	public function removeRecursive(PersistenceModel $model, PersistentEntity $entity, $permission_restore = null, $permission_delete = null, $removed_by_uid = null, $removed_moment = null, $parent_id = null) {
		// Create removed entity
		$parent = $this->makeRemovedEntity(get_class($model), $entity->getValues(), $permission_restore, $permission_delete, $removed_by_uid, $removed_moment, $parent_id);
		// Remove children, if any
		if ($entity instanceof TreeNode AND $entity->hasChildren()) {
			foreach ($entity->getChildren() as $child) {
				$this->removeRecursive($model, $child, $permission_restore, $permission_delete, $removed_by_uid, $removed_moment, $parent->object_id);
			}
		}
		// Delete from original model
		$model->deleteRecursive($entity);
	}

	/**
	 * Restore removed entity.
	 *
	 * @param PersistentEntity $removed_entity
	 * @throws Exception if restore fails
	 */
	public function restore(PersistentEntity $removed_entity, $callback = 'onRestored') {
		// Parent first, if any
		if ($removed_entity instanceof TreeNode AND $removed_entity->hasParent()) {
			throw new Exception('TreeNode has parent: use recursive ' . __FUNCTION__);
		}
		// Get original model
		$model_class = $removed_entity->model_class;
		require_once 'model/' . $model_class . '.class.php';
		$model = $model_class::instance();
		// Create original entity
		$orm = $model::ORM;
		$entity = new $orm();
		// Get removed attributes
		$attributes_model = static::getAttributesModel();
		$removed_attributes = $attributes_model->find('object_id = ?', array($removed_entity->object_id));
		$restored_attributes = array();
		foreach ($removed_attributes as $attribute) {
			$name = $attribute->name;
			if (property_exists($entity, $name)) {
				$entity->$name = $attribute->value;
				$restored_attributes[] = $name;
			} else {
				throw new Exception($orm . ' does not have attribute ' . $name);
			}
		}
		$entity->onAttributesRetrieved($restored_attributes);
		// Save original entity
		$created = $model->create($entity);
		if (!$created) {
			throw new Exception($model_class . ' failed to re-create ' . $orm . ' (' . $created . ')');
		}
		// Delete from recycle bin
		$this->delete($removed_entity);
		// Notify original model
		if (method_exists($model, $callback)) {
			call_user_func_array(array($model, $callback), array($entity));
		}
	}

	/**
	 * Retore removed entity and all of its children, if any.
	 *
	 * @param PersistentEntity $removed_entity
	 * @throws Exception if restore fails
	 */
	public function restoreRecursive(PersistentEntity $removed_entity, $callback = 'onRestored') {
		$this->restore($removed_entity, $callback);
		// Restore children, if any
		if ($removed_entity instanceof TreeNode AND $removed_entity->hasChildren()) {
			foreach ($removed_entity->getChildren() as $child) {
				$this->restoreRecursive($child, $callback);
			}
		}
	}

	/**
	 * Delete removed entity.
	 *
	 * @param PersistentEntity $entity
	 * @return int number of rows affected
	 */
	public function delete(PersistentEntity $entity) {
		// Children first, if any
		if ($entity instanceof TreeNode AND $entity->hasChildren()) {
			throw new Exception('TreeNode has children: use recursive ' . __FUNCTION__);
		}
		// Delete attributes
		$attributes_model = static::getAttributesModel();
		$removed_attributes = $attributes_model->find('object_id = ?', array($entity->object_id));
		foreach ($removed_attributes as $attribute) {
			$attributes_model->delete($attribute);
		}
		return parent::delete($entity);
	}

}

class RemovedAttributesModel extends PersistenceModel {

	const ORM = 'RemovedAttribute';

	protected static $instance;

	public function makeRemovedAttribute($removed_object_id, $attribute_name, $value) {
		$attribute = new RemovedAttribute();
		$attribute->object_id = $removed_object_id;
		$attribute->name = $attribute_name;
		$attribute->value = $value;
		$this->create($attribute);
		return $attribute;
	}

}
