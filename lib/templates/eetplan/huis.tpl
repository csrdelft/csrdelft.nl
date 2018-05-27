<table class="eetplantabel">
    <tr>
        <th style="width: 150px">Avond</th>
        <th style="width: 200px">&Uuml;bersjaarsch</th>
        <th>Mobiel</th>
        <th>E-mail</th>
        <th>Allergie</th>
    </tr>
    {assign 'oude_datum' ''}
    {assign 'row' 0}
    {assign 'kleuren' array('licht', 'donker')}
    {foreach from=$eetplan item=sessie}
        {assign 'datum' $sessie->avond}
        {assign 'noviet' $sessie->getNoviet()}
        {if $datum != $oude_datum}{$row=$row+1}{/if}
        <tr class="{$kleuren[$row%2]}">
            {if $datum == $oude_datum}
                <td>&nbsp;</td>
            {else}
                <td>{$datum}</td>
            {/if}
            <td>{CsrDelft\model\ProfielModel::getLink($noviet->uid, 'civitas')}</td>
            <td>{$noviet->mobiel}</td>
            <td>{$noviet->email}</td>
            <td>{$noviet->eetwens}</td>
        </tr>
        {assign 'oude_datum' $datum}
    {/foreach}
</table>