{CsrDelft\getMelding()}

{include file='forum/draad_kop.tpl'}

{if $draad->magModereren()}
    {include file='forum/draad_mod.tpl'}
{/if}

{$zoekform->view()}

{capture name='paginering'}
    <div class="forum-paginering">
        {if $draad->pagina_per_post}
            Bericht:
        {else}
            Pagina:
        {/if}
        {if isset($statistiek)}
            {assign var="append" value="/statistiek"}
        {else}
            {assign var="append" value=""}
        {/if}
        {sliding_pager baseurl="/forum/onderwerp/"|cat:$draad->draad_id|cat:"/" url_append=$append
        pagecount=CsrDelft\model\forum\ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) curpage=CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina()}
    </div>
{/capture}

<section id="forum-draad">
    {* Paginering boven eerste post op de pagina als de eerste post van het draadje niet plakkerig is of dit de eerste pagina is *}
    {if $paging AND (!$draad->eerste_post_plakkerig OR CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina() === 1)}
        {$smarty.capture.paginering}
    {/if}

    {assign var=vanaf value=false}
    {foreach from=$draad->getForumPosts() item=post name=posts}

        {* Als posts gewijzigd zijn zonder draad gewijzigd te triggeren voorkomt $draad->isOngelezen() dat de gele lijn wordt getoond *}
        {if !$vanaf AND $draad_ongelezen AND (!$gelezen_moment OR strtotime($post->laatst_gewijzigd) > $gelezen_moment)}
            {assign var=vanaf value=true}
            <tr class="tussenschot ongelezenvanaf">
                <td colspan="2">
                    <a id="ongelezen">&nbsp;</a>
                </td>
            </tr>
        {else}
            <tr class="tussenschot">
                <td colspan="2"></td>
            </tr>
        {/if}
        {include file='forum/post_lijst.tpl'}
        {* Paginering onder eerste plakkerige post op alle pagina's behalve de eerste *}
        {if $paging AND $draad->eerste_post_plakkerig AND CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina() != 1 AND $smarty.foreach.posts.first}
            {$smarty.capture.paginering}
        {/if}

    {/foreach}

    {* Paginering onderaan pagina *}
    {if $paging}
        {$smarty.capture.paginering}
    {/if}

    {* Geen ongelezen berichten op de laatste pagina betekend in het geheel geen ongelezen berichten *}
    {if !$vanaf AND CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina() === CsrDelft\model\forum\ForumPostsModel::instance()->getAantalPaginas($draad->draad_id)}
        <div class="tussenschot ongelezenvanaf">
            <a id="ongelezen">&nbsp;</a>
        </div>

    {else}
        <div class="tussenschot"></div>
    {/if}

    <div class="magreageren">
        {if $draad->verwijderd}
            <div class="draad-verwijderd">Dit onderwerp is verwijderd.</div>
        {elseif $draad->gesloten}
            <div class="draad-gesloten">
                U kunt hier niet meer reageren omdat dit onderwerp gesloten is.
                {if $draad->getForumDeel()->isOpenbaar() AND strtotime($draad->laatst_gewijzigd) < strtotime(CsrDelft\model\InstellingenModel::get('forum', 'externen_geentoegang_gesloten'))}
                    <div class="dikgedrukt">Dit externe onderwerp is niet meer toegankelijk voor externen en
                        zoekmachines.
                    </div>
                {/if}
            </div>
        {elseif !$draad->magPosten()}
            <div class="draad-readonly">U mag in dit deel van het forum niet reageren.</div>
        {/if}
    </div>

    <div class="forumfooter">.
        <div class="breadcrumbs">{$breadcrumbs}</div>
        {include file='forum/draad_kop.tpl'}
    </div>

    {if $draad->magPosten()}
        {include file='forum/post_form.tpl' deel=$draad->getForumDeel()}
    {/if}
</section>

{include file='forum/rss_link.tpl'}
