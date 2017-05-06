<?php
namespace CsrDelft\model\groepen;
use CsrDelft\model\entity\groepen\Werkgroep;

require_once 'model/groepen/KetzersModel.class.php';

class WerkgroepenModel extends KetzersModel {

	const ORM = Werkgroep::class;

	protected static $instance;

}
