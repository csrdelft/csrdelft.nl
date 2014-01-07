{include file='csrdelft2/partials/_header.tpl'}
<section id="blackout">
	<div id="pageover"{if isset($zijkolom) and $zijkolom===false} class="widepage"{/if}>
        {if isset($menutpl)}{include file="csrdelft2/partials/_menu$menutpl.tpl"}{/if}
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
{include file='csrdelft2/partials/_homeContent.tpl'}
{include file='csrdelft2/partials/_footer.tpl'}