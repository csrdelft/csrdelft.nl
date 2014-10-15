<?php

require_once 'MVC/model/framework/DatabaseAdmin.singleton.php';
require_once 'MVC/model/framework/CachedPersistenceModel.abstract.php';
require_once 'MVC/model/entity/framework/DynamicEntityDefinition.class.php';
require_once 'MVC/model/entity/framework/DynamicEntity.class.php';

/**
 * DynamicEntityModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Defines the DynamicEntity class from the DynamicEntityDefinition table and parent entity class.
 * 
 */
class DynamicEntityModel extends CachedPersistenceModel {

	const orm = 'DynamicEntity';

	protected static $instance;

	protected function __construct() {
		DynamicEntity::$definition = new DynamicEntityDefinition(); // TODO
		parent::__construct('framework');
	}

}
