<h1>Voorkeuren van lid</h1>
<p>Naam: {$profiel->getLink('volledig')}</p>

<table class="commissievoorkeuren">
    {assign 'opties' [1 => 'nee', 2 => 'misschien', 3 => 'ja']}
    {foreach $commissies as $commissie}{assign "voorkeur" $voorkeuren[$commissie->id]}
        <tr>
            <td>{$commissie->naam}</td>
            <td>{if $voorkeur === null}{$opties[1]}{else} {$opties[$voorkeur->voorkeur]}{/if}</td>
        </tr>
    {/foreach}
</table>
<h3>Opmerkingen van lid</h3><p>{$lidOpmerking}</p>
<h3>Opmerkingen van praeses</h3>
{$opmerkingForm->view()}
