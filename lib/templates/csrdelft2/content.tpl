{include file='csrdelft2/partials/_header.tpl'}
<section id="blackout">
	<div id="pageover">
        
		<header class="top">
			<h1>{$csrdelft->getTitel()}</h1>
			<a class="close" href="#">&times;</a>
		</header>
		<div class="mid">
        <!-- Deze moet nog teogevoegd, via JS kan dan het plaatje in de <img gezet worden
            <figure id="clip" class="rotate right">
            <img id="clip-img" /><div class="clip"></div>
        </figure>-->
    {$csrdelft->_body->view()}
		</div>
		<div class="btm"></div>
	</div>
</section>
{include file='csrdelft2/partials/_homeContent.tpl'}
{include file='csrdelft2/partials/_footer.tpl'}