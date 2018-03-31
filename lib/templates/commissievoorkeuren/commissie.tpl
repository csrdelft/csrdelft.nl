<h1>{$commissie->naam}</h1>
<table class="commissievoorkeuren col-md-6">
    <tr>
        <td><h4>Lid</h4></td>
        <td><h4>Interesse</h4></td>
    </tr>
    {assign "opties" array('', 'nee', 'misschien', 'ja')}
    {foreach $voorkeuren as $voorkeur}
        <tr {if $voorkeur->heeftGedaan()} style="opacity: .50"{/if} >
            <td><a href="/commissievoorkeuren/lidpagina/{$voorkeur->uid}">{$voorkeur->getProfiel()->getNaam()}</a>
            </td>
            <td>{$opties[$voorkeur->voorkeur]}</td>
        </tr>
    {/foreach}
</table>
<div class="col-md-6">
{$commissieFormulier->view()}
</div>