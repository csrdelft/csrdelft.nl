<h1>Peilingbeheertool</h1>
<div>
    {getMelding()}
    <span class="dikgedrukt">Nieuwe peiling:</span><br/>
    <form id="nieuwePeiling" action="/tools/peilingbeheer.php?action=toevoegen" method="post">
        <label for="titel">Titel:</label><input name="titel" type="text"/><br />
        <label for="verhaal">Verhaal:</label><textarea name="verhaal" rows="2"></textarea><br />
        <div id="peilingOpties">
            <label for="optie1">Optie 1</label><input name="opties[]" type="text" maxlength="255" /><br/>
            <label for="optie2">Optie 2</label><input name="opties[]" type="text" maxlength="255" /><br />
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