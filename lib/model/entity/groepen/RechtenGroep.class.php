<?php
use CsrDelft\Orm\Entity\T;

/**
 * RechtenGroep.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een groep beperkt voor rechten.
 */
class RechtenGroep extends AbstractGroep {

	const LEDEN = 'RechtenGroepLedenModel';

	/**
	 * Rechten benodigd voor aanmelden
	 * @var string
	 */
	public $rechten_aanmelden;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'rechten_aanmelden' => array(T::String)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'groepen';

	public function getUrl() {
		return '/groepen/overig/' . $this->id . '/';
	}

	/**
	 * Has permission for action?
	 * 
	 * @param AccessAction $action
	 * @return boolean
	 */
	public function mag($action) {
		switch ($action) {

			case AccessAction::BEKIJKEN:
			case AccessAction::AANMELDEN:
			case AccessAction::BEWERKEN:
			case AccessAction::AFMELDEN:
				if (!LoginModel::mag($this->rechten_aanmelden)) {
					return false;
				}
				break;
		}
		return parent::mag($action);
	}

}
