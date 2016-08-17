<ul>
{foreach from=$woonoorden item=woonoord}
<li>{$woonoord->naam} ({$woonoord->eetplan})</li>
{/foreach}
</ul>
