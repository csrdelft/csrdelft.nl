
	{foreach from=$scripts item=script}
		<script type="text/javascript" src="{$script}"></script>
	{/foreach}
	<script type="text/javascript">
		$(document).foundation();

		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-19828019-4']);
		_gaq.push(['_trackPageview']);
		(function() {
			var ga = document.createElement('script');
			ga.type = 'text/javascript';
			ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(ga, s);
		})();
	</script>
	{include file='MVC/layout/ubbhulp.tpl'}
</body>

</html>