<a class="ubb_block ubb_document" id="document_ubb_{$document->getID()}" href="{$document->getDownloadurl()}" title="{$document->getNaam()|escape:'html'}">
	<span class="mimetype" title="{$document->getMimetype()}">{$document->getMimetype()|mimeicon}</span>

	<span class="size">{$document->getFileSize()|filesize}</span>
	{$document->getNaam()|escape:'html'}
</a>

