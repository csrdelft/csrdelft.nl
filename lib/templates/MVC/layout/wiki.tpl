<!DOCTYPE html>
<html>
	<head>
		{include file='html_head.tpl'}
	</head>
	<body>
		{include file='MVC/layout/pagina_header.tpl'}
		<main class="cd-main-content">
			{$body->view()}
		</main>
		{$mainmenu->view()}
		{if isset($minion)}
			{$minion}
		{/if}
	</body>
</html>