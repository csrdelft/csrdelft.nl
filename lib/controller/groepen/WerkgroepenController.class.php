<?php
namespace CsrDelft\controller\groepen;

use CsrDelft\model\groepen\WerkgroepenModel;

require_once 'controller/groepen/KetzersController.class.php';

/**
 * WerkgroepenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor werkgroepen.
 * 
 * N.B. Een Werkgroep extends Ketzer, maar de controller niet om de "nieuwe ketzer"-wizard te vermijden.
 */
class WerkgroepenController extends AbstractGroepenController {

	public function __construct($query) {
		parent::__construct($query, WerkgroepenModel::instance());
	}

}
