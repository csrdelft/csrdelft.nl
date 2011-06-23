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
		<docs>http://csrdelft.nl/index.php</docs>
		<description>C.S.R. Delft: Vereniging van Christen-studenten te Delft.</description>
		<image>
			<link>http://csrdelft.nl/</link>
			<title>C.S.R. Delft</title>
			<url>{$csr_pics}layout/beeldmerk.jpg</url>
			<height>150</height>
			<width>118</width>
			<description>Logo van C.S.R. Delft</description>
		</image>
		<language>nl-nl</language>
		<atom:link href="{$selflink}" rel="self" type="application/rss+xml" />
		<link>http://csrdelft.nl/communicatie/forum/</link>
		<title>C.S.R. Delft forum laatste berichten.</title>
		<managingEditor>PubCie@csrdelft.nl (Publiciteitscommissie der C.S.R.)</managingEditor>
		<webMaster>pubcie@csrdelft.nl (Publiciteitscommissie der C.S.R.)</webMaster>
		{foreach from=$aPosts item=post}<item>
			<title>{$post.titel|truncate:30|escape:'html'}</title>
			<link>http://csrdelft.nl/communicatie/forum/reactie/{$post.postID}</link>
			<description><![CDATA[ {$post.tekst|escape:'htmlall'} ]]></description>
			<dc:creator>{$post.uid|csrnaam:'user':false:false|escape:'html'}</dc:creator>
			<category>forum/{$post.categorieTitel|escape:'html'}</category>
			<comments>http://csrdelft.nl/communicatie/forum/onderwerp/{$post.tid}</comments>
			<guid isPermaLink="true">http://csrdelft.nl/communicatie/forum/reactie/{$post.postID}</guid>
			<pubDate>{$post.datum|rfc2822}</pubDate>
		</item>
		{/foreach}
	</channel>
</rss>