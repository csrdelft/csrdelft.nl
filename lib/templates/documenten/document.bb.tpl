<a class="bb-block bb-document" id="document_bb-{$document->getID()}" href="{$document->getDownloadurl()}" title="{$document->getNaam()|escape:'html'}">
	<span class="mimetype" title="{$document->getMimetype()}">{$document->getMimetype()|mimeicon}</span>
	<span class="size">{$document->getFileSize()|filesize}</span>
	{$document->getNaam()|escape:'html'}
</a>