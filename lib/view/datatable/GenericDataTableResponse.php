<?php


namespace CsrDelft\view\datatable;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class GenericDataTableResponse extends Response {
	public function __construct(SerializerInterface $serializer, $data, $removedData = [], $modal = null, $autoUpdate = null) {
		$serialized = $serializer->serialize($data, 'json', ['groups' => ['datatable']]);
		$lastUpdate = time() - 1;
		$autoUpdateString = $autoUpdate ? "true" : "false";

		$responseText = <<<JSON
{
    "modal": "{$modal}",
    "autoUpdate": {$autoUpdateString},
    "lastUpdate": {$lastUpdate},
    "data": $serialized
}
JSON;

		parent::__construct($responseText, 200, ['Content-Type' => 'application/json']);
	}

}
