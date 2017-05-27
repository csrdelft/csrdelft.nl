{strip}
    {assign var=timestamp value=strtotime($draad->laatst_gewijzigd)}
    <div class="item{if CsrDelft\model\security\LoginModel::mag('P_LOGGED_IN') AND $draad->isOngelezen()} ongelezen{/if}" id="forumdraad-row-{$draad->draad_id}">

        <a href="/forum/onderwerp/{$draad->draad_id}{if CsrDelft\model\LidInstellingenModel::get('forum', 'open_draad_op_pagina') == 'ongelezen'}#ongelezen{elseif CsrDelft\model\LidInstellingenModel::get('forum', 'open_draad_op_pagina') == 'laatste'}#reageren{/if}"
           title="{$draad->titel}">
            {if CsrDelft\model\security\LoginModel::mag('P_LOGGED_IN') AND $draad->isOngelezen()}
                <span class="bullet">
                    <svg viewBox="0 0 32 32">
                        <ellipse cx="16" cy="16" rx="16" ry="16" style="fill:#ff9000;" ></ellipse>
                    </svg>
                </span>
            {/if}

            <span class="draad-titel" title="{$draad->titel}">{$draad->titel}</span>

            <span class="draad-deel">{$draad->getForumDeel()->titel}</span>

            <span class="draad-moment">
            {if date('d-m', $timestamp) === date('d-m')}
                {$timestamp|date_format:"%H:%M"}
            {elseif strftime('%U', $timestamp) === strftime('%U')}
                <div class="zijbalk-dag">{$timestamp|date_format:"%a"}&nbsp;</div>{$timestamp|date_format:"%d"}
            {else}
                {$timestamp|date_format:"%d-%m"}
            {/if}
            </span>

            <span class="draad-auteur"><img src="/plaetjes/pasfoto/{$draad->uid}.vierkant.png"/></span>
        </a>
    </div>
{/strip}
