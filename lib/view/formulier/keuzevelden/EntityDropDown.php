<?php

namespace CsrDelft\view\formulier\keuzevelden;
/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Select an entity based on the primary key while showing the label attributes
 */
class EntityDropDown extends SelectField {

	public function __construct($name, array $primary_key_values, $description, array $options, array $label_attributes, $size = 1, $multiple = false) {
		parent::__construct($name, json_encode($primary_key_values), $description, array(), $size, $multiple);
		if (!$this->required) {
			$this->options[''] = '';
		}
		foreach ($options as $option) {
			$label = array();
			foreach ($label_attributes as $attribute) {
				$label[] = $option->$attribute;
			}
			$this->options[json_encode($option->getValues(true))] = implode(' ', $label);
		}
	}

	public function getValue() {
		$this->value = parent::getValue();
		if ($this->empty_null AND $this->value == '') {
			return null;
		}
		return json_decode($this->value);
	}

}
