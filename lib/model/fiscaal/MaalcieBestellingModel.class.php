<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaal/MaalcieBestelling.class.php';

class MaalcieBestellingModel extends PersistenceModel {
	const ORM = 'MaalcieBestelling';
	const DIR = 'fiscaal/';
}
