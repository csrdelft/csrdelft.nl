<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\model\ProfielModel;
use CsrDelft\model\ProfielService;
use CsrDelft\model\security\AccountModel;

/**
 * LidField.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class LidField extends TextField {

	protected $fieldClassName = 'col-sm-4';

	// zoekfilter voor door namen2uid gebruikte LidZoeker::zoekLeden.
	// geaccepteerde input: 'leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies'
	private $zoekin;

	public function __construct($name, $value, $description, $zoekin = 'alleleden') {
		parent::__construct($name, $value, $description);
		if (!in_array($zoekin, array('leden', 'oudleden', 'novieten', 'alleleden', 'allepersonen', 'nobodies'))) {
			$zoekin = 'leden';
		}
		$this->zoekin = $zoekin;
		$this->suggestions[ucfirst($this->zoekin)] = '/tools/naamsuggesties/' . $this->zoekin . '?q=';
	}

	public function getValue() {
		$this->value = parent::getValue();
		if ($this->empty_null AND empty($this->value)) {
			return null;
		}
		if (!AccountModel::isValidUid($this->value)) {
			$profielen = ProfielService::instance()->zoekLeden($this->value, 'naam', 'alle', 'achternaam', $this->zoekin);
			if (!empty($profielen)) {
				$this->value = $profielen[0]->uid;
			}
		}
		return $this->value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		$value = parent::getValue();
		// geldig uid?
		if (AccountModel::isValidUid($value) AND ProfielModel::existsUid($value)) {
			return true;
		}
		$profielen = ProfielService::instance()->zoekLeden($value, 'naam', 'alle', 'achternaam', $this->zoekin);
		if (!empty($profielen)) {
			if (count($profielen) == 1) {
				return true;
			} else {
				$this->error = 'Meerdere leden mogelijk';
				return false;
			}
		}
		$this->error = 'Geen geldig lid';
		return $this->error === '';
	}

	public function getPreviewDiv() {
		return '<div id="lidPreview_' . $this->getId() . '"></div>';
	}

	public function getJavascript() {
		return /** @lang JavaScript */
			parent::getJavascript() . <<<JS

var preview{$this->getId()} = function() {
	var val = $('#{$this->getId()}').val();
	if (val.length < 1) {
		$('#lidPreview_{$this->getId()}').html('');
		return;
	}
	$.ajax({
		url: "/tools/naamlink.php?zoekin={$this->zoekin}&naam=" + val,
	}).done(function(response) {
		$('#lidPreview_{$this->getId()}').html(response);
		window.context.initContext('#lidPreview_{$this->getId()}');
	});
};
preview{$this->getId()}();
$('#{$this->getId()}').change(preview{$this->getId()});
JS;
	}

}
