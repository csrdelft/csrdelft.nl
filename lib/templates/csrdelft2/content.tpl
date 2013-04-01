{include file='csrdelft2/partials/_header.tpl'}
<section id="blackout">
	<div id="pageover">
        
		<header class="top">
			<h1>{$csrdelft->getTitel()}</h1>
			<a class="close" href="#">&times;</a>
		</header>
		<div class="mid">
			{$csrdelft->_body->view()}
		</div>
		<div class="btm"></div>
	</div>
</section>
{include file='csrdelft2/partials/_homeContent.tpl'}
{include file='csrdelft2/partials/_footer.tpl'}