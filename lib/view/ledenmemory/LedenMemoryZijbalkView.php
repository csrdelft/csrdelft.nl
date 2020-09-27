<?php

namespace CsrDelft\view\ledenmemory;

use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\View;

class LedenMemoryZijbalkView implements View {

	private $scores;
	private $titel;

	public function __construct(
		$scores,
		$titel
	) {
		$this->scores = $scores;
		$this->titel = $titel;
	}

	public function getTitel() {
		return 'Topscores ' . $this->titel;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getModel() {
		return $this->scores;
	}

	public function view() {
		echo '<div id="zijbalk_ledenmemory_topscores"><div class="zijbalk-kopje"><a href="/forum/onderwerp/8017">';
		echo $this->getTitel();
		echo '</a></div>';
		$first = true;
		foreach ($this->getModel() as $score) {
			echo '<div class="item">';
			echo sprintf('%02d', floor($score->tijd / 60 % 60)); //minuten
			echo ':';
			echo sprintf('%02d', floor($score->tijd % 60)); //seconden
			echo ' ';
			if ($first) {
				echo '<span class="cursief">';
			}
			echo ProfielRepository::getLink($score->door_uid, 'civitas');
			echo ' (';
			echo $score->beurten;
			echo ')';
			if ($first) {
				echo '</span>';
			}
			echo '</div>';
			$first = false;
		}
		echo '</div>'; //einde wrapperdiv
	}

}
