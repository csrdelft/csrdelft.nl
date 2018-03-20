<h1>Voorkeuren van lid</h1>
<p>Naam: {$profiel->getLink('volledig')}</p>

<table>
    {assign 'opties' [1 => 'nee', 2 => 'misschien', 3 => 'ja']}
    {foreach $commissies as $commissie}{assign "voorkeur" $voorkeuren[$commissie->id]}
        <tr>
            <td>{$commissie->naam}</td>
            <td>{if $voorkeur === null}{$opties[1]}{else} {$opties[$voorkeur->voorkeur]}{/if}</td>
        </tr>
    {/foreach}
</table><br/>
<h3>Lid opmerkingen</h3><p>{$opmerking->lidOpmerking}</p>
{$opmerkingForm->view()}