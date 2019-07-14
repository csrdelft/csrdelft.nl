<h1>Verjaardagen</h1>
<div class="row">
{section name=m start=0 loop=12 step=1}
    {assign var=maand value=($dezemaand - 1 + $smarty.section.m.index) % 12 + 1}
    <div class="col-xl-2 col-md-3 col-sm-4 mb-3">
        <table class="inline">
            <tr>
                <th></th>
                <th><h3>{mktime(0, 0, 0, $maand, 10)|date_format:'%B'|ucfirst}</h3></th>
            </tr>
            {foreach from=$verjaardagen[$maand - 1] item=verjaardag}
                {assign gebdag date('j', strtotime($verjaardag->gebdatum))}
                <tr>
                    {if $gebdag == $dezedag && $maand == $dezemaand}
                    <td class="text-right dikgedrukt cursief">
                        {else}
                    <td class="text-right">
                        {/if}

                        {$gebdag}
                    </td>
                    {if $gebdag == $dezedag && $maand == $dezemaand}
                    <td class="dikgedrukt cursief">
                        {else}
                    <td>
                        {/if}
                        &nbsp;
                        {$verjaardag->getLink('civitas')}
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
{/section}
</div>
