<?php

namespace CsrDelft\view\ledenmemory;

use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\View;

class LedenMemoryZijbalkView implements View
{

	private $scores;
	private $titel;

	public function __construct(
		$scores,
		$titel
	)
	{
		$this->scores = $scores;
		$this->titel = $titel;
	}

	public function getTitel()
	{
		return 'Topscores ' . $this->titel;
	}

	public function getBreadcrumbs()
	{
		return null;
	}

	public function getModel()
	{
		return $this->scores;
	}

	public function __toString()
	{
		$html = '';
		$html .= '<div id="zijbalk_ledenmemory_topscores"><div class="zijbalk-kopje"><a href="/forum/onderwerp/8017">';
		$html .= $this->getTitel();
		$html .= '</a></div>';
		$first = true;
		foreach ($this->getModel() as $score) {
			$html .= '<div class="item">';
			$html .= sprintf('%02d', floor($score->tijd / 60 % 60)); //minuten
			$html .= ':';
			$html .= sprintf('%02d', floor($score->tijd % 60)); //seconden
			$html .= ' ';
			if ($first) {
				$html .= '<span class="cursief">';
			}
			$html .= ProfielRepository::getLink($score->door_uid, 'civitas');
			$html .= ' (';
			$html .= $score->beurten;
			$html .= ')';
			if ($first) {
				$html .= '</span>';
			}
			$html .= '</div>';
			$first = false;
		}
		$html .= '</div>'; //einde wrapperdiv
		return $html;
	}

}
