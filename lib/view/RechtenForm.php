<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 13-6-18
 * Time: 23:35
 */

namespace CsrDelft\view;

use CsrDelft\entity\security\AccessControl;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\required\RequiredRechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class RechtenForm extends ModalForm
{
	/**
	 * RechtenForm constructor.
	 * @param AccessControl $ac
	 * @param $action
	 */
	public function __construct(AccessControl $ac, $action)
	{
		parent::__construct(
			$ac,
			'/rechten/' . $action . '/' . $ac->environment . '/' . $ac->resource,
			'Rechten aanpassen voor ',
			true
		);
		if ($ac->resource === '*') {
			$this->titel .= 'elke ' . $ac->environment;
		} else {
			$this->titel .= $ac->environment . ' ' . $ac->resource;
		}

		if ($action === 'aanmaken') {
			if (LoginService::mag(P_ADMIN)) {
				$fields[] = new RequiredTextField(
					'environment',
					$ac->environment,
					'Klasse'
				);
				$fields[] = new RequiredTextField('resource', $ac->resource, 'Object');
			}

			$acties = [];
			foreach (AccessAction::getEnumValues() as $option) {
				$acties[$option] = AccessAction::from($option)->getDescription();
			}
			$fields[] = new SelectField('action', $ac->action, 'Actie', $acties);
		} else {
			$fields[] = new HtmlComment(
				'<label>Actie</label><div class="dikgedrukt">' .
					AccessAction::from($ac->action)->getDescription() .
					'</div>'
			);
		}
		$fields[] = new RequiredRechtenField(
			'subject',
			$ac->subject,
			'Toegestaan voor'
		);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
