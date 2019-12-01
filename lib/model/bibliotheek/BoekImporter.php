<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\model\entity\bibliotheek\Boek;
use EasyRdf_Graph;
use EasyRdf_Resource;

class BoekImporter {

	/**
	 * BoekImporter constructor.
	 */
	public function __construct() {
	}

	public function import(Boek $boek) {
		$isbn = filter_var($boek->getISBN(), FILTER_SANITIZE_NUMBER_INT);
		if (trim($isbn) === '') {
			return;
		}
		$rdf = new EasyRdf_Graph("http://worldcat.org/isbn/" . $isbn);
		$rdf->load(null);
		/** @var EasyRdf_Resource $topic */
		$topic = $rdf->resource("schema:Book")->get("^a");
		if ($topic !== null) {
			$rdf->load($topic->getUri() . ".nt");

			$boek->titel = $topic->get("schema:name")->__toString();
			$boek->auteur = $topic->get("schema:creator/schema:name")->__toString();
		}
	}
}
