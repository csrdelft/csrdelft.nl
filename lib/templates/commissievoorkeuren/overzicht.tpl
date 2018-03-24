<h2>Commissievoorkeuren</h2>
<div class="col-md-6">
<p>Klik op een commissie om de voorkeuren te bekijken</p>
{foreach $categorien as $cid => $cat}
    <h2>{$cat["categorie"]->naam} </h2>
    <ul>
        {foreach $cat["commissies"] as $commissie}
            <li {if !$commissie->zichtbaar} style="opacity: .50" {/if}><a
                        href="/commissievoorkeuren/overzicht/{$commissie->id}">{$commissie->naam}</a></li>
        {/foreach}
        {if count($cat["commissies"]) == 0}
            Deze categorie bevat geen commissies.
            <form method="post" action="/commissievoorkeuren/verwijdercategorie/{$cid}">
                <button type="submit" >Categorie verwijderen</button>
            </form>
        {/if}
    </ul>
{/foreach}
</div>
<div class="col-md-6">
{$categorieFormulier->view()}
{$commissieFormulier->view()}
</div>