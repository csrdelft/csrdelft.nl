<div class="ubb_document">
	<span class="size">{$document->getSize()|filesize}</span>
	<span class="mimetype" title="{$document->getMimetype()}">{$document->getMimetype()|mimeicon}</span>
	<a href="{$document->getDownloadurl()}">{$document->getNaam()|escape:'html'}</a>
</div>
