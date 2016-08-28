<h1>Eetplanbeheer</h1>

<h2>Woonoorden die meedoen met eetplan</h2>
<table>
    <tr>
        <th>Woonoord</th>
        <th>&nbsp;</th>
    </tr>
    {foreach from=$woonoorden item=woonoord}
        <tr>
            <td>{$woonoord->naam}</td>
            <td class="{cycle values="donker,licht"}">
                <a title="Deelname veranderen" data-id="{$woonoord->id}"
                   data-status="{$woonoord->eetplan|var_export:true}" class="woonoord-status btn {if $woonoord->eetplan}ja{else}nee{/if}">
                    <span></span>
                </a>
            </td>
        </tr>
    {/foreach}
</table>
{$bekendentable->view()}
