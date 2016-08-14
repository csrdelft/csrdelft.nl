<table class="eetplantabel">
    <tr>
        <th style="width: 150px;">Avond</th><th style="width: 200px">Huis</th>
    </tr>
    {assign 'row' 0}
    {foreach from=$eetplan item=eetplanData key=row}
        {assign 'woonoord' WoonoordenModel::omnummeren($eetplanData['groepid'])}
        <tr class="kleur{$row%2}">
            <td>{$model->getDatum($eetplanData['avond'])}</td>
            <td><a href="/groepen/woonoorden/{$woonoord->id}">{$woonoord->naam}</a></td>
        </tr>
    {/foreach}

</table>

