<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\ProfielService;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class LidField extends AutocompleteField
{
	protected $fieldClassName = 'col-sm-4';

	// zoekfilter voor door namen2uid gebruikte LidZoeker::zoekLeden.
	// geaccepteerde input: 'leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies'
	private $zoekin;

	public function __construct(
		$name,
		$value,
		$description,
		$zoekin = 'alleleden'
	) {
		parent::__construct($name, $value, $description);
		if (
			!in_array($zoekin, [
				'leden',
				'oudleden',
				'novieten',
				'alleleden',
				'allepersonen',
				'nobodies',
			])
		) {
			$zoekin = 'leden';
		}
		$this->zoekin = $zoekin;
		$this->suggestions[ucfirst($this->zoekin)] =
			'/tools/naamsuggesties?zoekin=' . $this->zoekin . '&q=';
	}

	public function getValue(): ?string
	{
		$this->value = parent::getValue();
		if ($this->empty_null and empty($this->value)) {
			return null;
		}
		if (!AccountRepository::isValidUid($this->value)) {
			$profielService = ContainerFacade::getContainer()->get(
				ProfielService::class
			);
			$profielen = $profielService->zoekLeden(
				$this->value,
				'naam',
				'alle',
				'achternaam',
				$this->zoekin
			);
			if (!empty($profielen)) {
				$this->value = $profielen[0]->uid;
			}
		}
		return $this->value;
	}

	public function validate(): bool
	{
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		$value = parent::getValue();
		// geldig uid?
		if (
			AccountRepository::isValidUid($value) and
			ProfielRepository::existsUid($value)
		) {
			return true;
		}
		$profielService = ContainerFacade::getContainer()->get(
			ProfielService::class
		);
		$profielen = $profielService->zoekLeden(
			$value,
			'naam',
			'alle',
			'achternaam',
			$this->zoekin
		);
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

	public function getPreviewDiv(): string
	{
		return '<div id="lidPreview_' . $this->getId() . '"></div>';
	}

	public function getJavascript(): string
	{
		return /** @lang JavaScript */
			parent::getJavascript() .
				<<<JS

var preview{$this->getId()} = function() {
	var val = $('#{$this->getId()}').val();
	if (val.length < 1) {
		$('#lidPreview_{$this->getId()}').html('');
		return;
	}
	$.ajax({
		url: "/tools/naamlink?zoekin={$this->zoekin}&naam=" + val,
	}).done(function(response) {
		$('#lidPreview_{$this->getId()}').html(response);
		var el = document.getElementById('lidPreview_{$this->getId()}');

		if (el) { // el kan op dit moment niet meer bestaan.
				window.context.init(el);
		}
	});
};
preview{$this->getId()}();
$('#{$this->getId()}').change(preview{$this->getId()});
JS;
	}
}
