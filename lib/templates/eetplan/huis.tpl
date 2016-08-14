<table class="eetplantabel">
    <tr>
        <th style="width: 150px">Avond</th>
        <th style="width: 200px">&Uuml;bersjaarsch</th>
        <th>Mobiel</th>
        <th>E-mail</th>
        <th>Eetwens</th>
    </tr>
    {assign 'oude_datum' ''}
    {assign 'row' 0}
    {foreach from=$eetplan item=eetplanData}
        {assign 'datum' $model->getDatum($eetplanData['avond'])}
        {if $datum != $oude_datum}{$row=$row+1}{/if}
        <tr class="kleur{$row%2}">
            {if $datum == $oude_datum}
                <td>&nbsp;</td>
            {else}
                <td>{$datum}</td>
            {/if}
            <td>{ProfielModel::getLink($eetplanData['pheut'], 'civitas')}</td>
            <td>{htmlspecialchars($eetplanData['mobiel'])}</td>
            <td>{htmlspecialchars($eetplanData['email'])}</td>
            <td>{htmlspecialchars($eetplanData['eetwens'])}</td>
        </tr>
        {assign 'oude_datum' $model->getDatum($eetplanData['avond'])}
    {/foreach}
</table>