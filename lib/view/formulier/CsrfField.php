<?php


namespace CsrDelft\view\formulier;


use CsrDelft\view\View;
use Symfony\Component\Security\Csrf\CsrfToken;

class CsrfField implements View {
	/**
	 * @var CsrfToken
	 */
	private $token;

	public function __construct(CsrfToken $token) {
		$this->token = $token;
	}

	public function view() {
		if ($this->token === null) {
			return;
		}
		// Note that explicit HTML instead of making use of HiddenField because HiddenField will automatically take posted values
		echo '<input type="hidden" name="X-CSRF-ID" value="' . htmlentities($this->token->getId()) . '"  />';
		echo '<input type="hidden" name="X-CSRF-VALUE" value="' . htmlentities($this->token->getValue()) . '"  />';
	}

	public function getTitel() {
		return null;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getModel() {
		return $this->token;
	}
}
