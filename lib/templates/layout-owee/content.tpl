{include file='layout-owee/partials/_header.tpl'}
<section id="blackout">
	<div id="pageover">
        {if isset($menutpl)}{include file="layout2/partials/_menu$menutpl.tpl"}{/if}
		<header class="pg-top">
			<a class="close" href="#">&times;</a>
		</header>
		<div class="pg-mid">
            <div class="content">
                {$body->view()}
            </div>
		</div>
		<div class="pg-btm"></div>
	</div>
</section>
{include file='layout2/partials/_lidWordenContent.tpl'}
{include file='layout2/partials/_footer.tpl'}
