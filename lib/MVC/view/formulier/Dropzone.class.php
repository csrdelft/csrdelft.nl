<?php

/**
 * Dropzone.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Form consisting of a DropzoneUploader and fallback FileField
 * 
 */
class Dropzone extends Formulier {

	private $dropzone;
	private $fallback;

	public function __construct($model, $formId, $action, FileField $fallback, $titel = false) {
		parent::__construct($model, $formId, $action, $titel);
		$this->css_classes[] = 'dropzone';
		$this->fallback = $fallback;

		$this->dropzone = new DropzoneUploader($this->fallback->getName(), $this->fallback->getFilter(), false);
		$fields[] = $this->dropzone;
		$fields[] = new HtmlComment('<div class="fallback">');
		$fields[] = $this->fallback;
		$fields[] = new FormKnoppen(null, false);
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

	public function isPosted() {
		if ($this->dropzone->isPosted()) {
			return true;
		} elseif ($this->fallback->isPosted()) {
			return true;
		}
		return false;
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

	public function getJavascript() {
		$js = parent::getJavascript();
		$mag = (LoginModel::mag('P_ALBUM_DEL') ? 'true' : 'false');
		$delete = str_replace('uploaden', 'verwijderen', $this->action);
		$existing = str_replace('uploaden', 'bestaande', $this->action);
		$accept = implode(',', $this->dropzone->getFilter());
		$js[] = <<<JS
thisDropzone = new Dropzone('#{$this->formId}', {
	paramName: "{$this->dropzone->getName()}",
	url: "{$this->action}",
	acceptedFiles: "{$accept}",
	addRemoveLinks: {$mag},
	removedfile: function (file) {
		if (!confirm('Foto definitief verwijderen?')) {
			return;
		}
		var jqXHR = $.ajax({
			type: "POST",
			url: "{$delete}",
			cache: false,
			data: "foto=" + file.name
		});
		jqXHR.done(function (data, textStatus, jqXHR) {
			$(file.previewElement).remove();
		});
		jqXHR.fail(function (jqXHR, textStatus, errorThrown) {
			alert(textStatus);
		});
	}
});
showExisting_{$this->dropzone->getName()} = function (){
	$.post('{$existing}', function (data) {
		$.each(data, function (key, value) {
			mockFile = { name: value.name, size: value.size, type: value.type };
			thisDropzone.emit('addedfile', mockFile);
			if (typeof value.thumb !== 'undefined') {
				thisDropzone.emit('thumbnail', mockFile, value.thumb);
			}
		});
	});
}
JS;
		return $js;
	}

}
