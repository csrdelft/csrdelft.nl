<!DOCTYPE html>
<html>
	<head>
		{include file='html_head.tpl'}
	</head>
	<body role="document">
		{$body->view()}
		{if $smarty.const.DEBUG AND (LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued())}
			<a id="mysql_debug_toggle" onclick="$(this).replaceWith($('#mysql_debug').toggle());">DEBUG</a>
			<div id="mysql_debug" class="pre">{getDebug()}</div>
		{/if}
	</body>
</html>