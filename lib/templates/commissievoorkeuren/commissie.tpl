<h1>{$commissie->naam}</h1>
<div class="col-md-6">
    <table class="commissievoorkeuren">
        <tr>
            <th>Lid</th>
            <th>Interesse</th>
        </tr>
        {assign "opties" array('', 'nee', 'misschien', 'ja')}
        {foreach $voorkeuren as $voorkeur}
            {assign "profiel" $voorkeur->getProfiel()}
            {if $profiel->isLid()}
                <tr {if $voorkeur->heeftGedaan()} style="opacity: .50"{/if} >
                    <td><a href="/commissievoorkeuren/lidpagina/{$voorkeur->uid}">{$profiel->getNaam()}</a>
                    </td>
                    <td>{$opties[$voorkeur->voorkeur]}</td>
                </tr>
            {/if}
        {/foreach}
    </table>
</div>
<div class="col-md-6">
    {$commissieFormulier->view()}
</div>