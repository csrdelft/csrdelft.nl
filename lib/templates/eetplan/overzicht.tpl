<h1>Eetplan</h1>
<div class="geelblokje">
    <h3>LET OP: </h3>
    <p>Van novieten die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen
        koken op het huis waarbij zij gefaeld hebben.</p>
</div>

<table class="eetplantabel">
    <tr>
        <th style="width: 200px;">Noviet/Avond</th>
        {foreach from=$avonden item=sessie}
            <th class="huis">{$sessie->avond}</th>
        {/foreach}
    </tr>


    {foreach from=$eetplan item=feut key=row}
        <tr class="{cycle values="donker,licht"}">
            {assign 'noviet' $feut[0]->getNoviet()}
            <td><a href="/eetplan/noviet/{$noviet->uid}">{$noviet->getNaam('volledig')}</a></td>
            {foreach from=$feut item=avond}
                {assign 'huis' $avond->getWoonoord()}
                <td class="huis"><a href="/eetplan/huis/{$huis->id}">{$huis->naam}</a></td>
            {/foreach}
        </tr>
    {/foreach}
</table>
