<?php

require_once 'MVC/model/framework/DatabaseAdmin.singleton.php';
require_once 'MVC/model/framework/CachedPersistenceModel.abstract.php';
require_once 'MVC/model/entity/framework/DynamicEntityDefinition.class.php';
require_once 'MVC/model/entity/framework/DynamicEntity.class.php';

/**
 * DynamicPersistenceModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Builds the DynamicEntity class with the attributes from the database table
 * defined as defined by the DynamicEntityDefinition.
 * 
 */
class DynamicPersistenceModel extends CachedPersistenceModel {

	const orm = 'DynamicEntity';

	protected static $instance;

	protected function __construct() {
		DynamicEntity::$definition = new DynamicEntityDefinition(); // TODO
		parent::__construct('framework');
	}

}
