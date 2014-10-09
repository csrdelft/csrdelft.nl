<?php

/**
 * WikiView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class WikiView implements View {

	/**
	 * Doorstuur url
	 * @var string
	 */
	protected $req_url;

	public function __construct($url) {
		$this->req_url = $url;
	}

	public function getModel() {
		return $this->req_url;
	}

	public function getTitel() {
		return 'Wiki';
	}

	public function view() {
		echo '<iframe id="wikiframe" src="' . CSR_ROOT . $this->getModel() . '"></iframe>';
	}

}
