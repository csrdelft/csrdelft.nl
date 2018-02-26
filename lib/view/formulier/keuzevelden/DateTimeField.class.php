<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\invoervelden\TextField;

/**
 * DateTimeField.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Date time picker with range (optional).
 */
class DateTimeField extends TextField {

	public $from_datetime;
	public $to_datetime;
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

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		$jaar = (int)substr($this->value, 0, 4);
		$maand = (int)substr($this->value, 5, 2);
		$dag = (int)substr($this->value, 8, 2);
		$uur = (int)substr($this->value, 11, 2);
		$min = (int)substr($this->value, 14, 2);
		$sec = (int)substr($this->value, 17, 2);
		if (!checkdate($maand, $dag, $jaar)) {
			$this->error = 'Ongeldige datum';
		} elseif ($uur < 0 OR $uur > 23 OR $min < 0 OR $min > 59 OR $sec < 0 OR $sec > 59) {
			$this->error = 'Ongeldig tijdstip';
		} elseif (is_int($this->max_jaar) AND $jaar > $this->max_jaar) {
			$this->error = 'Kies een jaar voor ' . $this->max_jaar;
		} elseif (is_int($this->min_jaar) AND $jaar < $this->min_jaar) {
			$this->error = 'Kies een jaar na ' . $this->min_jaar;
		}
		return $this->error === '';
	}

	public function getJavascript() {
		if ($this->readonly) {
			return '';
		}
		if ($this->from_datetime) {
			$min = $this->from_datetime->getValue();
		} else {
			$min = null;
		}
		if ($this->to_datetime) {
			$max = $this->to_datetime->getValue();
		} else {
			$max = null;
		}
		$settings = json_encode(array(
			'changeYear' => true,
			'changeMonth' => true,
			'showWeek' => true,
			'showButtonPanel' => true,
			'dateFormat' => 'yy-mm-dd',
			'timeFormat' => 'HH:mm:ss',
			'minDate' => $min,
			'maxDate' => $max
		));
		$js = parent::getJavascript() . <<<JS

var settings{$this->getId()} = {$settings};
settings{$this->getId()}['onClose'] = function (selectedDate) {
	
JS;
		if ($this->from_datetime) {
			$js .= '$("#' . $this->from_datetime->getId() . '").datetimepicker("option", "maxDate", selectedDate);';
		}
		if ($this->to_datetime) {
			$js .= '$("#' . $this->to_datetime->getId() . '").datetimepicker("option", "minDate", selectedDate);';
		}
		return $js . <<<JS

};
$("#{$this->getId()}").datetimepicker(settings{$this->getId()});
JS;
	}

}
