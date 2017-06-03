<div class="bb-block bb-document" id="document_bb-{$document->id}">
	<span class="mimetype" title="{$document->mimetype}">{$document->getMimetypeIcon()}</span>
	<span class="size">{$document->filesize|filesize}</span>
	<span class="download"><a href="{$document->getDownloadUrl()}" title="Document neerladen"><span class="fa fa-download module-icon"></span></a></span>
	<a href="{$document->getUrl()}" target="_blank">{$document->naam|escape:'html'}</a>
</div>
