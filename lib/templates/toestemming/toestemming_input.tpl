<div class="row">
    <div class="col-sm-8">{$label}</div>
    <div class="col-sm-4">
    {foreach from=$opties key=value item=optie}
    {if $value != 0}
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="{$module}_{$id}" id="{$module}_{$id}" value="{$optie}"{if $optie === $waarde} checked="checked"{/if}>
            <label class="form-check-label" for="{$module}_{$id}">{$optie}</label>
        </div>

    {/if}
    {/foreach}
    </div>
</div>