{include file='csrdelft2/partials/_header.tpl'}
<section id="blackout">
	<div id="pageover">
        
		<header class="top">
			<h1>{$csrdelft->getTitel()}</h1>
			<a class="close" href="#">&times;</a>
		</header>
		<div class="mid">
            <figure id="clip" class="rotate right">
                <img id="clip-img" class="REPLACE-ANCHOR" /><div class="clip"></div>
            </figure>
            <div class="content">
                {$csrdelft->_body->view()}
            </div>
		</div>
		<div class="btm"></div>
	</div>
</section>
{include file='csrdelft2/partials/_homeContent.tpl'}
{include file='csrdelft2/partials/_footer.tpl'}