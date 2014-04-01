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
		<docs>http://csrdelft.nl/</docs>
		<description>C.S.R. Delft: Vereniging van Christen-studenten te Delft.</description>
		<image>
		<link>http://csrdelft.nl/</link>
		<title>C.S.R. Delft</title>
		<url>{$CSR_PICS}layout/beeldmerk.jpg</url>
		<height>150</height>
		<width>118</width>
		<description>Beeldmerk der C.S.R. Delft</description>
		</image>
		<language>nl-nl</language>
		<atom:link href="{$privatelink}" rel="self" type="application/rss+xml" />
		<link>http://csrdelft.nl/forum/recent</link>
		<title>C.S.R. Delft forum recent</title>
		<managingEditor>PubCie@csrdelft.nl (Publiciteitscommissie der C.S.R.)</managingEditor>
		<webMaster>pubcie@csrdelft.nl (Publiciteitscommissie der C.S.R.)</webMaster>
		{foreach from=$draden item=draad}<item>
				<title>{$draad->titel}</title>
				<link>http://csrdelft.nl/forumdraad/{$draad->draad_id}</link>
				<dc:creator>{$draad->lid_id|csrnaam:'user':false:false|escape:'html'}</dc:creator>
				<category>forum/{$titels[$draad->forum_id]}</category>
				<comments>http://csrdelft.nl/forumdraad/{$draad->draad_id}</comments>
				<guid isPermaLink="true">http://csrdelft.nl/forumdraad/{$draad->draad_id}</guid>
				<pubDate>{$draad->datum_tijd|rfc2822}</pubDate>
			</item>
		{/foreach}
	</channel>
</rss>