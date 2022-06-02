<?php


namespace CsrDelft\view\datatable;


use CsrDelft\view\ToResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class GenericDataTableResponse implements ToResponse
{
	public $modal;
	public $lastUpdate;
	/**
	 * @var null
	 */
	private $autoUpdate;
	/**
	 * @var SerializerInterface
	 */
	private $serializer;
	private $data;
	/**
	 * @var string[]
	 */
	private $groups;

	public function __construct(SerializerInterface $serializer, $data, $modal = null, $autoUpdate = null, $groups = null)
	{
		$this->data = $data;
		$this->lastUpdate = time() - 1;
		$this->autoUpdate = $autoUpdate;
		$this->serializer = $serializer;
		$this->modal = $modal;
		$this->groups = $groups ?? ['datatable'];
	}


	public function toResponse(): Response
	{
		$serialized = $this->serializer->serialize($this->data, 'json', ['groups' => $this->groups]);
		$autoUpdateString = $this->autoUpdate ? "true" : "false";
		$modalHtml = json_encode($this->modal);

		$responseText = <<<JSON
{
    "modal": {$modalHtml},
    "autoUpdate": {$autoUpdateString},
    "lastUpdate": {$this->lastUpdate},
    "data": $serialized
}
JSON;

		return new Response($responseText, 200, ['Content-Type' => 'application/json']);
	}
}
