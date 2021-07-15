<?php


namespace CsrDelft\view\streeplijst;


use CsrDelft\entity\Streeplijst;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;
use CsrDelft\view\formulier\ModalForm;

class StreeplijstForm extends ModalForm
{
	/**
	 * StreeplijstForm constructor.
	 * @param $model Streeplijst
	 * @param $action
	 */
	public function __construct($model)
	{
		parent::__construct( $model, "/streeplijst/aanmaken");
//		$fields[] = new TextareaField('inhoud_streeplijst', $model->inhoud_streeplijst, 'Inhoud van de streeplijst');
//		$this->addFields($fields);
//		$this->formKnoppen = new FormDefaultKnoppen();
		$this->addFields([new HtmlComment("<h2>Inhoud toevoegen</h2>")]);
		$this->addFields([new RequiredTextField("inhoud", "", "Inhoud")]);
		$this->formKnoppen->addKnop(new SubmitKnop());
	}
}
