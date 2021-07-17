<?php


namespace CsrDelft\view\formulier\invoervelden;


/**
 * Geef suggesties op basis van een externe bron
 */
class RemoteSuggestieField extends TextField
{
	protected $type = 'hidden';

	protected $url;

	public function __construct($name, $value, $description, $url)
	{
		parent::__construct($name, $value, $description);

		$this->url = $url;
	}

	public function getHtml()
	{
		$config = [
			'name' => $this->getName(),
			'id' => $this->getId(),
			'url' => $this->url,
		];

		$configString = vue_encode($config);

		return parent::getHtml()
			. "<div data-autocomplete=\"$configString\"></div>";
	}

}
