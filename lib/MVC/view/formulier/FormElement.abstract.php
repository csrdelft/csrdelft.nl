<?php

/**
 * FormElement.abstract.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Dit is een poging om maar op één plek dingen voor een formulier te defenieren:
 *  - validatorfuncties
 *  - Html voor de velden, inclusief bijbehorende javascript.
 *  - suggesties voor formuliervelden
 * 
 * Alle elementen die in een formulier terecht kunnen komen stammen af van
 * de class FormElement.
 * 
 * FormElement
 *  - InputField					Elementen die data leveren
 *  - FileField						Bestand upload ketzer
 *  - HtmlComment					Uitleg/commentaar in een formulier stoppen
 *  - FormulierKnop					Submitten, resetten en custom functies van het formulier
 * 
 * Uitbreidingen van HtmlComment:
 * 		- HtmlComment				invoer wordt als html weergegeven
 * 		- BBComment					invoer wordt als bbcode geparsed
 * 		- Subkopje					invoer wordt als <h3> weergegeven
 * 
 */
interface FormElement extends View {

	public function getModel();

	public function getType();

	public function getJavascript();
}

/**
 * Commentaardingen voor formulieren
 */
class HtmlComment implements FormElement {

	protected $comment;

	public function __construct($comment) {
		$this->comment = $comment;
	}

	public function getModel() {
		return $this->comment;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function view() {
		echo $this->comment;
	}

	public function getJavascript() {
		return '';
	}

	public function getTitel() {
		return $this->getType();
	}

	public function getType() {
		return get_class($this);
	}

}

class BBComment extends HtmlComment {

	public function view() {
		echo CsrBB::parse($this->comment);
	}

}

class Subkopje extends HtmlComment {

	public function view() {
		echo '<h3>' . $this->comment . '</h3>';
	}

}
