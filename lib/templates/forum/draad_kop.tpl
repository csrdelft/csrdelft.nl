<h1>
    {$draad->titel}
    {if isset($statistiek)}
        &nbsp;&nbsp;&nbsp;
        <span class="lichtgrijs small" title="Aantal lezers">{$draad->getAantalLezers()} lezers</span>
    {/if}
</h1>

<div class="forumheadbtn">
    {if !isset($statistiek) AND $draad->magStatistiekBekijken()}
        <a href="/forum/onderwerp/{$draad->draad_id}/{CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina()}/statistiek"
           class="btn" title="Toon statistieken">{icon get="chart_line"}</a>
        &nbsp;&nbsp;&nbsp;
    {/if}
    <a title="Onderwerp toevoegen aan favorieten" class="btn post popup addfav"
       href="/menubeheer/toevoegen/favoriet">{icon get="star"}</a>
    &nbsp;&nbsp;&nbsp;
    {if $draad->isGevolgd()}
        <a href="/forum/volgenuit/{$draad->draad_id}" class="btn post ReloadPage volgenUit"
           title="Onderwerp niet meer volgen per email">{icon get="email_go" hover="email_delete"}</a>
    {elseif $draad->magVolgen()}
        <a href="/forum/volgenaan/{$draad->draad_id}" class="btn post ReloadPage volgenAan"
           title="Onderwerp volgen per email">{icon get="email" hover="email_add"}</a>
    {/if}
    &nbsp;&nbsp;&nbsp;
    {if $draad->isVerborgen()}
        <a href="/forum/tonen/{$draad->draad_id}" class="btn post ReloadPage tonenAan"
           title="Onderwerp tonen in zijbalk">{icon get="layout" hover="layout_add"}</a>
    {elseif $draad->magVerbergen()}
        <a href="/forum/verbergen/{$draad->draad_id}" class="btn post ReloadPage tonenUit"
           title="Onderwerp verbergen in zijbalk">{icon get="layout_sidebar" hover="layout_delete"}</a>
    {/if}
    &nbsp;&nbsp;&nbsp;
    {if $draad->magModereren()}
        {if $draad->gesloten}
            <a href="/forum/wijzigen/{$draad->draad_id}/gesloten" class="btn post ReloadPage slotjeUit"
               title="Openen (reactie mogelijk)">{icon get="lock" hover="lock_break"}</a>
        {else}
            <a href="/forum/wijzigen/{$draad->draad_id}/gesloten" class="btn post ReloadPage slotjeAan"
               title="Sluiten (geen reactie mogelijk)">{icon get="lock_open" hover="lock"}</a>
        {/if}
        &nbsp;&nbsp;&nbsp;
        <a class="btn" title="Moderatie-functies weergeven" onclick="$('#forumtabel a.forummodknop').fadeIn();
						$('#modereren').slideDown();
						$(window).scrollTo('#modereren', 600, {
							easing: 'easeInOutCubic',
							offset: {
								top: -100,
								left: 0
							}
						});
				   ">{icon get="wrench"} Modereren</a>
    {/if}
</div>
