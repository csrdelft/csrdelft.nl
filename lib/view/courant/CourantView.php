<?php

namespace CsrDelft\view\courant;

use CsrDelft\entity\courant\Courant;
use CsrDelft\entity\courant\CourantCategorie;
use CsrDelft\view\ToResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * CourantView.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @property Courant $model
 *
 */
class CourantView implements ToResponse {

	private $model;
	private $berichten;

	/**
	 * CourantView constructor.
	 * @param Courant $courant
	 * @param $berichten
	 */
	public function __construct(Courant $courant, $berichten) {
		$this->model = $courant;
		setlocale(LC_ALL, 'nl_NL@euro');
		$this->berichten = $berichten;
	}

	public function getTitel() {
		return 'C.S.R.-courant van ' . $this->getVerzendMoment();
	}

	public function getVerzendMoment() {
		return strftime('%d %B %Y', strtotime($this->model->verzendMoment));
	}

	public function getHtml($headers = false) {
		return view('courant.mail', [
			'headers' => $headers,
			'courant' => $this->model,
			'berichten' => $this->berichten,
			'catNames' => CourantCategorie::getSelectOptions(),
		])->getHtml();
	}

	public function toResponse(): Response {
		return new Response($this->getHtml());
	}
}
