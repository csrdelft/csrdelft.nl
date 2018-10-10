<?php

namespace CsrDelft\view;

class MeldingResponse implements View {
	public function view() {
		echo getMelding();
	}

	public function getTitel() {
		return '';
	}

	public function getBreadcrumbs() {
		return '';
	}

	/**
	 * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
	 */
	public function getModel() {
		return null;
	}
}
