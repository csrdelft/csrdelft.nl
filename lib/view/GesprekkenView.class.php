<?php

/**
 * FotoAlbumView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GesprekkenView implements View {

	protected $gesprekken;

	public function __construct($gesprekken) {
		$this->gesprekken = $gesprekken;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getModel() {
		return $this->gesprekken;
	}

	public function getTitel() {
		return 'Gesprekken';
	}

	public function view() {
		//TODO
	}

}
