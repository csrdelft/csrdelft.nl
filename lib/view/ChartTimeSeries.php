<?php

namespace CsrDelft\view;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class ChartTimeSeries extends JsonResponse
{
	/**
	 * @return (((int|string)[][]|false|int|mixed)[]|string)[][]
	 *
	 * @psalm-return array{labels: list{string, string}, datasets: list{0?: array{label: mixed, data: list{0?: array{x: string, y: int},...}, lineTension: 0, fill: false, borderWidth: 2, pointRadius: 1, pointHitRadius: 2},...}}
	 */
	public function getModel()
	{
		$array = [];
		$minimum = time();
		foreach ($this->model as $label => $data) {
			$entry = [];
			foreach ($data as $row) {
				if ($row['timestamp'] < $minimum) {
					$minimum = (int) $row['timestamp'];
				}
				$entry[] = [
					'x' => date('Y-m-d', $row['timestamp']),
					'y' => (int) $row['count'],
				];
			}
			$array[] = [
				'label' => $label,
				'data' => $entry,
				'lineTension' => 0,
				'fill' => false,
				'borderWidth' => 2,
				'pointRadius' => 1,
				'pointHitRadius' => 2,
			];
		}
		return [
			'labels' => [date('Y-m-d', $minimum), date('Y-m-d')],
			'datasets' => $array,
		];
	}
}
