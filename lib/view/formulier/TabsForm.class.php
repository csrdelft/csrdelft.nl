<?php

namespace CsrDelft\view\formulier;

/**
 * TabsForm.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier met tabbladen.
 *
 */
class TabsForm extends Formulier {

	private $tabs = array();
	public $vertical = false;
	public $hoverintent = false;
	public $nestedForm = false;

	public function getTabs() {
		return $this->tabs;
	}

	public function hasTabs() {
		return !empty($this->tabs);
	}

	public function hasTab($tab) {
		return isset($this->tabs[$tab]);
	}

	public function addTab($tab) {
		if ($this->hasTab($tab)) {
			return false;
		}
		$this->tabs[$tab] = array();
		return true;
	}

	public function addFields(array $fields, $tab = 'head') {
		$this->addTab($tab);
		$this->tabs[$tab] = array_merge($this->tabs[$tab], $fields);
		parent::addFields($fields);
	}

	/**
	 * Toont het formulier en javascript van alle fields.
	 */
	public function view() {
		echo getMelding();
		if ($this->nestedForm) {
			echo '<div id="' . $this->formId . '" class="' . implode(' ', $this->css_classes) . '">';
		} else {
			echo $this->getFormTag();
		}
		if ($this->getTitel()) {
			echo '<h2 class="Titel">' . $this->getTitel() . '</h2>';
		}
		// fields above tabs
		if (isset($this->tabs['head'])) {
			foreach ($this->tabs['head'] as $field) {
				echo $field->getHtml();
			}
			unset($this->tabs['head']);
		}
		// fields below tabs
		if (isset($this->tabs['foot'])) {
			$foot = $this->tabs['foot'];
			unset($this->tabs['foot']);
		}
		// tabs
		if (sizeof($this->tabs) > 0) {
			echo '<br /><div id="' . $this->formId . '-tabs" class="tabs-list"><ul>';
			foreach ($this->tabs as $tab => $fields) {
				echo '<li><a href="#' . $this->formId . '-tab-' . $tab . '" class="tab-item">' . ucfirst($tab) . '</a></li>';
			}
			echo '</ul>';
			foreach ($this->tabs as $tab => $fields) {
				echo '<div id="' . $this->formId . '-tab-' . $tab . '" class="tabs-content">';
				foreach ($fields as $field) {
					echo $field->getHtml();
				}
				echo '</div>';
			}
			echo '</div><br />';
		}
		// fields below tabs
		if (isset($foot)) {
			foreach ($foot as $field) {
				echo $field->getHtml();
			}
		}
		echo $this->getScriptTag();
		if ($this->nestedForm) {
			echo '</div>';
		} else {
			echo '</form>';
		}
	}

	public function getJavascript() {
		$js = <<<JS

$('#{$this->formId}-tabs').tabs();
JS;
		if ($this->vertical) {
			$js .= <<<JS

$('#{$this->formId}-tabs').tabs().addClass('ui-tabs-vertical ui-helper-clearfix');
$('#{$this->formId}-tabs li').removeClass('ui-corner-top').addClass('ui-corner-left');
JS;
		}
		if ($this->hoverintent) {
			$js .= <<<JS

try {
	$('#{$this->formId}-tabs .tab-item').hoverIntent(function() {
		$(this).trigger('click');
	});
} catch(err) {
	console.log(err);
	// Missing js file
}
JS;
		}
		return parent::getJavascript() . $js;
	}

}
