<?php

namespace CsrDelft\view\formulier\knoppen;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class FormDefaultKnoppen extends FormKnoppen
{

	public $submit;
	public $reset;
	public $cancel;

	public function __construct($cancel_url = null, $reset = true, $icons = true, $labels = true, $cancel_reset = false, $submit_reset = false, $submit_DataTableResponse = false)
	{
		parent::__construct();

		$this->submit = new SubmitKnop();
		if ($cancel_reset) {
			$this->submit->icon = 'accept';
		}
		if ($submit_reset) {
			$this->submit->action .= ' reset';
		}
		if ($submit_DataTableResponse) {
			$this->submit->action .= ' DataTableResponse';
		}
		$this->addKnop($this->submit);
		if ($reset) {
			$this->reset = new ResetKnop();
			$this->addKnop($this->reset);
		}
		if ($cancel_url !== false) {
			$this->cancel = new CancelKnop($cancel_url);
			if ($cancel_reset) {
				$this->cancel->action .= ' reset';
			}
			$this->addKnop($this->cancel);
		}
		if (!$icons) {
			foreach ($this->getModel() as $knop) {
				$knop->icon = null;
			}
		}
		if (!$labels) {
			foreach ($this->getModel() as $knop) {
				$knop->label = null;
			}
		}
	}

	public function setConfirmAll()
	{
		foreach ($this->getModel() as $knop) {
			$knop->action .= ' confirm';
		}
	}

}
