<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 18-3-18
 * Time: 19:21
 */

namespace CsrDelft\view\commissievoorkeuren;


use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;

class AddCommissieFormulier extends Formulier {

	/**
	 * AddCommissieFormulier constructor.
	 */
	public function __construct($model) {
		parent::__construct($model, "/commissievoorkeuren/nieuwecommissie");
		$this->addFields([new HtmlComment("<h2>Commissie toevoegen</h2>")]);
		$this->addFields([new RequiredTextField("naam", "", "Naam")]);
		$this->addFields([new SubmitKnop()]);
	}
}