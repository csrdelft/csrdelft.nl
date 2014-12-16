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
	public $single;
	public $hover_click;
	private $expand;
	private $collapse;

	public function __construct($id, $titel, $collapsed = false, $single = false, $hover_click = false, $animate = true) {
		parent::__construct($titel);
		$this->id = $id;
		$this->collapsed = $collapsed;
		$this->single = $single;
		$this->hover_click = $hover_click;
		if ($animate) {
			$this->expand = 'slideDown(200)';
			$this->collapse = 'slideUp(200)';
		} else {
			$this->expand = 'show()';
			$this->collapse = 'hide()';
		}
	}

	public function getJavascript() {
		$js = parent::getJavascript() . <<<JS

$('#toggle_kopje_{$this->id}').click(function() {
	if ($('#expand_kopje_{$this->id}').is(':visible')) {
JS;
		// niet inklappen?
		if (!$this->single) {
			$js .= <<<JS

		$('#expand_kopje_{$this->id}').{$this->collapse};
		$(this).removeClass('toggle-group-expanded');
JS;
		}
		$js .= <<<JS
	} else {
JS;
		// de rest inklappen?
		if ($this->single) {
			$js .= <<<JS

$(this).siblings('.expanded-submenu').{$this->collapse};
$(this).siblings('.toggle-group').removeClass('toggle-group-expanded');
JS;
		}
		// uitklappen:
		$js .= <<<JS

		$('#expand_kopje_{$this->id}').{$this->expand};
		$(this).addClass('toggle-group-expanded');
	}
});
JS;
		// uitklappen bij hover?
		if ($this->hover_click) {
			$js .= <<<JS

try {
	$('#toggle_kopje_{$this->id}').hoverIntent(function() {
		if (!$(this).hasClass('toggle-group-expanded')) {
			$(this).trigger('click');
		}
	});
} catch(e) {
	// Missing js file
}
JS;
		}
		return $js;
	}

	public function getHtml() {
		return '<div id="toggle_kopje_' . $this->id . '" class="toggle-group ' . ($this->collapsed ? '' : 'toggle-group-expanded') . '">'
				. parent::getHtml() .
				'</div><div id="expand_kopje_' . $this->id . '" class="expanded-submenu" ' . ($this->collapsed ? 'style="display:none;"' : '') . '>';
	}

}
