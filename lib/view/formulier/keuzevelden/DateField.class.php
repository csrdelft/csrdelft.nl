<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\invoervelden\InputField;

/**
 * DateField.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * DateField
 *
 * Selecteer een datum, met een mogelijk maximum jaar.
 *
 * Produceert drie velden.
 */
class DateField extends InputField {
	protected $wrapperClassName = 'form-group row form-inline';
	protected $labelClassName = 'col-3 col-form-label d-flex justify-content-start'; // Bootstrap plaatst labels in het midden in form-inline; forceer links.
	protected $fieldClassName = 'col-6';

	protected $max_jaar;
	protected $min_jaar;

	public function __construct($name, $value, $description, $maxyear = null, $minyear = null) {
		parent::__construct($name, $value, $description);
		if (is_int($maxyear)) {
			$this->max_jaar = $maxyear;
		} else {
			$this->max_jaar = (int)date('Y') + 10;
		}
		if (is_int($minyear)) {
			$this->min_jaar = $minyear;
		} else {
			$this->min_jaar = (int)date('Y') - 10;
		}
		$jaar = (int)date('Y', strtotime($value));
		if ($jaar > $this->max_jaar) {
			$this->max_jaar = $jaar;
		}
		if ($jaar < $this->min_jaar) {
			$this->min_jaar = $jaar;
		}

	}

	public function isPosted() {
		return isset($_POST[$this->name . '_jaar'], $_POST[$this->name . '_maand'], $_POST[$this->name . '_dag']);
	}

	public function getJaar() {
		return $_POST[$this->name . '_jaar'];
	}

	public function getMaand() {
		return $_POST[$this->name . '_maand'];
	}

	public function getDag() {
		return $_POST[$this->name . '_dag'];
	}

	public function getValue() {
		$this->value = parent::getValue();
		if ($this->isPosted()) {
			$this->value = $this->getJaar() . '-' . $this->getMaand() . '-' . $this->getDag();
		}
		if ($this->empty_null AND $this->value == '0000-00-00') {
			return null;
		}
		return $this->value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		$jaar = (int)$this->getJaar();
		$maand = (int)$this->getMaand();
		$dag = (int)$this->getDag();
		if ($this->value == '0000-00-00' OR empty($this->value)) {
			if ($this->required) {
				$this->error = 'Dit is een verplicht veld';
			}
		} elseif (!preg_match('/^(\d{4})-(\d\d?)-(\d\d?)$/', $this->value) OR !checkdate($maand, $dag, $jaar)) {
			$this->error = 'Ongeldige datum';
		} elseif (is_int($this->max_jaar) AND $jaar > $this->max_jaar) {
			$this->error = 'Kies een jaar voor ' . $this->max_jaar;
		} elseif (is_int($this->min_jaar) AND $jaar < $this->min_jaar) {
			$this->error = 'Kies een jaar na ' . $this->min_jaar;
		}

		return $this->error === '';
	}

	public function getPreviewDiv() {
		return '<div id="datumPreview_' . $this->getId() . '" class="col"></div>';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS

var preview{$this->getId()} = function () {
	var datum = new Date($('#{$this->getId()}_jaar').val(), $('#{$this->getId()}_maand').val() - 1, $('#{$this->getId()}_dag').val());
	var weekday = [ 'zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag' ];
	$('#datumPreview_{$this->getId()}').html(weekday[datum.getDay()]);
}
preview{$this->getId()}();
$('#{$this->getId()}_dag').change(preview{$this->getId()});
$('#{$this->getId()}_dag').keyup(preview{$this->getId()});
$('#{$this->getId()}_maand').change(preview{$this->getId()});
$('#{$this->getId()}_maand').keyup(preview{$this->getId()});
$('#{$this->getId()}_jaar').change(preview{$this->getId()});
$('#{$this->getId()}_jaar').keyup(preview{$this->getId()});
JS;
	}

	public function getHtml() {
		$years = range($this->min_jaar, $this->max_jaar);
		$months = range(1, 12);
		$days = range(1, 31);

		if (!$this->required) {
			array_unshift($years, 0);
			array_unshift($months, 0);
			array_unshift($days, 0);
		}

		$html = '<select id="' . $this->getId() . '_dag" name="' . $this->name . '_dag" origvalue="' . substr($this->origvalue, 8, 2) . '" ' . $this->getInputAttribute('class') . '>';
		foreach ($days as $value) {
			$value = sprintf('%02d', $value);
			$html .= '<option value="' . $value . '"';
			if ($value == substr($this->value, 8, 2)) {
				$html .= ' selected="selected"';
			}
			$label = (int)$value;
			if ($label > 0) {
				if ($label < 10) {
					$label = '&nbsp;&nbsp;' . $label;
				}
			} else {
				$label = '';
			}
			$html .= '>' . $label . '</option>';
		}
		$html .= '</select> ';

		$html .= '<select id="' . $this->getId() . '_maand" name="' . $this->name . '_maand" origvalue="' . substr($this->origvalue, 5, 2) . '" ' . $this->getInputAttribute('class') . '>';
		foreach ($months as $value) {
			$value = sprintf('%02d', $value);
			$html .= '<option value="' . $value . '"';
			if ($value == substr($this->value, 5, 2)) {
				$html .= ' selected="selected"';
			}
			$label = (int)$value;
			if ($label > 0) {
				$label = '&nbsp;&nbsp;' . strftime('%B', mktime(0, 0, 0, $label, 1, 0));
			} else {
				$label = '';
			}
			$html .= '>' . $label . '</option>';
		}
		$html .= '</select> ';

		$html .= '<select id="' . $this->getId() . '_jaar" name="' . $this->name . '_jaar" origvalue="' . substr($this->origvalue, 0, 4) . '" ' . $this->getInputAttribute('class') . '>';
		foreach ($years as $value) {
			$value = sprintf('%04d', $value);
			$html .= '<option value="' . $value . '"';
			if ($value == substr($this->value, 0, 4)) {
				$html .= ' selected="selected"';
			}
			if ((int)$value > 0) {
				$label = $value;
			} else {
				$label = '';
			}
			$html .= '>' . $label . '</option>';
		}
		return $html . '</select>';
	}

}
