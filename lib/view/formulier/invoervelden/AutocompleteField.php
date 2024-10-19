<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\Util\CryptoUtil;
use CsrDelft\common\Util\TextUtil;

class AutocompleteField extends TextField
{
	/**
	 * Iedere waarde is een lijst (voor vooraf ingestelde waardes) of een url (voor externe waardes)
	 *
	 * @var array lijst van search providers
	 */
	public $suggestions = [];

	public function __construct($name, $value, $description, $clicktogo = false)
	{
		parent::__construct($name, $value, $description);

		$this->css_classes[] = 'autocomplete-field';

		if ($clicktogo) {
			$this->css_classes[] = 'clicktogo';
		}
	}

	public function getHtml()
	{
		$sources = [];

		// Formatteer suggesties zodat ze met Bloodhound opgepikt kunnen worden.
		foreach ($this->suggestions as $name => $source) {
			$dataset[$name] = CryptoUtil::uniqid_safe($this->name);

			if (is_array($source)) {
				$suggestions = array_values($source);
				foreach ($suggestions as $i => $suggestion) {
					if (!is_array($suggestion)) {
						$suggestions[$i] = ['value' => $suggestion];
					}
				}

				$sources[] = ['local' => $suggestions];
			} else {
				$sources[] = [
					'remote' => [
						'url' => "{$source}%QUERY",
						'wildcard' => '%QUERY',
					],
				];
			}
		}

		$sourcesJSON = TextUtil::vue_encode($sources);

		$clickToGo = array_search('clicktogo', $this->css_classes)
			? 'true'
			: 'false';

		$autoselectStr = $this->autoselect ? 'true' : 'false';

		$inputAttribute = $this->getInputAttribute([
			'type',
			'id',
			'name',
			'class',
			'value',
			'origvalue',
			'disabled',
			'readonly',
			'maxlength',
			'placeholder',
			'autocomplete',
		]);

		return '<input data-autoselect="' .
			$autoselectStr .
			'" data-clicktogo="' .
			$clickToGo .
			'" data-sources="' .
			$sourcesJSON .
			'" ' .
			$inputAttribute .
			' />';
	}

	protected function getInputAttribute($attribute)
	{
		if (
			$attribute === 'autocomplete' &&
			(!$this->autocomplete || $this->suggestions !== [])
		) {
			return 'autocomplete="off"';
			// browser autocompete
		}
		return parent::getInputAttribute($attribute);
	}
}
