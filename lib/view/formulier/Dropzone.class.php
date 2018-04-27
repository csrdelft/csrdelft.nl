<?php

namespace CsrDelft\view\formulier;

use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\uploadvelden\FileField;

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
		$maxsize = getMaximumFileUploadSize() / 1024 / 1024; // MB
		$delete = str_replace('uploaden', 'verwijderen', $this->action);
		$existing = str_replace('uploaden', 'bestaande', $this->action);
		$cover = str_replace('uploaden', 'albumcover', $this->action);
		$accept = implode(',', $this->dropzone->getFilter());
		return parent::getJavascript() . <<<JS

thisDropzone = new Dropzone('#{$this->formId}', {
	paramName: "{$this->dropzone->getName()}",
	url: "{$this->action}",
	acceptedFiles: "{$accept}",
	addRemoveLinks: true,
	removedfile: function (file) {
		var jqXHR = $.ajax({
			type: "POST",
			url: "{$delete}",
			cache: false,
			data: "foto=" + file.name // TODO generic
		});
		jqXHR.done(function (data, textStatus, jqXHR) {
			$(file.previewElement).remove();
		});
		jqXHR.fail(function (jqXHR, textStatus, errorThrown) {
			alert(textStatus);
		});
	},
	maxFilesize: {$maxsize},
	maxFiles: 500,
	dictDefaultMessage: "Drop files here to upload",
	dictFallbackMessage: "Your browser does not support drag'n'drop file uploads.",
	dictFallbackText: "Please use the fallback form below to upload your files like in the olden days.",
	dictFileTooBig: "Te groot bestand: ({{filesize}}MiB). Maximum: {{maxFilesize}}MiB.",
	dictInvalidFileType: "Bestanden van dit type zijn niet toegestaan.",
	dictResponseError: "Server responded with {{statusCode}} code.",
	dictCancelUpload: "Annuleren",
	dictCancelUploadConfirmation: "Toevoegen annuleren. Weet u het zeker?",
	dictRemoveFile: "X",
	dictRemoveFileConfirmation: "Bestand verwijderen. Weet u het zeker?",
	dictMaxFilesExceeded: "You can not upload any more files.",
	init: function() {
		this.on('addedfile', function(file) {
			var coverBtn = Dropzone.createElement('<a class="btn" title="Stel deze foto in als omslag voor het album">Omslag</a>');
			file.previewElement.appendChild(coverBtn);
			coverBtn.addEventListener('click', function(e) {
				var coverBtn = $(this);
				// Make sure the button click doesn't submit the form
				e.preventDefault();
				e.stopPropagation();
				var jqXHR = $.ajax({
					type: "POST",
					url: "{$cover}",
					cache: false,
					data: "foto=" + file.name
				});
				jqXHR.done(function (data, textStatus, jqXHR) {
					coverBtn.replaceWith('<span class="glyphicon glyphicon-ok"></span> Omslag');
				});
				jqXHR.fail(function (jqXHR, textStatus, errorThrown) {
					alert(textStatus);
				});
			});
			$('.dz-remove').removeAttr('class').addClass('btn');
		});
	}
});
showExisting_{$this->formId} = function (){
	$.post('{$existing}', function (data) {
		$.each(data, function (key, value) {
			mockFile = { name: value.name, size: value.size, type: value.type };
			thisDropzone.emit('addedfile', mockFile);
			if (typeof value.thumbnail !== 'undefined') {
				thisDropzone.emit('thumbnail', mockFile, value.thumbnail);
			}
		});
	});
}
JS;
	}

}
