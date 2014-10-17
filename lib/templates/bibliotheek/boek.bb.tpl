<a class="bb-block bb-boek" id="boek_bb-{$boek->getID()}" href="{$boek->getUrl()}" title="Boek: {$boek->getTitel()|escape:'html'}">
	{icon get="book"}
	<span title="{$boek->getStatus()} boek" class="boekindicator {$boek->getStatus()}">•</span><span class="titel">{$boek->getTitel()|escape:'html'}</span><span class="auteur">{$boek->getAuteur()|escape:'html'}</span>
</a>
