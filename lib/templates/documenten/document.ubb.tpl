<a class="ubb_block ubb_document" href="{$document->getDownloadurl()}" title="{$document->getNaam()|escape:'html'}">
	<span class="mimetype" title="{$document->getMimetype()}">{$document->getMimetype()|mimeicon}</span>
	
	<span class="size">{$document->getSize()|filesize}</span>
	{$document->getNaam()|escape:'html'}
</a>

