{*
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# rss.tpl
# -------------------------------------------------------------------
# templaat voor de rss-voederbak
# -------------------------------------------------------------------
*}<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<copyright>Copyright 2006 C.S.R. Delft</copyright>
		<pubDate>{$smarty.now|rfc2822}</pubDate>
		<lastBuildDate>{$smarty.now|rfc2822}</lastBuildDate>
		<docs>http://csrdelft.nl</docs>
		<description>C.S.R. Delft: Vereniging van Christen-studenten te Delft.</description>
		<image>
			<link>http://csrdelft.nl</link>
			<title>C.S.R. Delft</title>
			<url>//csrdelft.nl/layout/beeldmerk.jpg</url>
			<height>150</height>
			<width>118</width>
			<description>Beeldmerk der C.S.R. Delft</description>
		</image>
		<language>nl-nl</language>
		<atom:link href="{$privatelink}" rel="self" type="application/rss+xml" />
		<link>http://csrdelft.nl/forum</link>
		<title>C.S.R. Delft forum recent</title>
		<managingEditor>PubCie@csrdelft.nl (Publiciteitscommissie der C.S.R.)</managingEditor>
		<webMaster>pubcie@csrdelft.nl (Publiciteitscommissie der C.S.R.)</webMaster>
		{foreach from=$draden item=draad}<item>
				<title>{$draad->titel|escape:'html'}</title>
				<link>http://csrdelft.nl/forum/reactie/{$draad->laatste_post_id}</link>
				{*foreach from=$draad->getForumPosts() item=post}<description><![CDATA[ {$post->tekst|bbcode} ]]></description>
					<pubDate>{$post->datum_tijd|rfc2822}</pubDate>
				{/foreach*}
				<dc:creator>{$draad->laatste_wijziging_uid|csrnaam:'user':'link'|escape:'html'}</dc:creator>
				<category>forum/{$delen[$draad->forum_id]->titel|escape:'html'}</category>
				<comments>http://csrdelft.nl/forum/onderwerp/{$draad->draad_id}</comments>
				<guid isPermaLink="true">http://csrdelft.nl/forum/reactie/{$draad->laatste_post_id}#{$draad->laatste_post_id}</guid>
			</item>
		{/foreach}
	</channel>
</rss>