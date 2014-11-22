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
 * 		* SelectField				Lijst van invoeropties
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
		echo '<h3 class="' . get_class($this) . '">' . $this->comment . '</h3>';
	}

}

/**
 * Je moet zelf de DIV sluiten!
 */
class CollapsableSubkopje extends Subkopje {

	private $id;
	public $collapsed;

	public function __construct($id, $titel, $collapsed = false) {
		parent::__construct($titel);
		$this->id = $id;
		$this->collapsed = $collapsed;
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS
$('#toggle_kopje_{$this->id}').click(function() {
	if ($('#expand_kopje_{$this->id}').is(':visible')) {
		$(this).removeClass('toggle-group-expanded');
		$('#expand_kopje_{$this->id}').slideUp(200);
	} else {
		$(this).addClass('toggle-group-expanded');
		$('#expand_kopje_{$this->id}').slideDown(200);
	}
});
JS;
	}

	public function view() {
		echo '<div id="toggle_kopje_' . $this->id . '" class="toggle-group ' . ($this->collapsed ? '' : 'toggle-group-expanded') . '">';
		parent::view();
		echo '</div><div id="expand_kopje_' . $this->id . '" class="expanded-submenu" ' . ($this->collapsed ? 'style="display:none;"' : '') . '>';
	}

}
