<?php

namespace CsrDelft\Component\DataTable;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DataTableInstance
{
	/** @var array */
	private $settings;
	/** @var string */
	private $titel;
	/** @var string */
	private $tableId;
	/** @var SerializerInterface */
	private $serializer;
	/** @var NormalizerInterface */
	private $normalizer;
	/** @var string */
	private $beschrijving;

	public function __construct(
		SerializerInterface $serializer,
		NormalizerInterface $normalizer,
		$titel,
		$beschrijving,
		$tableId,
		array $settings
	) {
		$this->settings = $settings;
		$this->titel = $titel;
		$this->tableId = $tableId;
		$this->serializer = $serializer;
		$this->normalizer = $normalizer;
		$this->beschrijving = $beschrijving;
	}

	public function createView()
	{
		$id = str_replace(' ', '-', strtolower($this->titel));

		$settingsJson = htmlspecialchars(
			$this->serializer->serialize($this->settings, 'json')
		);

		$title = $this->titel
			? "<h2 id=\"table-{$id}\" class=\"Titel\">{$this->titel}</h2>"
			: '';
		$beschrijving = $this->beschrijving ? "<p>{$this->beschrijving}</p>" : '';
		$table = "<table id=\"{$this->tableId}\" class=\"ctx-datatable display\" data-settings=\"{$settingsJson}\"></table>";

		return new DataTableView($title . $beschrijving . $table);
	}

	/**
	 * @param $data
	 * @param null $modal
	 * @param bool $autoUpdate
	 * @return Response
	 * @throws ExceptionInterface
	 */
	public function createData($data, $modal = null, $autoUpdate = false)
	{
		$normalizedData = $this->normalizer->normalize($data, 'json', [
			AbstractNormalizer::GROUPS => ['datatable'],
		]);

		$model = [
			'modal' => $modal,
			'autoUpdate' => $autoUpdate,
			'lastUpdate' => time() - 1,
			'data' => $normalizedData,
		];

		return new Response($this->serializer->serialize($model, 'json'), 200, [
			'Content-Type' => 'application/json',
		]);
	}

	/**
	 * @return string
	 */
	public function getTableId(): string
	{
		return $this->tableId;
	}
}
