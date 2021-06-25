<?php

namespace CsrDelft\view\formulier;

use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\uploadvelden\FileField;
use CsrDelft\view\formulier\uploadvelden\ImageField;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Form consisting of a DropzoneUploader and fallback FileField
 */
class Dropzone extends Formulier {

	/**
	 * @var ImageField
	 */
	private $dropzone;
	/**
	 * @var FileField
	 */
	private $fallback;

	public function __construct($model, $action, FileField $fallback, $cancel_url, $titel = false) {
		parent::__construct($model, $action, $titel);
		$this->css_classes[] = 'dropzone';
		$this->fallback = $fallback;
		$this->dropzone = $fallback->getUploader();

		$fields[] = new HtmlComment('<div class="fallback">');
		$fields[] = $this->fallback;
		$fields[] = new FormDefaultKnoppen($cancel_url, false);
		$fields[] = new HtmlComment('</div>');

		$this->addFields($fields);
	}

	public function getPostedUploader() {
		if ($this->dropzone->isPosted()) {
			return $this->dropzone;
		} elseif ($this->fallback->isPosted()) {
			return $this->fallback;
		}
		return null;
	}

	public function validate() {
		if (!$this->isPosted()) {
			return false;
		}
		if ($this->dropzone->validate()) {
			return true;
		} elseif ($this->fallback->validate()) {
			return true;
		}
		return false;
	}

	public function isPosted() {
		if ($this->dropzone->isPosted()) {
			return true;
		} elseif ($this->fallback->isPosted()) {
			return true;
		}
		return false;
	}

	protected function getFormTag() {
		if ($this->dataTableId) {
			$this->css_classes[] = 'DataTableResponse';
		}
		$action = htmlspecialchars($this->action);
		$className = implode(' ', $this->css_classes);
		$maxsize = getMaximumFileUploadSize() / 1024 / 1024; // MB
		$delete = str_replace('uploaden', 'verwijderen', $this->action);
		$existing = str_replace('uploaden', 'bestaande', $this->action);
		$cover = str_replace('uploaden', 'albumcover', $this->action);
		$accept = implode(',', $this->dropzone->getFilter());
		return <<<HTML
<form
		enctype="{$this->enctype}"
		action="{$action}"
		id="{$this->formId}"
		data-tableid="{$this->dataTableId}"
		class="{$className}"
		method="{$this->post}"
		data-naam="{$this->dropzone->getName()}"
		data-accept="{$accept}"
		data-delete-url="{$delete}"
		data-maxsize="{$maxsize}"
		data-cover-url="{$cover}"
		data-existing-url="{$existing}"
>
HTML;
	}

}
