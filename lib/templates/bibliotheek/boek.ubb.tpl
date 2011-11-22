<a class="ubb_block ubb_boek" id="boek_ubb_{$boek->getID()}" href="{$boek->getUrl()}" title="Boek: {$boek->getTitel()|escape:'html'}">
	{icon get="book"}
	<span title="{$boek->getStatus()} boek" class="boekindicator {$boek->getStatus()}">•</span><span class="titel">{$boek->getTitel()|escape:'html'}</span><span class="auteur">{$boek->getAuteur()->getNaam()|escape:'html'}</span>
</a>
