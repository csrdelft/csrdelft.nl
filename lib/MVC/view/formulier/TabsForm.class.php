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
		$this->tabs = $tabs;
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
		if ($this->hasTab($tab)) {
			$this->tabs[$tab] = array_merge($this->tabs[$tab], $fields);
		}
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
		echo '<div id="tabs"><ul>';
		foreach ($this->tabs as $tab) {
			echo '<li><a href="#tabs-' . $tab . '">' . ucfirst($tab) . '</a></li>';
		}
		echo '</ul>';
		$fields = $this->getFields();
		foreach ($this->tabs as $tab) {
			echo '<div id="tabs-' . $tab . '">';
			foreach ($tab as $field) {
				$field->view();
				$todo = array_search($field, $fields, true);
				if ($todo) {
					unset($fields[$todo]);
				}
			}
			echo '</div>';
		}
		echo '</div>';
		foreach ($fields as $field) {
			$field->view();
		}
		echo $this->getScriptTag();
		echo '</form>';
	}

}
