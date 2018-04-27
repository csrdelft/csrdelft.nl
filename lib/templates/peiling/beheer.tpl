<h1>Peilingbeheertool</h1>
<div>
    {getMelding()}
    <span class="dikgedrukt">Nieuwe peiling:</span><br/>
    <form id="nieuwePeiling" action="/peilingen/beheer" method="post">
        <label for="titel">Titel:</label><input name="titel" type="text" value="{$peiling->titel}"/><br />
        <label for="verhaal">Verhaal:</label><textarea name="verhaal" rows="2">{$peiling->tekst}</textarea><br />
        <div id="peilingOpties">
        {if count($peiling->getOpties()) > 0}
            {foreach from=$peiling->getOpties() key=index item=optie}
            <label>Optie {$index + 1}:</label><input name="opties[]" type="text" maxlength="255" value="{$optie->optie}"/><br />
            {/foreach}
            <label>Optie {$index + 2}:</label><input name="opties[]" type="text" maxlength="255" /><br/>
        {else}
            <label>Optie 1</label><input name="opties[]" type="text" maxlength="255" /><br/>
            <label>Optie 2</label><input name="opties[]" type="text" maxlength="255" /><br/>
        {/if}
        </div>
        <label for="foo">&nbsp;</label> <input type="button" onclick="addOptie()" value="extra optie" /><br />
        <label for="submit">&nbsp;</label><input type="submit" value="Maak nieuwe peiling" />
    </form>
    <br />
    <div class="peilingen">
        {foreach from=$peilingen item=peiling}
            {$peiling->view()}
        {/foreach}
    </div>
</div>