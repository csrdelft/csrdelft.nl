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
 *  - HtmlComment					Uitleg/commentaar in een formulier stoppen
 *  - FormButtons					Submitten, resetten en custom functies van het formulier
 *  - FileField						Bestand upload ketzer
 * 
 * Uitbreidingen van HtmlComment:
 * 		- HtmlComment				invoer wordt als html weergegeven
 * 		- UbbComment				invoer wordt als ubb geparsed
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

class UbbComment extends HtmlComment {

	public function view() {
		echo CsrUbb::parse($this->comment);
	}

}

class Subkopje extends HtmlComment {

	public function view() {
		echo '<h3>' . $this->comment . '</h3>';
	}

}

/**
 * Submit, reset, cancel, delete & extra button (all optional except submit)
 */
class FormButtons implements FormElement {

	public $submitTitle = 'Invoer opslaan';
	public $submitText;
	public $submitIcon;
	public $resetTitle = 'Reset naar opgeslagen gegevens';
	public $resetText;
	public $resetIcon;
	public $cancelTitle = 'Niet opslaan en terugkeren';
	public $cancelText;
	public $cancelIcon;
	public $cancelUrl;
	public $cancelReset;
	public $deleteTitle;
	public $deleteText;
	public $deleteIcon;
	public $deleteUrl;
	public $deleteAction = 'post confirm ReloadPage';
	public $extraTitle;
	public $extraText;
	public $extraIcon;
	public $extraUrl;
	public $extraAction;
	public $js = '';

	public function __construct($cancel_url = null, $icons = true, $text = true, $reset = true, $delete_url = null) {
		$this->cancelUrl = $cancel_url;
		if ($icons) {
			$this->submitIcon = 'disk';
			$this->resetIcon = 'arrow_rotate_anticlockwise';
			$this->cancelIcon = 'delete';
			$this->deleteIcon = 'cross';
		}
		if ($text) {
			$this->submitText = 'Opslaan';
			$this->resetText = 'Reset';
			$this->cancelText = 'Annuleren';
			$this->deleteText = 'Verwijderen';
		}
		if (!$reset) {
			unset($this->resetIcon);
			unset($this->resetText);
		}
		if ($delete_url === null) {
			unset($this->deleteIcon);
			unset($this->deleteText);
		} else {
			$this->deleteUrl = $delete_url;
		}
	}

	public function getModel() {
		return null;
	}

	public function getTitel() {
		return $this->getType();
	}

	public function getType() {
		return get_class($this);
	}

	public function view() {
		echo '<div class="FormButtons">';
		if (isset($this->deleteIcon) OR isset($this->deleteText)) {
			echo '<div class="float-left"><a id="deleteButton" class="knop';
			if (isset($this->deleteAction)) {
				echo ' ' . $this->deleteAction;
			}
			echo '" title="' . $this->deleteTitle . '" href="' . $this->deleteUrl . '">';
			if (isset($this->deleteIcon)) {
				echo '<img src="' . CSR_PICS . '/famfamfam/' . $this->deleteIcon . '.png" class="icon" width="16" height="16" alt="delete" /> ';
			}
			echo $this->deleteText . '</a></div>';
		}
		if (isset($this->extraIcon) OR isset($this->extraText)) {
			echo '<a id="extraButton" class="knop';
			if (isset($this->extraAction)) {
				echo ' ' . $this->extraAction;
			}
			echo '" title="' . $this->extraTitle . '" href="' . $this->extraUrl . '">';
			if (isset($this->extraIcon)) {
				echo '<img src="' . CSR_PICS . '/famfamfam/' . $this->extraIcon . '.png" class="icon" width="16" height="16" alt="extra" /> ';
			}
			echo $this->extraText . '</a> ';
		}
		if (isset($this->submitIcon) OR isset($this->submitText)) {
			echo '<a class="knop submit" title="' . $this->submitTitle . '">';
			if (isset($this->submitIcon)) {
				echo '<img src="' . CSR_PICS . '/famfamfam/' . $this->submitIcon . '.png" class="icon" width="16" height="16" alt="submit" /> ';
			}
			echo $this->submitText . '</a> ';
		}
		if (isset($this->resetIcon) OR isset($this->resetText)) {
			echo '<a class="knop reset" title="' . $this->resetTitle . '">';
			if (isset($this->resetIcon)) {
				echo '<img src="' . CSR_PICS . '/famfamfam/' . $this->resetIcon . '.png" class="icon" width="16" height="16" alt="reset" /> ';
			}
			echo $this->resetText . '</a> ';
		}
		if (isset($this->cancelIcon) OR isset($this->cancelText)) {
			echo '<a class="knop' . ($this->cancelReset ? ' reset' : '') . ' cancel" title="' . $this->cancelTitle . '"';
			if (isset($this->cancelUrl)) {
				echo ' href="' . $this->cancelUrl . '"';
			}
			echo '>';
			if (isset($this->cancelIcon)) {
				echo '<img src="' . CSR_PICS . '/famfamfam/' . $this->cancelIcon . '.png" class="icon" width="16" height="16" alt="cancel" /> ';
			}
			echo $this->cancelText . '</a>';
		}
		echo '</div>';
	}

	public function getJavascript() {
		if (strpos($this->extraAction, 'submit') !== false AND isset($this->extraUrl)) {
			$this->js .= "$('#extraButton').unbind('click.action');$('#extraButton').bind('click.action', form_replace_action);";
		}
		return $this->js;
	}

}
