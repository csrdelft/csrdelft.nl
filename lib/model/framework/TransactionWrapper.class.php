<?php

/**
 * TransactionWrapper.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Wraps PersistenceModel methods inside a PDO database transaction,
 * if not already in one.
 *
 */
class TransactionWrapper {

	/**
	 * Model wrapped inside
	 * @var PersistenceModel
	 */
	private $model;

	function __construct(PersistenceModel $model) {
		$this->model = $model;
	}

	/**
	 * Wrap any method in a transaction, if not already in one.
	 * Rolls back the transaction if any exception is thrown and re-throws it;
	 * commits the transaction otherwise.
	 *
	 * @param string $method_name
	 * @param array $args
	 * @return mixed
	 * @throws Exception
	 */
	function __call($method_name, $args) {
		if (!method_exists($this->model, $method_name)) {
			throw new Exception('Method does not exist: ' . $method_name . ' in ' . get_class($this->model));
		}
		// Begin transaction, if not already in one
		$db = Database::instance();
		$beganTransaction = false;
		if (!$db->inTransaction()) {
			$db->beginTransaction();
			$beganTransaction = true;
		}
		try {
			// Invoke model method
			$return = call_user_func_array(array($this->model, $method_name), $args);
			// Commit transaction, if started in this function __call
			if ($beganTransaction) {
				$db->commit();
			}
			return $return;
		} catch (Exception $ex) {
			// Roll back transaction, if started in this function __call
			if ($beganTransaction) {
				$db->rollBack();
			}
			throw $ex; // rethrow to caller
		}
	}

}
