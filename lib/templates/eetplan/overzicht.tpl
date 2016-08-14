<h1>Eetplan</h1>
<div class="geelblokje">
    <h3>LET OP: </h3>
    <p>Van novieten die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen
        koken op het huis waarbij zij gefaeld hebben.</p>
</div>

<table class="eetplantabel">
    <tr>
        <th style="width: 200px;">Noviet/Avond</th>
        {foreach from=$avonden item=avond}
            <th class="huis">{$model->getDatum($avond)}</th>
        {/foreach}
    </tr>

    {foreach from=$eetplan item=$eetplanData key=row}
        <tr class="kleur{$row%2}">
            <td><a href="/eetplan/noviet/{$eetplanData[0]['uid']}">{$eetplanData[0]['naam']}</a></td>
            {foreach from=$avonden item=avond}
                {assign 'huis' $huizen[$eetplanData[$avond] - 1]['huisNaam']}
                <td class="huis"><a href="/eetplan/huis/{$eetplanData[$avond]}">{$huis}</a></td>
            {/foreach}
        </tr>
    {/foreach}
</table>
