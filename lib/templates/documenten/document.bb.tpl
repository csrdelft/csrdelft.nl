<div class="bb-block bb-document" id="document_bb-{$document->getID()}">
	<span class="mimetype" title="{$document->getMimetype()}">{$document->getMimetype()|mimeicon}</span>
	<span class="size">{$document->getFileSize()|filesize}</span>
	<span class="download">
		<a href="{$document->getDownloadUrl()}" title="Document neerladen">
			<img src="//csrdelft.nl/plaetjes/knopjes/down-16.png" width="14" height="14" alt="download" class="module-icon">
		</a>
	</span>
	<a href="{$document->getUrl()}" target="_blank">{$document->getNaam()|escape:'html'}</a>
</div>