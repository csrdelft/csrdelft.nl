<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 18-3-18
 * Time: 18:16
 */

namespace CsrDelft\view\commissievoorkeuren;


use CsrDelft\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;

class CommissieVoorkeurPraesesOpmerkingForm extends Formulier {

	/**
	 * CommissieVoorkeurOpmerkingForm constructor.
	 * @param VoorkeurOpmerking $model
	 */
	public function __construct(VoorkeurOpmerking $model) {
		parent::__construct($model, '/commissievoorkeuren/lidpagina/' . $model->uid);
		$this->addFields([new TextareaField("praesesOpmerking", $model->praesesOpmerking, "Opmerking van praeses")]);

		$this->formKnoppen->addKnop(new SubmitKnop(null, 'submit', 'Opslaan'));
	}
}
