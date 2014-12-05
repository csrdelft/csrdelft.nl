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

	public function getHtml();

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

	public function getHtml() {
		return $this->comment;
	}

	public function view() {
		echo $this->getHtml();
	}

	public function getJavascript() {
		return <<<JS

/* {$this->getTitel()} */
JS;
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

	public $h = 3;

	public function getHtml() {
		return '<h' . $this->h . ' class="' . get_class($this) . '">' . $this->comment . '</h' . $this->h . '>';
	}

}

/**
 * Je moet zelf de DIV sluiten!
 */
class CollapsableSubkopje extends Subkopje {

	private $id;
	public $collapsed;
	private $slide;

	public function __construct($id, $titel, $collapsed = false, $slide = true) {
		parent::__construct($titel);
		$this->id = $id;
		$this->collapsed = $collapsed;
		$this->slide = $slide;
	}

	public function getJavascript() {
		if ($this->slide) {
			$expand = 'slideDown(200)';
			$collapse = 'slideUp(200)';
		} else {
			$expand = 'show()';
			$collapse = 'hide()';
		}
		return parent::getJavascript() . <<<JS
$('#toggle_kopje_{$this->id}').click(function() {
	if ($('#expand_kopje_{$this->id}').is(':visible')) {
		$(this).removeClass('toggle-group-expanded');
		$('#expand_kopje_{$this->id}').{$collapse};
	} else {
		$(this).addClass('toggle-group-expanded');
		$('#expand_kopje_{$this->id}').{$expand};
	}
});
JS;
	}

	public function getHtml() {
		return '<div id="toggle_kopje_' . $this->id . '" class="toggle-group ' . ($this->collapsed ? '' : 'toggle-group-expanded') . '">'
				. parent::getHtml() .
				'</div><div id="expand_kopje_' . $this->id . '" class="expanded-submenu" ' . ($this->collapsed ? 'style="display:none;"' : '') . '>';
	}

}
