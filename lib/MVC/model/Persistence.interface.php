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

	function create(PersistentEntity $entity);

	function retrieve(PersistentEntity $entity);

	function update(PersistentEntity $entity);

	function delete(PersistentEntity $entity);
}
