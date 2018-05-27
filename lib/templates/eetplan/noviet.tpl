<table class="eetplantabel">
    <tr>
        <th style="width: 150px;">Avond</th><th style="width: 200px">Huis</th>
    </tr>
    {assign 'row' 0}
    {foreach from=$eetplan item=sessie}
        {assign 'huis' $sessie->getWoonoord()}
        <tr class="{cycle values="donker,licht"}">
            <td>{$sessie->avond}</td>
            <td><a href="/groepen/woonoorden/{$huis->id}">{$huis->naam}</a></td>
        </tr>
    {/foreach}

</table>

