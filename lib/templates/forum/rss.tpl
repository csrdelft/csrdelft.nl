{*
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# rss.tpl
# -------------------------------------------------------------------
# templaat voor de rss-voederbak
# -------------------------------------------------------------------
# TODO: maak de data rfc-aware && regel de forum-voorkeuren voor namen
*}
<rss version="2.0">
	<channel>
		<copyright>Copyright 2006 C.S.R. Delft</copyright>
		<pubDate>{$smarty.now}</pubDate>
		<lastBuildDate>{$smarty.now}</lastBuildDate>
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
		<link>http://csrdelft.nl/forum/</link>
		<title>C.S.R. Delft forum laatste berichten.</title>
		<managingEditor>PubCie@csrdelft.nl</managingEditor>
		<webMaster>pubcie@csrdelft.nl</webMaster>
		{foreach from=$aPosts item=post}<item>
			<title>{$post.nickname}:{$post.tekst|truncate:30}</title>
			<link>http://csrdelft.nl/forum/onderwerp/{$post.topicID}#post{$post.postID}</link>
			<description>{$post.tekst}</description>
			<author>{$post.nickname}</author>
			<category>forum: {$post.categorieTitel}</category>
			<comments>http://csrdelft.nl/forum/onderwerp/{$post.topicID}</comments>
			<guid>http://csrdelft.nl/forum/onderwerp/{$post.topicID}#post{$post.postID}</guid>
			<pubDate>{$post.datum}</pubDate>
		</item>
		{/foreach}
	</channel>
</rss>