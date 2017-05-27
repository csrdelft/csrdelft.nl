<div id="komende_verjaardagen">
    {foreach from=$verjaardagen item=profiel}
        <div class="verjaardag{if $profiel->isJarig()} cursief{/if}">
            {$profiel->getLink('pasfoto')}
            <span class="datum">{date('d-m', strtotime($profiel->gebdatum))}</span>
        </div>
    {/foreach}
</div>
