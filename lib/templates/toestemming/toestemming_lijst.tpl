{*
	beheer_abonnement_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{assign var=uid value=''}

<div class="btn-group float-right">
    <a class="btn btn-primary" href="?alleen=ja">Alleen Ja</a>
    <a class="btn btn-primary" href="?alleen=nee">Alleen Nee</a>
    <a class="btn btn-primary" href="?alleen=">Alles</a>
</div>

<table class="table">
    <thead class="thead-light">
    <tr>
        <td>Naam</td>
        <td>Onderdeel</td>
        <td>Type</td>
        <td>Keuze</td>
    </tr>
    </thead>

    <tbody>
    {foreach name=loop from=$toestemmingen item=toestemming}
        {if $uid != $toestemming->uid}
            <tr>
                <td colspan="4"></td>
            </tr>
        {/if}
        <tr>
            <td>
                {if $uid != $toestemming->uid}
                    {$toestemming->getProfiel()->getLink()}
                {/if}
            </td>
            <td>
                {$toestemming->module}
            </td>
            <td>
                {$toestemming->instelling_id}
            </td>
            {if $toestemming->waarde == 'ja'}
                <td class="table-success">
                    {$toestemming->waarde}
                </td>
            {elseif $toestemming->waarde == 'nee'}
                <td class="table-danger">
                    {$toestemming->waarde}
                </td>
            {/if}
        </tr>
        {assign var=uid value=$toestemming->uid}
    {/foreach}
    </tbody>
</table>
