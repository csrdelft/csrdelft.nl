<?php

/**
 * Persistence.interface.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Generic CRUD.
 * 
 */
interface Persistence {

	public function create(PersistentEntity $entity);

	public function retrieve(PersistentEntity $entity);

	public function update(PersistentEntity $entity);

	public function delete(PersistentEntity $entity);
}
