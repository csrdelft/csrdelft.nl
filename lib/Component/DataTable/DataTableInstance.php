<?php


namespace CsrDelft\Component\DataTable;


use CsrDelft\view\ToResponse;
use Symfony\Component\HttpFoundation\Response;

class DataTableInstance implements ToResponse {
	/**
	 * @var array
	 */
	private $settings;
	private $titel;
	private $tableId;

	public function __construct($titel, $tableId, array $settings) {
		$this->settings = $settings;
		$this->titel = $titel;
		$this->tableId = $tableId;
	}

	public function toResponse(): Response {
		$id = str_replace(' ', '-', strtolower($this->titel));

		$settingsJson = htmlspecialchars(json_encode($this->settings, DEBUG ? JSON_PRETTY_PRINT : 0));

		$body = <<<HTML
<h2 id="table-{$id}" class="Titel">{$this->titel}</h2>

<table id="{$this->tableId}" class="ctx-datatable display" data-settings="{$settingsJson}"></table>
HTML;

		return new Response($body);
	}
}
