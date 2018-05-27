<div id="zijbalk_verjaardagen">
    <div class="zijbalk-kopje">
        {toegang P_LEDEN_READ}
            <a href="/leden/verjaardagen">Verjaardagen</a>
        {geentoegang}
            Verjaardagen
        {/toegang}
    </div>

    {if $toonpasfotos}
        <div class="item" id="komende_pasfotos">
            {foreach from=$verjaardagen item=profiel}
                <div class="verjaardag{if $profiel->isJarig()} cursief{/if}">
                    {$profiel->getLink('pasfoto')}
                    <span class="datum">{date('d-m', strtotime($profiel->gebdatum))}</span>
                </div>
            {/foreach}
            <div class="clear"></div>
        </div>
    {else}
        {foreach from=$verjaardagen item=profiel}
            <div class="item">{date('d-m', strtotime($profiel->gebdatum))}
                <span{if $profiel->isJarig()} class="cursief"{/if}>{$profiel->getLink('civitas')}</span>
            </div>
        {/foreach}
    {/if}

</div>
