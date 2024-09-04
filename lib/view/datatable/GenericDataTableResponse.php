<?php

namespace CsrDelft\view\datatable;

use CsrDelft\view\ToResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class GenericDataTableResponse implements ToResponse
{
	public $lastUpdate;
	/**
	 * @var null
	 */
	private $autoUpdate;
	/**
	 * @var string[]
	 */
	private $groups;

	public function __construct(
		private readonly SerializerInterface $serializer,
		private $data,
		public $modal = null,
		$autoUpdate = null,
		$groups = null
	) {
		$this->lastUpdate = time() - 1;
		$this->autoUpdate = $autoUpdate;
		$this->groups = $groups ?? ['datatable'];
	}

	public function toResponse(): Response
	{
		$serialized = $this->serializer->serialize($this->data, 'json', [
			'groups' => $this->groups,
		]);
		$autoUpdateString = $this->autoUpdate ? 'true' : 'false';
		$modalHtml = json_encode($this->modal);

		$responseText = <<<JSON
{
    "modal": {$modalHtml},
    "autoUpdate": {$autoUpdateString},
    "lastUpdate": {$this->lastUpdate},
    "data": $serialized
}
JSON;

		return new Response($responseText, 200, [
			'Content-Type' => 'application/json',
		]);
	}
}
