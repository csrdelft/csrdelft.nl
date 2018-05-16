<p class="instelling clear">
    <label class="instelling" for="toest_{$module}_{$id}">
        {$label}
    </label>
    {foreach from=$opties key=value item=optie}
    {if $value != 0}
        <label>{$optie}
            <input type="radio"
                   value="{$optie}"
                   name="{$module}_{$id}"
                   id="toest_{$module}_{$id}_{$value}"
                    {if $optie === $waarde} checked="checked"{/if} /></label>

    {/if}
    {/foreach}
</p>