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

	public function setTabs(array $tabs) {
		foreach ($tabs as $tab) {
			$this->addTab($tab);
		}
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

	public function addFields(array $fields, $tab = null) {
		$this->addTab($tab);
		$this->tabs[$tab] = array_merge($this->tabs[$tab], $fields);
		parent::addFields($fields);
	}

	/**
	 * Toont het formulier en javascript van alle fields.
	 */
	public function view() {
		echo getMelding();
		if ($this->getTitel()) {
			echo '<h1 class="formTitle">' . $this->getTitel() . '</h1>';
		}
		echo $this->getFormTag();
		if (isset($this->tabs['head'])) {
			foreach ($this->tabs['head'] as $field) {
				$field->view();
			}
			unset($this->tabs['head']);
		}
		if (isset($this->tabs['foot'])) {
			$foot = $this->tabs['foot'];
			unset($this->tabs['foot']);
		}
		echo '<div id="tabs"><ul>';
		foreach ($this->tabs as $tab => $fields) {
			echo '<li><a href="#tabs-' . $tab . '">' . ucfirst($tab) . '</a></li>';
		}
		echo '</ul>';
		foreach ($this->tabs as $tab => $fields) {
			echo '<div id="tabs-' . $tab . '">';
			foreach ($fields as $field) {
				$field->view();
			}
			echo '</div>';
		}
		echo '</div>';
		if (isset($foot)) {
			foreach ($foot as $field) {
				$field->view();
			}
		}
		echo $this->getScriptTag();
		echo '</form>';
	}

}
