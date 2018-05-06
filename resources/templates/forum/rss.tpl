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
		<docs>{$smarty.const.CSR_ROOT}</docs>
		<description>C.S.R. Delft: Vereniging van Christen-studenten te Delft.</description>
		<image>
			<link>{$smarty.const.CSR_ROOT}</link>
			<title>C.S.R. Delft</title>
			<url>{$smarty.const.CSR_ROOT}/dist/images/beeldmerk.jpg</url>
			<height>150</height>
			<width>118</width>
			<description>Beeldmerk der C.S.R. Delft</description>
		</image>
		<language>nl-nl</language>
		<atom:link href="{$privatelink}" rel="self" type="application/rss+xml" />
		<link>{$smarty.const.CSR_ROOT}/forum</link>
		<title>C.S.R. Delft forum recent</title>
		<managingEditor>PubCie@csrdelft.nl (Publiciteitscommissie der C.S.R.)</managingEditor>
		<webMaster>pubcie@csrdelft.nl (Publiciteitscommissie der C.S.R.)</webMaster>
		{foreach from=$draden item=draad}<item>
				<title>{$draad->titel|escape:'html'}</title>
				<link>{$smarty.const.CSR_ROOT}/forum/reactie/{$draad->laatste_post_id}</link>
				{*foreach from=$draad->getForumPosts() item=post}<description><![CDATA[ {$post->tekst|bbcode:fixme} ]]></description>
					<pubDate>{$post->datum_tijd|rfc2822}</pubDate>
				{/foreach*}
				<dc:creator>{CsrDelft\model\ProfielModel::getNaam($draad->laatste_wijziging_uid, 'user')}</dc:creator>
				<category>{$draad->getForumDeel()->getForumCategorie()->titel} » {$draad->getForumDeel()->titel}</category>
				<comments>{$smarty.const.CSR_ROOT}/forum/onderwerp/{$draad->draad_id}</comments>
				<guid isPermaLink="true">{$smarty.const.CSR_ROOT}/forum/reactie/{$draad->laatste_post_id}#{$draad->laatste_post_id}</guid>
			</item>
		{/foreach}
	</channel>
</rss>