<?php

require_once 'MVC/view/formulier/Formulier.class.php';

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

	public function getTabs() {
		return $this->tabs;
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
		echo $this->getFormTag();
		if ($this->getTitel()) {
			echo '<h1 class="Titel">' . $this->getTitel() . '</h1>';
		}
		// fields above tabs
		if (isset($this->tabs['head'])) {
			foreach ($this->tabs['head'] as $field) {
				$field->view();
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
				echo '<li><a href="#' . $this->formId . '-tab-' . $tab . '">' . ucfirst($tab) . '</a></li>';
			}
			echo '</ul>';
			foreach ($this->tabs as $tab => $fields) {
				echo '<div id="' . $this->formId . '-tab-' . $tab . '" class="tabs-content">';
				foreach ($fields as $field) {
					$field->view();
				}
				echo '</div>';
			}
			echo '</div><br />';
		}
		// fields below tabs
		if (isset($foot)) {
			foreach ($foot as $field) {
				$field->view();
			}
		}
		echo $this->getScriptTag();
		echo '</form>';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS
$('#{$this->formId}-tabs').tabs();
JS;
	}

}
