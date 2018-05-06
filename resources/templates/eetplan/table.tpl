<div id="eetplan">
    <table class="novietentable">
        <thead>
        <tr>
            <th>Novieten</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$novieten item=noviet}
            <tr>
                <td><a href="/eetplan/noviet/{$noviet['uid']}">{$noviet['naam']}</a></td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    <table class="eetplantable">
        <thead>
        <tr>
            {foreach from=$avonden item=avond}
                <th>{$avond}</th>
            {/foreach}
        </tr>
        </thead>
        <tbody>
        {foreach from=$novieten item=noviet}
            <tr>
                {foreach from=$noviet['avonden'] item=avond}
                    <td><a href="/eetplan/huis/{$avond['woonoord_id']}">{$avond['woonoord']}</a></td>
                {/foreach}
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
